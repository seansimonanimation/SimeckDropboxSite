<?php
/**
 * toggleDeliverableEndpoint.php
 *
 * Toggles the "deliverable" flag on a locked file record.
 * If no lockedfiles record exists for the file, creates one with deliverable=1.
 * If one exists, flips the deliverable value.
 *
 * POST params:
 *   hash - elFinder file hash
 *
 * Returns JSON:
 *   { success: true, deliverable: 1|0 }
 *   { success: false, error: "..." }
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';

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

$hash = $_POST['hash'] ?? '';
if (empty($hash)) {
    echo json_encode(['success' => false, 'error' => 'No file hash provided.']);
    exit;
}

$elfinderOptions = GetRoleElfinderOptions();

$decodedPath = DecodeElfinderHash($hash, $elfinderOptions);
if ($decodedPath === null) {
    echo json_encode(['success' => false, 'error' => 'Could not decode file hash.']);
    exit;
}

$normalized = NormalizeFilePath($decodedPath);
if ($normalized === false) {
    echo json_encode(['success' => false, 'error' => 'File path outside site root.']);
    exit;
}

$pdo = DBConnect();

$stmt = $pdo->prepare("SELECT lockid, deliverable FROM lockedfiles WHERE filepath = ?");
$stmt->execute([$normalized]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $newVal = $existing['deliverable'] ? 0 : 1;
    $update = $pdo->prepare("UPDATE lockedfiles SET deliverable = ? WHERE lockid = ?");
    $update->execute([$newVal, $existing['lockid']]);
    echo json_encode(['success' => true, 'deliverable' => $newVal]);
} else {
    $insert = $pdo->prepare(
        "INSERT INTO lockedfiles (filepath, locktime, assetlock, commentlock, deliverable) VALUES (?, NOW(), 0, 0, 1)"
    );
    $insert->execute([$normalized]);
    echo json_encode(['success' => true, 'deliverable' => 1]);
}
