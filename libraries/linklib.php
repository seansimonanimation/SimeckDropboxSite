<?php
/**
 * libraries/linklib.php - Link generation orchestration layer
 * 
 * Combines tokenlib, shortlinklib, and elFinder hash decoding into
 * unified functions that return complete download URLs.
 */

require_once __ROOT__ . '/libraries/tokenlib.php';
require_once __ROOT__ . '/libraries/shortlinklib.php';
require_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

/**
 * Generate a complete download URL from a filepath.
 *
 * @param string $filepath  Absolute server filesystem path
 * @param string $mode      internal|clientPreview|thumbnail|deliverable
 * @param string $type      permalink|shortlink
 * @return string|false     Full URL (e.g. https://.../download.php?download=TOKEN), or false on failure
 */
function MakeLink($filepath, $mode = 'internal', $type = 'permalink') {
    $v2Token = GenerateElfinderDownloadToken($filepath, $mode);
    if ($v2Token === false) return false;
    
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $baseUrl = $scheme . $_SERVER['HTTP_HOST'];
    
    if ($type === 'shortlink') {
        $expiry = date('Y-m-d H:i:s', strtotime('+14 days'));
        $shortId = CreateShortlink($v2Token, $expiry);
        if ($shortId === false) return false;
        return $baseUrl . '/download.php?download=' . urlencode($shortId);
    }
    
    return $baseUrl . '/download.php?download=' . urlencode($v2Token);
}

/**
 * Generate a complete short download URL from a filepath.
 * Convenience wrapper around MakeLink(..., 'shortlink').
 */
function MakeShortlink($filepath, $mode = 'internal') {
    return MakeLink($filepath, $mode, 'shortlink');
}

/**
 * Generate a complete download URL from an elFinder hash.
 *
 * @param string $hash      elFinder file hash
 * @param string $mode      internal|clientPreview|thumbnail|deliverable
 * @param string $type      permalink|shortlink
 * @return string|false     Full URL, or false on failure
 */
function MakeLinkFromHash($hash, $mode = 'internal', $type = 'permalink') {
    $elfinderOptions = GetRoleElfinderOptions();
    $decodedPath = DecodeElfinderHash($hash, $elfinderOptions);
    if ($decodedPath === null) return false;
    
    $normalizedPath = str_replace('\\', '/', $decodedPath);
    $isValid = false;
    foreach ($elfinderOptions['roots'] as $root) {
        if (isset($root['path'])) {
            $vRoot = rtrim(str_replace('\\', '/', $root['path']), '/');
            if (strpos($normalizedPath, $vRoot) === 0) {
                $isValid = true;
                break;
            }
        }
    }
    if (!$isValid) return false;
    
    return MakeLink($decodedPath, $mode, $type);
}
