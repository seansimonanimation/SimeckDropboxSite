<?php
/**
 * libraries/downloadTokenAnalyzerLib.php
 *
 * Analyzes Simeck download tokens (V1 and V2) to extract author, mode,
 * and filepath information. Used by the admin dashboard link analyzer.
 */

if (!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}

require_once __ROOT__ . '/libraries/encryptlib.php';
require_once __ROOT__ . '/libraries/tokenlib.php';

/**
 * Decode and analyze a download token.
 *
 * Accepts either a raw base64 token string, or a full URL like
 * "download.php?download=TOKEN".
 *
 * @param  string  $input  The token or URL to analyze
 * @return array            ['success' => bool, ...data...]
 */
function AnalyzeDownloadToken($input)
{
    // Strip full URL down to just the token
    if (strpos($input, 'download.php?download=') !== false) {
        parse_str(parse_url($input, PHP_URL_QUERY), $params);
        $input = $params['download'] ?? $input;
    }

    $decoded = base64_decode($input, true);
    if ($decoded === false || empty($decoded)) {
        return ['success' => false, 'error' => 'Invalid base64 encoding.'];
    }

    $parts = explode('|', $decoded);

    // ── V2 Token (2 parts: elFinder|<encrypted>) ─────────────────────
    if (count($parts) === 2 && $parts[0] === 'elFinder') {
        $plaintext = decryptImportantData($parts[1]);
        if ($plaintext === false) {
            return ['success' => false, 'error' => 'Failed to decrypt token (wrong key or tampered data).'];
        }
        $inner = explode('|', $plaintext);
        if (count($inner) < 3) {
            return ['success' => false, 'error' => 'Decrypted token has unexpected format.'];
        }
        $author   = $inner[0];
        $mode     = $inner[1];
        $filepath = implode('|', array_slice($inner, 2));

        return [
            'success'  => true,
            'version'  => 'V2',
            'author'   => $author,
            'mode'     => $mode,
            'filepath' => $filepath,
            'filename' => basename($filepath),
        ];
    }

    // ── V1 Token (3 parts: elFinder|filepath|hmac) ──────────────────
    if (count($parts) === 3) {
        $first       = $parts[0];
        $identifier  = $parts[1];
        $providedSig = $parts[2];

        $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
        $expectedSig = hash_hmac('sha256', $identifier, $secret);

        if (!hash_equals($expectedSig, $providedSig)) {
            return ['success' => false, 'error' => 'HMAC signature is invalid.'];
        }

        if ($first === 'elFinder') {
            return [
                'success'  => true,
                'version'  => 'V1',
                'author'   => 'legacy',
                'mode'     => 'internal',
                'filepath' => $identifier,
                'filename' => basename($identifier),
            ];
        } else {
            // Legacy DB document token (username|docID|hmac)
            return [
                'success'  => true,
                'version'  => 'V1',
                'author'   => $first,
                'mode'     => 'document',
                'filepath' => 'DB document #' . $identifier,
                'filename' => 'DB document #' . $identifier,
            ];
        }
    }

    return ['success' => false, 'error' => 'Unknown token format (' . count($parts) . ' parts).'];
}

/**
 * Look up an author's display name from the artists table.
 *
 * @param  string  $username  The username to look up
 * @return string             "Firstname Lastname" or the username as fallback
 */
function LookupAuthorDisplayName($username)
{
    if ($username === 'legacy' || $username === 'unknown') {
        return ucfirst($username);
    }

    try {
        $pdo = DBConnect();
        $stmt = $pdo->prepare("SELECT firstname, lastname FROM artists WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        }
    } catch (\Exception $e) {
        // Fall through to return username
    }

    return $username;
}

/**
 * Generate a V2 thumbnail download token for a filepath.
 *
 * @param  string  $filepath  Absolute server path to the file
 * @return string|false       Base64-encoded token, or false on failure
 */
function GetFileThumbnailUrl($filepath)
{
    return GenerateElfinderDownloadToken($filepath, 'thumbnail');
}

/**
 * Generate a V2 internal-mode download token for preview.
 *
 * @param  string  $filepath  Absolute server path to the file
 * @return string|false       Base64-encoded token, or false on failure
 */
function GetFilePreviewUrl($filepath)
{
    return GenerateElfinderDownloadToken($filepath, 'internal');
}

/**
 * Generate a floating island HTML snippet for previewing a file.
 *
 * Determines the file type by extension and renders an appropriate
 * preview element (img for images, video for videos, iframe for PDFs,
 * download link for everything else).
 *
 * @param  string  $filepath  Absolute server path to the file
 * @return string             Floating island HTML, or error message
 */
function GetFilePreviewIsland($filepath)
{
    require_once __ROOT__ . '/libraries/floatingIslandLib.php';

    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    $filename = basename($filepath);
    $previewToken = GetFilePreviewUrl($filepath);

    if (!$previewToken) {
        return '<p>Failed to generate preview token.</p>';
    }

    $downloadUrl = 'download.php?download=' . urlencode($previewToken);

    $imageExts  = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];
    $videoExts  = ['mp4', 'webm', 'mov', 'avi', 'mkv'];
    $audioExts  = ['mp3', 'wav', 'ogg', 'flac', 'aac'];
    $pdfExts    = ['pdf'];
    $codeExts   = ['txt', 'php', 'js', 'css', 'html', 'json', 'xml', 'md', 'sql', 'py', 'ts'];

    if (in_array($ext, $imageExts)) {
        $content = '<img src="' . htmlspecialchars($downloadUrl) . '" style="max-width:100%;max-height:80vh;display:block;margin:0 auto;" alt="' . htmlspecialchars($filename) . '">';
    } elseif (in_array($ext, $videoExts)) {
        $content = '<video controls style="max-width:100%;max-height:80vh;display:block;margin:0 auto;"><source src="' . htmlspecialchars($downloadUrl) . '" type="video/' . htmlspecialchars($ext) . '">Your browser does not support video playback.</video>';
    } elseif (in_array($ext, $audioExts)) {
        $content = '<audio controls style="width:100%;"><source src="' . htmlspecialchars($downloadUrl) . '" type="audio/' . htmlspecialchars($ext) . '">Your browser does not support audio playback.</audio>';
    } elseif (in_array($ext, $pdfExts)) {
        $content = '<iframe src="' . htmlspecialchars($downloadUrl) . '" style="width:100%;height:70vh;border:none;"></iframe>';
    } elseif (in_array($ext, $codeExts)) {
        // For text/code files, show a download link with a note
        $content = '<p style="margin-bottom:12px;">This is a text file. <a href="' . htmlspecialchars($downloadUrl) . '" target="_blank">Open in new tab</a> or download.</p>';
        $content .= '<pre style="max-height:60vh;overflow:auto;padding:12px;background:var(--color-bg-raised);border:1px solid var(--color-border-dim);border-radius:var(--radius-sm);font-size:0.85rem;">';
        $content .= htmlspecialchars(file_get_contents($filepath));
        $content .= '</pre>';
    } else {
        $content = '<p>Cannot preview this file type in the browser.</p>';
        $content .= '<p style="margin-top:12px;"><a href="' . htmlspecialchars($downloadUrl) . '" style="display:inline-block;padding:10px 20px;background:var(--color-accent);color:#fff;text-decoration:none;border-radius:var(--radius-sm);" download>Download ' . htmlspecialchars($filename) . '</a></p>';
    }

    return SpawnFloatingIsland($content, $filename);
}
