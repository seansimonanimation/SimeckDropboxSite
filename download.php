<?php
/**
 * download.php
 * 
 * Unified download handler. Supports two token formats:
 *   1. Legacy DB documents:  "username|uploadID" (base64-encoded)
 *   2. elFinder filepath:    "elFinder|filepath|hmac_signature" (base64-encoded)
 * 
 * The HMAC signature is computed over the filepath using DOWNLOAD_SECRET.
 * 
 * Legacy flow: Looks up artistdocuments/clientdocuments table by uploadID, verifies ownership.
 * elFinder flow: Decodes hash, verifies HMAC + volume access, serves file directly.
 * 
 * Thumbnail mode: Add ?thumb=1 to serve the cached thumbnail from /files/.tmb/
 * instead of the original file. Only works for elFinder filepath tokens.
 */

include_once __DIR__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';

// Look for the secret in the Docker config location; fall back to a local config
if (file_exists('/var/www/dbconfig.php')) {
    include_once '/var/www/dbconfig.php';
} elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
    include_once __ROOT__ . '/dbconfig.php';
}

if (isset($_GET['download'])) {
    InitiateDownload($_GET['download']);
}

// ─── Token Generation ──────────────────────────────────────────────

/**
 * Generate a signed download token for an elFinder filepath.
 * 
 * @param string $filepath Real server filesystem path to the file
 * @return string          Base64-encoded token (safe for URL query params)
 */
function GenerateElfinderDownloadToken($filepath) {
    $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
    $signature = hash_hmac('sha256', $filepath, $secret);
    $tokenData = 'elFinder|' . $filepath . '|' . $signature;
    return base64_encode($tokenData);
}

/**
 * Legacy: Generate download token for a DB-recorded document.
 * Preserved for backward compat with artistManagement, clientManagement, timeclock.
 * 
 * @param string $username The document owner's username
 * @param string $docID    The uploadID from the DB table
 * @return string          Base64-encoded token (safe for URL query params)
 */
function Generateb64EncodedDownloadLink($username, $docID){
    $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
    $signature = hash_hmac('sha256', $docID, $secret);
    $tokenData = $username . '|' . $docID . '|' . $signature;
    return base64_encode($tokenData);
}

// ─── Token Dispatch ────────────────────────────────────────────────

/**
 * Main dispatch: decides based on the embedded username whether it's a
 * legacy DB download or an elFinder filepath download.
 */
function InitiateDownload($encodedData) {
    $decoded = base64_decode($encodedData, true);
    if ($decoded === false || empty($decoded)) {
        echo "Invalid download token.";
        return;
    }

    $parts = explode('|', $decoded);
    
    // Token must have 3 parts:  username | filepath/docID | signature
    if (count($parts) < 3) {
        echo "Invalid download token format.";
        return;
    }

    $username = $parts[0];
    $identifier = $parts[1];  // docID or filepath
    $providedSig = $parts[2];
    
    $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
    $expectedSig = hash_hmac('sha256', $identifier, $secret);
    
    if (!hash_equals($expectedSig, $providedSig)) {
        echo "Invalid or tampered download token.";
        return;
    }

    if ($username === 'elFinder') {
        // ─── elFinder filepath download (or thumbnail) ────────────
        ServeElfinderFile($identifier);
    } else {
        // ─── Legacy DB document download ───────────────────────────
        $b64Legacy = base64_encode($username . '|' . $identifier);
        if (DownloadPermissionCheck($b64Legacy)) {
            ServeFileForDownload($username, $identifier);
        } else {
            echo "You do not have permission to download this file.";
        }
    }
}

// ─── Permission Checks ─────────────────────────────────────────────

/**
 * Permission check for legacy DB downloads (unchanged from original).
 */
