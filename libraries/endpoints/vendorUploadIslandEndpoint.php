<?php
/**
 * vendorUploadIslandEndpoint.php
 * 
 * Dual-purpose:
 *   GET  → Returns floating island HTML with upload form
 *   POST → Processes the file upload, returns JSON
 */
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/libraries/session.php';
require_once __ROOT__ . '/libraries/floatingIslandLib.php';
require_once __ROOT__ . '/libraries/db.php';
require_once __ROOT__ . '/libraries/vendorLib.php';

// ─── Mode 1: Process upload (POST) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    header('Content-Type: application/json');

    $vendorId = $_POST['vendor_id'] ?? '';
    $uploadType = $_POST['upload_type'] ?? '';

    if (empty($vendorId)) {
        echo json_encode(['success' => false, 'error' => 'No vendor specified.']);
        exit;
    }

    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, company_name, vendor_poc_firstname, vendor_poc_lastname FROM vendors WHERE username = ?");
    $stmt->execute([$vendorId]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        echo json_encode(['success' => false, 'error' => 'Vendor not found.']);
        exit;
    }

    $result = UploadVendorDocument(
        $vendor['username'],
        $vendor['company_name'],
        $vendor['vendor_poc_firstname'],
        $vendor['vendor_poc_lastname'],
        $_FILES['uploaded_file'],
        $uploadType
    );

    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'File upload failed.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// ─── Mode 2: Render floating island (GET) ───────────────────────
$vendorId = $_GET['vendor_id'] ?? '';
if (empty($vendorId)) {
    echo SpawnFloatingIsland('<p>No vendor specified.</p>', 'Upload Document');
    exit;
}

echo LoadVendorUploadIsland($vendorId);
