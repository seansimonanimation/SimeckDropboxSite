<?php
// previewPdf.php
// PDF preview endpoint using the browser's native PDF viewer.
// Serves the file with Content-Disposition: inline so the browser
// renders PDF inline rather than triggering a download.
// Accepts ?url= (elFinder file URL)
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/libraries/session.php';
require_once __ROOT__ . '/libraries/db.php';

// ─── Parameter parsing ───────────────────────────────────────────────
$path = $_GET['url'] ?? $_GET['path'] ?? '';
if (!$path) {
    http_response_code(400);
    echo 'Missing path or url parameter';
    exit;
}
$path = urldecode($path);
$path = str_replace('\\', '/', $path);

if (strpos($path, '/') === 0) {
    $filePath = rtrim(__ROOT__, '/\\') . $path;
} else {
    $filePath = rtrim(__ROOT__, '/\\') . '/' . ltrim($path, '/');
}

if (!file_exists($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    echo 'File not found: ' . htmlspecialchars($filePath);
    exit;
}

// ─── Extension validation ────────────────────────────────────────────
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(400);
    echo 'Unsupported file type';
    exit;
}

// ─── Permission check ───────────────────────────────────────────────
// Reuse the same permission function from download.php
require_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';

$realPath = realpath($filePath);
if ($realPath === false) {
    http_response_code(500);
    echo 'Unable to resolve file path.';
    exit;
}

// Simple permission check using the same volume-root logic
$root = str_replace('\\', '/', __ROOT__);
$realNormalized = str_replace('\\', '/', $realPath);

$allowedRoots = [
    $root . '/files/Dropboxes',
    $root . '/files/Projects',
    $root . '/files/Resources',
    $root . '/files/Corporate',
];

$hasAccess = false;
foreach ($allowedRoots as $volumeRoot) {
    if (strpos($realNormalized, $volumeRoot) === 0) {
        $hasAccess = true;
        break;
    }
}

if (!$hasAccess) {
    http_response_code(403);
    echo 'You do not have permission to access this file.';
    exit;
}

// ─── Serve the PDF ──────────────────────────────────────────────────
$fileName = basename($realPath);
$fileSize = filesize($realPath);

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');

readfile($realPath);
exit;
