<?php
/**
 * API endpoint that has ONE JOB. To lock a file.
 * Called via AJAX from elFinder frontend.
 * Actions:
 * filepath = takes a filepath to lock the file.
 * Admin and Project Lead only.
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
$GLOBALS['db'] = DBConnect();
header('Content-Type: application/json');
$filePath = $_REQUEST['filepath'] ?? '';
if(!$filePath) {
    echo json_encode(['success' => false, 'error' => 'No filepath provided']);
    exit;
}
$sqlQueryString = "SELECT lockid, filepath, locktime,assetlock, commentlock FROM lockedfiles WHERE filepath = ?";
$stmt1 = $GLOBALS['db']->prepare($SQLQueryString);
$stmt1 -> execute([$filepath]);
$existing = $stmt1->fetch(PDO::FETCH_ASSOC);
if ($existing) {
    echo json_encode(['success' => false, 'error' => 'File is already locked']);
    exit;
}


$SQLInsertString = 'INSERT INTO lockedfiles (filepath, locktime, assetlock, commentlock) VALUES (?, NOW(), 1, 1)';
$stmt2 = $GLOBALS['db']->prepare($SQLInsertString);
$stmt2->execute([$filepath]);

echo json_encode(['success' => true, 'message' => 'Lock applied successfully']);