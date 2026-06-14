<?php
/**
 * getElfinderDownloadLinksEndpoint.php
 * 
 * Accepts an array of elFinder file hashes and returns signed download URLs.
 * This keeps the HMAC secret server-side.
 * 
 * POST params:
 *   hashes[] - array of elFinder hash strings
 * 
 * Returns JSON:
 *   { success: true|false, urls: [...], error: "..." }
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

// Load the elFinder connector to gain access to volume decoding
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

$hashes = $_POST['hashes'] ?? [];
if (!is_array($hashes) || empty($hashes)) {
    echo json_encode(['success' => false, 'error' => 'No hashes provided.']);
    exit;
}

// Build the elFinder volumes for the current user's role so we can decode hashes
$role = $_SESSION['tempRole'] ?? $_SESSION['role'] ?? 'artist';

switch ($role) {
    case 'admin':
        $elfinderOptions = getAdminFileBrowserOptions();
        break;
    case 'artist':
        $elfinderOptions = getArtistFileBrowserOptions();
        break;
    case 'client':
        $elfinderOptions = getClientFileBrowserOptions();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown role.']);
        exit;
}

// Collect the filepath for each volume so we can reject paths outside them
$volumeRoots = [];
foreach ($elfinderOptions['roots'] as $root) {
    if (isset($root['path'])) {
        $volumeRoots[] = rtrim(str_replace('\\', '/', $root['path']), '/');
    }
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
           . $_SERVER['HTTP_HOST'];

$downloadUrls = [];
$errors = [];

foreach ($hashes as $hash) {
    // Try to decode the hash through each volume
    $decodedPath = null;
    
    // Simpler approach: use fm_decode through the elFinder API
    // The connector already ran, but we need a decoder. Use base64 of the hash part.
    // elFinder hash format: volumeId_base64(path)
    // Where base64 uses - instead of +, _ instead of /, . instead of =
    
    $decodedPath = DecodeElfinderHash($hash, $elfinderOptions);
    
    if ($decodedPath === null) {
        $errors[] = "Could not decode hash: $hash";
        continue;
    }
    
    // Verify the decoded path is within a valid volume root
    $normalizedPath = str_replace('\\', '/', $decodedPath);
    $isValid = false;
    foreach ($volumeRoots as $vRoot) {
        if (strpos($normalizedPath, $vRoot) === 0) {
            $isValid = true;
            break;
        }
    }
    
    if (!$isValid) {
        $errors[] = "Path outside allowed volumes: $decodedPath";
        continue;
    }
    
    // Check the file actually exists
    if (!file_exists($decodedPath)) {
        $errors[] = "File not found on disk: $decodedPath";
        continue;
    }
    
    // Generate the signed token and build URL
    $token = GenerateElfinderDownloadToken($decodedPath);
    $downloadUrls[] = $baseUrl . '/download.php?download=' . urlencode($token);
}

echo json_encode([
    'success' => count($downloadUrls) > 0,
    'urls'    => $downloadUrls,
    'errors'  => $errors,
    'total_requested' => count($hashes),
    'total_generated' => count($downloadUrls)
]);