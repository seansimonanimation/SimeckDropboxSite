<?php
/**
 * serveTmb.php
 *
 * Serve elFinder cached thumbnails through PHP so /files/ can remain internal.
 * Only serves files from /files/.tmb/ and requires an authenticated session.
 */

if (!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}

require_once __ROOT__ . '/libraries/session.php';

if (empty($_SESSION['username'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$filename = $_GET['file'] ?? '';
$filename = basename($filename);

if ($filename === '' || preg_match('/[^A-Za-z0-9._-]/', $filename)) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$thumbPath = __ROOT__ . '/files/.tmb/' . $filename;

if (!is_file($thumbPath) || !is_readable($thumbPath)) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$ext = strtolower(pathinfo($thumbPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'bmp'  => 'image/bmp',
];

$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . filesize($thumbPath));

readfile($thumbPath);
exit;