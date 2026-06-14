<?php

/**
 * API endpoint that has ONE JOB. To return a list of locked files under a given directory.
 * Called via AJAX from elFinder frontend.
 * Actions:
 *   directory - Returns all locked files under the given directory (recursively). Expects a 'directory' POST parameter with the root-relative path, e.g. "/files/Projects/internal/P01_C City/"
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
$GLOBALS['db'] = DBConnect();

header('Content-Type: application/json');

$requestDir = $_REQUEST['directory'] ?? '';
if (!$requestDir) {
    $requestDir = '/files/Projects';
}

$SQLQuery = 'SELECT filepath, assetlock, commentlock FROM lockedfiles WHERE filepath LIKE ?';
$stmt = $GLOBALS['db']->prepare($SQLQuery);
$stmt->execute([rtrim($requestDir, '/') . '/%']);
$lockedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'lockedFiles' => $lockedFiles]);