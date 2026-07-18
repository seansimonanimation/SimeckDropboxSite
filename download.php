<?php
/**
 * download.php - Unified download handler with V1/V2 token support
 * 
 * Token format detection:
 *   V2 (encrypted): base64_decode -> 2 pipe parts (elFinder|<encrypted>)
 *   V1 (legacy):    base64_decode -> 3 pipe parts (elFinder|filepath|hmac)
 *   Legacy DB:      base64_decode -> 3 pipe parts (username|docID|hmac)
 * 
 * V2 modes:
 *   internal      - Full resolution, no watermark (artists/admins)
 *   clientPreview - 800px max, watermarked (client elFinder previews)
 *   thumbnail     - elFinder .tmb cached thumbnail
 *   deliverable   - Full resolution, no watermark (client "download full" links)
 * 
 * Watermark: /globalSiteAssets/simeck-logo.png at 20% opacity
 * Cached in: /files/.watermarked/ (JPEG, auto-invalidates on file change)
 */

include_once __DIR__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
require_once __ROOT__ . '/libraries/tokenlib.php';

// Look for the secret in the Docker config location; fall back to a local config
if (file_exists('/var/www/dbconfig.php')) {
    include_once '/var/www/dbconfig.php';
} elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
    include_once __ROOT__ . '/dbconfig.php';
} elseif (file_exists(__ROOT__ . '/../dbconfig.php')) {
    include_once __ROOT__ . '/../dbconfig.php';
}
if (isset($_GET['download'])) {
    InitiateDownload($_GET['download']);
}
// ─── Hash-based Preview ──────────────────────────────────────────
// Accepts ?hash= (elfinder hash) for same-session file serving.
// Resolves hash to real path, validates access, serves with role-based mode.
if (isset($_GET['hash'])) {
    require_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
    require_once __ROOT__ . '/libraries/elfinderLibs/volumeConfig.php';
    
    $elfinderOptions = GetRoleElfinderOptions();
    $decodedPath = DecodeElfinderHash($_GET['hash'], $elfinderOptions);
    if ($decodedPath === null) {
        http_response_code(404);
        echo "File not found.";
        exit;
    }
    
    $role = $_SESSION['tempRole'] ?? $_SESSION['role'] ?? 'artist';
    $mode = ($role === 'client') ? 'clientPreview' : 'internal';
    
    ServeElfinderFile($decodedPath, $mode);
    exit;
}

// ─── Token Generation (see libraries/tokenlib.php) ──────────────────
//   GenerateElfinderDownloadToken()  → V2 elFinder token
//   Generateb64EncodedDownloadLink() → V1 legacy DB document token

// ─── Token Dispatch ────────────────────────────────────────────────

/**
 * Main dispatch: detects token version (V1 vs V2) and routes accordingly.
 */
function InitiateDownload($encodedData) {
    if (!str_contains($encodedData, '|')) {
        require_once __ROOT__ . '/libraries/shortlinklib.php';
        $result = ResolveShortlink($encodedData);
        if ($result['valid']) {
            InitiateDownload($result['download_token']);
            return;
        }
    }
    $decoded = base64_decode($encodedData, true);
    if ($decoded === false || empty($decoded)) {
        echo "Invalid download token.";
        return;
    }
    $parts = explode('|', $decoded);
    if (count($parts) === 2 && $parts[0] === 'elFinder') {
        $encryptedBlob = $parts[1];
        $plaintext = decryptImportantData($encryptedBlob);
        if ($plaintext === false) {
            echo "Invalid or tampered download token.";
            return;
        }
        $inner = explode('|', $plaintext);
        if (count($inner) < 3) {
            echo "Invalid download token format.";
            return;
        }
        $author   = $inner[0];
        $mode     = $inner[1];
        $filepath = implode('|', array_slice($inner, 2));
        ServeElfinderFile($filepath, $mode, $author);
        return;
    }
    if (count($parts) === 3) {
        $first       = $parts[0];
        $identifier  = $parts[1];
        $providedSig = $parts[2];
        $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
        $expectedSig = hash_hmac('sha256', $identifier, $secret);
        if (!hash_equals($expectedSig, $providedSig)) {
            echo "Invalid or tampered download token.";
            return;
        }
        if ($first === 'elFinder') {
            ServeElfinderFile($identifier, 'internal', 'legacy');
        } else {
            $b64Legacy = base64_encode($first . '|' . $identifier);
            if (DownloadPermissionCheck($b64Legacy)) {
                ServeFileForDownload($first, $identifier);
            } else {
                echo "You do not have permission to download this file.";
            }
        }
        return;
    }
    echo "Invalid download token format.";
}