function DownloadPermissionCheck($encodedData){
    $decodedData = base64_decode($encodedData);
    list($username, $filename) = explode('|', $decodedData);
    if (isset($_SESSION['username']) && UserHasPermissionForArtistFile($_SESSION['username'], $username)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Permission check for legacy artist/client document access (unchanged).
 */
function UserHasPermissionForArtistFile($username, $artistID){
    if ($_SESSION['role'] == 'admin') {
        return true;
    }
    if ($_SESSION['username'] == $username) {
        return true;
    }
    return false;
}

/**
 * Permission check for elFinder filepath downloads.
 * Verifies the file is within one of the accessible volume roots.
 */
function UserHasAccessToElfinderPath($filepath) {
    $realFilepath = realpath($filepath);
    if ($realFilepath === false) return false;
    
    // Normalize to forward slashes for comparison
    $realFilepath = str_replace('\\', '/', $realFilepath);
    $root = str_replace('\\', '/', __ROOT__);
    
    $allowedRoots = [
        $root . '/files/Dropboxes',
        $root . '/files/Projects',
        $root . '/files/Resources',
        $root . '/files/Corporate',
        // Client-specific paths will be checked separately via DB
    ];
    
    foreach ($allowedRoots as $volumeRoot) {
        if (strpos($realFilepath, $volumeRoot) === 0) {
            return true;
        }
    }
    
    // Also check if this is a client project path by querying active_path
    // (This catches clientUpload folders inside project dirs)
    return false;
}

// ─── File Serving ──────────────────────────────────────────────────

/**
 * Serve a file directly from the filesystem (elFinder download path).
 * 
 * If ?thumb=1 (or ?thumbnail=1) is present, serves the cached thumbnail
 * from /files/.tmb/ instead of the original file.
 */
function ServeElfinderFile($filepath) {
    if (!UserHasAccessToElfinderPath($filepath)) {
        echo "You do not have permission to access this file.";
        return;
    }

    $realPath = realpath($filepath);
    if ($realPath === false || !file_exists($realPath)) {
        echo "File not found.";
        return;
    }

    // ── Thumbnail mode ──────────────────────────────────────────
    $thumbMode = isset($_GET['thumb']) || isset($_GET['thumbnail']);
    
    if ($thumbMode) {
        // Compute thumbnail name matching SimeckVolumeDriver::tmbname()
        // Format: md5(realPath) . filemtime . '.png'
        $tmbName = md5($realPath) . filemtime($realPath) . '.png';
        $tmbPath = __ROOT__ . '/files/.tmb/' . $tmbName;
        
        if (!file_exists($tmbPath)) {
            http_response_code(404);
            echo "Thumbnail not available.";
            return;
        }
        
        header('Content-Description: Thumbnail');
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="' . $tmbName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tmbPath));
        readfile($tmbPath);
        exit;
    }

    // ── Normal (original file) mode ─────────────────────────────
    // Detect MIME type from the file extension
    $mimeTypes = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'bmp'  => 'image/bmp',
        'svg'  => 'image/svg+xml',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'pdf'  => 'application/pdf',
    ];
    
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
    
    // Images and videos get inline disposition (for Discord embeds), everything else gets attachment
    $inlineTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml', 'video/mp4', 'video/webm'];
    $disposition = in_array($contentType, $inlineTypes) ? 'inline' : 'attachment';

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($realPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($realPath));
    readfile($realPath);
    exit;
}


/**
 * Look up and serve a file by DB record (legacy artistdocuments/clientdocuments flow).
 */
function ServeFileForDownload($username, $docID){
    $pdo = DBConnect();
    
    $stmt = $pdo->prepare("SELECT filepath FROM artistdocuments WHERE owner = ? AND uploadID = ?");
    $stmt->execute([$username, $docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        $stmt = $pdo->prepare("SELECT filepath FROM clientdocuments WHERE owner = ? AND uploadID = ?");
        $stmt->execute([$username, $docID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$result) {
        echo "File not found.";
        return;
    }
    
    $filePath = __ROOT__ . $result['filepath'];
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
}
