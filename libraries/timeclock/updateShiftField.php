<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';

// Admin-only check
if(GetRole() !== 'admin'){
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$shiftId = $_POST['shift_id'] ?? null;
$field = $_POST['field'] ?? null;
$value = $_POST['value'] ?? null;

if(!$shiftId || !$field || $value === null){
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$result = UpdateTimeclockShiftField((int)$shiftId, $field, $value);

if($result){
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Update failed']);
}