// ─── Permission Checks ─────────────────────────────────────────────

function DownloadPermissionCheck($encodedData){
    $decodedData = base64_decode($encodedData);
    list($username, $filename) = explode('|', $decodedData);
    if (isset($_SESSION['username']) && UserHasPermissionForArtistFile($_SESSION['username'], $username)) {
        return true;
    } else {
        return false;
    }
}

function UserHasPermissionForArtistFile($username, $artistID){
    if ($_SESSION['role'] == 'admin') {
        return true;
    }
    if ($_SESSION['username'] == $username) {
        return true;
    }
    return false;
}

function UserHasAccessToElfinderPath($filepath) {
    $realFilepath = realpath($filepath);
    if ($realFilepath === false) return false;
    
    $realFilepath = str_replace('\\', '/', $realFilepath);
    $root = str_replace('\\', '/', __ROOT__);
    
    $allowedRoots = [
        $root . '/files/Dropboxes',
        $root . '/files/Projects',
        $root . '/files/Resources',
        $root . '/files/Corporate',
    ];
    
    foreach ($allowedRoots as $volumeRoot) {
        if (strpos($realFilepath, $volumeRoot) === 0) {
            return true;
        }
    }
    
    return false;
}

// ─── File Serving ──────────────────────────────────────────────────

/**
 * Route to the appropriate serving function based on mode.
 */
function ServeElfinderFile($filepath, $mode = 'internal', $author = 'unknown') {
    if (!UserHasAccessToElfinderPath($filepath)) {
        echo "You do not have permission to access this file.";
        return;
    }

    $realPath = realpath($filepath);
    if ($realPath === false || !file_exists($realPath)) {
        echo "File not found.";
        return;
    }

    switch ($mode) {
        case 'thumbnail':
            ServeThumbnail($realPath);
            break;
        case 'clientPreview':
            ServeWatermarkedImage($realPath, 800, $author);
            break;
        case 'internal':
        case 'deliverable':
        default:
            ServeFullFile($realPath);
            break;
    }
}

/**
 * Serve the elFinder cached thumbnail from /files/.tmb/
 */
function FindThumbnailFile($realPath) {
    $tmbDir = __ROOT__ . '/files/.tmb/';
    if (!is_dir($tmbDir)) {
        return null;
    }

    $prefix = md5($realPath);

    // First try the modern elFinder-style naming, which is usually
    // "<md5(path)>...(.png)".
    $candidates = glob($tmbDir . $prefix . '*');
    if ($candidates !== false && !empty($candidates)) {
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true)) {
                    return $candidate;
                }
            }
        }
    }

    // Fallback for the older naming convention used in some environments.
    $legacyPath = $tmbDir . $prefix . filemtime($realPath) . '.png';
    if (file_exists($legacyPath) && is_file($legacyPath)) {
        return $legacyPath;
    }

    return null;
}

function ServeThumbnail($realPath) {
    $tmbPath = FindThumbnailFile($realPath);

    if ($tmbPath === null || !file_exists($tmbPath) || !is_readable($tmbPath)) {
        http_response_code(404);
        echo "Thumbnail not available.";
        return;
    }

    $ext = strtolower(pathinfo($tmbPath, PATHINFO_EXTENSION));
    $contentType = 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $contentType = 'image/jpeg';
    } elseif ($ext === 'gif') {
        $contentType = 'image/gif';
    } elseif ($ext === 'webp') {
        $contentType = 'image/webp';
    } elseif ($ext === 'bmp') {
        $contentType = 'image/bmp';
    }

    header('Content-Description: Thumbnail');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . basename($tmbPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($tmbPath));
    readfile($tmbPath);
    exit;
}

