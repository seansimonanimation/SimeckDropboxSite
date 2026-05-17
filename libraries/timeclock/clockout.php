<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';

if (isset($_GET['shift_id'])) {
    CloseTimeclockShift((int)$_GET['shift_id']);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing shift_id']);
}