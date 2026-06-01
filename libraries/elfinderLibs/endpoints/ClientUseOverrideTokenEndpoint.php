<?php
/**
 * API endpoint that has ONE JOB. To consume a client override token and mark the corresponding lock as overridden.
 * Called via AJAX from elFinder frontend.
 * Actions:
 *   No actions. It knows what it's about.
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
$GLOBALS['db'] = DBConnect();
header('Content-Type: application/json');
$filePath = $_REQUEST['filepath'] ?? '';
if(!$filePath) {
    echo json_encode(['success' => false, 'error' => 'No filepath provided']);
    exit;
}


$lockOverridesAvailable = GetClientLockOverrides($_SESSION['username'] ?? '');

//We should never be here... The button should never appear if there are none available.
if ($lockOverridesAvailable <= 0) {
    echo json_encode(['success' => false, 'error' => 'No lock overrides available']);
    exit;
}
$SQLUserString = "UPDATE clients SET lock_overrides = ? WHERE username = ?";
$stmt1 = $GLOBALS['db']->prepare($SQLUserString);
$stmt1->execute([$lockOverridesAvailable - 1, $_SESSION['username'] ?? '']);


$SQLOverrideString = "UPDATE lockedfiles SET commentLock = 0 WHERE filepath = ?";
$stmt2 = $GLOBALS['db']->prepare($SQLOverrideString);
$stmt2->execute([$filePath]);

echo json_encode(['success' => true, 'message' => 'Lock override applied successfully', 'overridesLeft' => $lockOverridesAvailable - 1]);