<?php
/**
 * libraries/tokenlib.php - Download token generation (V1 and V2)
 * 
 * V1 (Legacy): base64("elFinder|filepath|hmac(filepath)")
 *   - 3 pipe-delimited parts, HMAC-signed with DOWNLOAD_SECRET
 * 
 * V2 (Current): base64("elFinder|" . AES-256-GCM("author|mode|filepath"))
 *   - 2 pipe-delimited parts, data encrypted with DB_ENCRYPTION_KEY
 *   - author: $_SESSION username of whoever generated the token
 *   - mode: internal|clientPreview|thumbnail|deliverable
 *   - filepath: absolute filesystem path
 * 
 * Backward compat: InitiateDownload() tries V2 decryption first,
 * falls back to V1 HMAC verification.
 */

require_once __DIR__ . '/encryptlib.php';

/**
 * Generate a V2 download token for an elFinder filepath.
 * 
 * @param string $filepath  Real server filesystem path to the file
 * @param string $mode      Token mode: internal, clientPreview, thumbnail, deliverable
 * @param string|null $author  Username who generated the token (default: $_SESSION['username'])
 * @return string|false     Base64-encoded token, or false on failure
 */
function GenerateElfinderDownloadToken($filepath, $mode = 'internal', $author = null) {
    if ($author === null) {
        $author = $_SESSION['username'] ?? 'unknown';
    }
    $plaintext = $author . '|' . $mode . '|' . $filepath;
    $encrypted = encryptImportantData($plaintext);
    if ($encrypted === false) {
        error_log('tokenlib.php: encryptImportantData failed for filepath: ' . $filepath);
        return false;
    }
    $tokenData = 'elFinder|' . $encrypted;
    return base64_encode($tokenData);
}

/**
 * Legacy V1: Generate download token for a DB-recorded document.
 * Preserved for backward compat with artistManagement, clientManagement, timeclock.
 * 
 * @param string $username The document owner's username
 * @param string $docID    The uploadID from the DB table
 * @return string          Base64-encoded token
 */
function Generateb64EncodedDownloadLink($username, $docID){
    $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
    $signature = hash_hmac('sha256', $docID, $secret);
    $tokenData = $username . '|' . $docID . '|' . $signature;
    return base64_encode($tokenData);
}
