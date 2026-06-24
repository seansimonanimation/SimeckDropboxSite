<?php
/**
 * linkEndpoint.php - Server endpoint for linklib.js
 *
 * POST params:
 *   hash - elFinder file hash
 *   type - "shortlink" or "permalink" (default: "permalink")
 *   mode - "internal", "clientPreview", "thumbnail", "deliverable" (default: "internal")
 *
 * Returns JSON:
 *   { success: true, url: "https://..." }
 *   { success: false, error: "..." }
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/linklib.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required.']);
    exit;
}

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

$hash = $_POST['hash'] ?? '';
$type = $_POST['type'] ?? 'permalink';
$mode = $_POST['mode'] ?? 'internal';

if (empty($hash)) {
    echo json_encode(['success' => false, 'error' => 'Missing hash parameter.']);
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

$url = MakeLinkFromHash($hash, $mode, $type);
if ($url === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate link. Archive or invalid file.']);
    exit;
}

echo json_encode(['success' => true, 'url' => $url]);