/**
 * Serve a watermarked version of an image.
 * Resizes to max 800px on the longest side, overlays the Simeck logo at 20% opacity.
 * Results are cached in /files/.watermarked/ as JPEG.
 * Falls back to ServeFullFile() on any failure.
 */
function ServeWatermarkedImage($realPath, $maxDimension = 800, $author = 'unknown') {
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $imageTypes = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
    
    if (!in_array($ext, $imageTypes)) {
        ServeFullFile($realPath);
        return;
    }
    
    // Ensure cache directory
    $cacheDir = __ROOT__ . '/files/.watermarked/';
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
            ServeFullFile($realPath);
            return;
        }
    }
    
    $cacheKey = md5($realPath . filemtime($realPath)) . '.jpg';
    $cachePath = $cacheDir . $cacheKey;
    
    if (!file_exists($cachePath)) {
        // Load source image via GD
        $srcImage = null;
        switch ($ext) {
            case 'png':   $srcImage = @imagecreatefrompng($realPath); break;
            case 'jpg':
            case 'jpeg':  $srcImage = @imagecreatefromjpeg($realPath); break;
            case 'gif':   $srcImage = @imagecreatefromgif($realPath); break;
            case 'webp':  $srcImage = @imagecreatefromwebp($realPath); break;
            case 'bmp':   $srcImage = @imagecreatefrombmp($realPath); break;
        }
        
        if (!$srcImage) {
            ServeFullFile($realPath);
            return;
        }
        
        $origW = imagesx($srcImage);
        $origH = imagesy($srcImage);
        
        // Resize to max 800px on the longest side
        if ($origW > $maxDimension || $origH > $maxDimension) {
            $ratio = min($maxDimension / $origW, $maxDimension / $origH);
            $newW = (int)round($origW * $ratio);
            $newH = (int)round($origH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($srcImage);
            $srcImage = $resized;
        }
        
        // Apply watermark logo at 20% opacity
        $logoPath = __ROOT__ . '/globalSiteAssets/simeck-logo.png';
        if (file_exists($logoPath)) {
            $logoImg = @imagecreatefrompng($logoPath);
            if ($logoImg) {
                $imgW = imagesx($srcImage);
                $imgH = imagesy($srcImage);
                $logoW = imagesx($logoImg);
                $logoH = imagesy($logoImg);

                // Scale logo to 60% of image width, maintaining aspect ratio
                $scaleLogo = min(0.6 * $imgW / $logoW, 0.6 * $imgH / $logoH, 1);
                $newLogoW = (int)round($logoW * $scaleLogo);
                $newLogoH = (int)round($logoH * $scaleLogo);

                $overlay = imagecreatetruecolor($imgW, $imgH);
                imagefill($overlay, 0, 0, imagecolorallocatealpha($overlay, 0, 0, 0, 127));
                imagesavealpha($overlay, true);

                $destX = (int)(($imgW - $newLogoW) / 2);
                $destY = (int)(($imgH - $newLogoH) / 2);
                imagecopyresampled($overlay, $logoImg, $destX, $destY, 0, 0, $newLogoW, $newLogoH, $logoW, $logoH);

                
                // Merge overlay onto source at 20% opacity
                imagecopymerge($srcImage, $overlay, 0, 0, 0, 0, $imgW, $imgH, 15);
                
                imagedestroy($overlay);
                imagedestroy($logoImg);
            }
        }
        
        // Save as JPEG (quality 85)
        $saved = imagejpeg($srcImage, $cachePath, 85);
        imagedestroy($srcImage);
        
        if (!$saved) {
            ServeFullFile($realPath);
            return;
        }
    }
    
    header('Content-Description: Watermarked Preview');
    header('Content-Type: image/jpeg');
    header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($cachePath));
    readfile($cachePath);
    exit;
}

/**
 * Serve the full original file.
 */
function ServeFullFile($realPath) {
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
 * Look up and serve a file by DB record (legacy artistdocuments/clientdocuments).
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
    $stmt = $pdo->prepare("SELECT filepath FROM vendordocuments WHERE owner = ? AND uploadID = ?");
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
