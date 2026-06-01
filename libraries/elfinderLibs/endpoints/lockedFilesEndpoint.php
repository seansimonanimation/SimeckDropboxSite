<?php
/**
 * API endpoint for file locking operations.
 * Called via AJAX from elFinder frontend.
 *
 * Actions:
 *   query  – Get locked files under a directory
 *   lock   – Lock a file (admin/artist only)
 *   Override – Client override of comment lock (client only)
 *   check  – Check lock status of a single file
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
$GLOBALS['db'] = DBConnect();

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) { //action is what brings us to this endpoint.

    case 'query':
        //Safety check against directories.
        $directory = $_POST['directory'] ?? '';
        if (!$directory) {
            echo json_encode(['success' => false, 'error' => 'You can\'t lock directories!']);
            exit;
        }
        $locked = GetLockedFilesForDirectoryRecursively($directory);
        // Return full lock info
        echo json_encode(['success' => true, 'locked' => $locked]);
        break;


    case 'lock':
        // Only admins can lock
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin'])) {
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        $filepath = $_POST['filepath'] ?? '';
        if (!$filepath) {
            echo json_encode(['success' => false, 'error' => 'No filepath provided']);
            exit;
        }
        // Validate filepath starts with /files/Projects
        if (strpos($filepath, '/files/Projects') !== 0) {
            echo json_encode(['success' => false, 'error' => 'Locking is only allowed for project files']);
            exit;
        }
        // Check if already locked
        if (IsFileLocked($filepath)) {
            echo json_encode(['success' => false, 'error' => 'File is already locked']);
            exit;
        }
        $assetlock  = (int)($_POST['assetlock'] ?? 1);
        $commentlock = (int)($_POST['commentlock'] ?? 1);
        $stmt = $GLOBALS['db']->prepare(
            'INSERT INTO lockedfiles (filepath, locktime, assetlock, commentlock) VALUES (?, NOW(), ?, ?)'
        );
        $stmt->execute([$filepath, $assetlock, $commentlock]);
        echo json_encode(['success' => true]);
        break;

    case 'unlock':
        // Only admins and artists can unlock
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'artist'])) {
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        $filepath = $_POST['filepath'] ?? '';
        if (!$filepath) {
            echo json_encode(['success' => false, 'error' => 'No filepath provided']);
            exit;
        }
        $stmt = $GLOBALS['db']->prepare('DELETE FROM lockedfiles WHERE filepath = ?');
        $stmt->execute([$filepath]);
        echo json_encode(['success' => true]);
        break;

    case 'check':
        $filepath = $_POST['filepath'] ?? '';
        if (!$filepath) {
            echo json_encode(['success' => false, 'error' => 'No filepath provided']);
            exit;
        }
        $lock = IsFileLocked($filepath);
        echo json_encode([
            'success' => true,
            'locked'  => $lock !== false,
            'details' => $lock
        ]);
        break;
    case 'override':
        // Only clients can use lock overrides
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'client') {
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        $filepath = $_POST['filepath'] ?? '';
        if (!$filepath) {
            echo json_encode(['success' => false, 'error' => 'No filepath provided']);
            exit;
        }
        // Validate filepath starts with /files/Projects
        if (strpos($filepath, '/files/Projects') !== 0) {
            echo json_encode(['success' => false, 'error' => 'Override is only allowed for project files']);
            exit;
        }
        // Check client has overrides available
        $overrides = GetClientLockOverrides($_SESSION['username']);
        if ($overrides <= 0) {
            echo json_encode(['success' => false, 'error' => 'No lock overrides remaining']);
            exit;
        }
        // Check the file is locked and commentlock is still 1
        $lock = IsFileLocked($filepath);
        if (!$lock) {
            echo json_encode(['success' => false, 'error' => 'File is not locked']);
            exit;
        }
        if ((int)$lock['commentlock'] === 0) {
            echo json_encode(['success' => false, 'error' => 'Comment lock has already been overridden on this file']);
            exit;
        }
        // Perform the override: set commentlock to 0
        $stmt = $GLOBALS['db']->prepare(
            'UPDATE lockedfiles SET commentlock = 0 WHERE filepath = ?'
        );
        $stmt->execute([$filepath]);
        // Consume one override token
        ConsumeClientLockOverride($_SESSION['username']);
        echo json_encode(['success' => true, 'remaining_overrides' => $overrides - 1]);
        break;
    case 'get_client_overrides':
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'client') {
            echo json_encode(['success' => false, 'overrides' => 0]);
            exit;
        }
        $overrides = GetClientLockOverrides($_SESSION['username']);
        echo json_encode(['success' => true, 'overrides' => $overrides]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
