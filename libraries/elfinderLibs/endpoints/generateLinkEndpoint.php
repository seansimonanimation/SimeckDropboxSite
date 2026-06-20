<?php
/**
 * generateLinkEndpoint.php
 *
 * Unified endpoint for generating download links (both shortlinks and permalinks).
 *
 * POST params:
 *   hash - elFinder file hash
 *   type - "shortlink" (14-day expiry) or "permalink" (no expiry)
 *   mode - "internal" (full res), "clientPreview" (watermarked),
 *          "thumbnail" (cached thumbnail), "deliverable" (full res)
 *
 * Returns JSON:
 *   { success: true, url: "https://..." }
 *   { success: false, error: "..." }
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
include_once __ROOT__ . '/libraries/shortlinklib.php';

require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';
require_once __ROOT__ . '/libraries/elfinderLibs/SimeckVolumeDriver.php';
require_once __ROOT__ . '/libraries/elfinderLibs/volumeConfig.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required.']);
    exit;
}

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

$hash   = $_POST['hash'] ?? '';
$type   = $_POST['type'] ?? '';
$mode   = $_POST['mode'] ?? '';

if (empty($hash) || empty($type) || empty($mode)) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters (hash, type, mode).']);
    exit;
}

$validTypes = ['shortlink', 'permalink'];
$validModes = ['internal', 'clientPreview', 'thumbnail', 'deliverable'];

if (!in_array($type, $validTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid type. Must be shortlink or permalink.']);
    exit;
}
if (!in_array($mode, $validModes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid mode.']);
    exit;
}

$elfinderOptions = GetRoleElfinderOptions();

$decodedPath = DecodeElfinderHash($hash, $elfinderOptions);
if ($decodedPath === null) {
    echo json_encode(['success' => false, 'error' => 'Could not decode file hash.']);
    exit;
}

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

if (!$isValid) {
    echo json_encode(['success' => false, 'error' => 'File is outside allowed volumes.']);
    exit;
}

if (!file_exists($decodedPath)) {
    echo json_encode(['success' => false, 'error' => 'File not found on disk.']);
    exit;
}

$v2Token = GenerateElfinderDownloadToken($decodedPath, $mode);
if ($v2Token === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate download token.']);
    exit;
}

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$baseUrl = $scheme . $_SERVER['HTTP_HOST'];

if ($type === 'shortlink') {
    $expiry = date('Y-m-d H:i:s', strtotime('+14 days'));
    $shortId = CreateShortlink($v2Token, $expiry);
    if ($shortId === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to create shortlink record.']);
        exit;
    }
    $url = $baseUrl . '/download.php?download=' . urlencode($shortId);
} else {
    $url = $baseUrl . '/download.php?download=' . urlencode($v2Token);
}

echo json_encode([
    'success' => true,
    'url'     => $url,
]);
