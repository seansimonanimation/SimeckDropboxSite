<?php
include_once __DIR__ . '/../../../libraries/session.php';
include_once __DIR__ . '/../../../libraries/db.php';
include_once __DIR__ . '/../../../libraries/projectlib.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update_lead') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$pid = trim($_POST['pid'] ?? '');
$lead = trim($_POST['lead'] ?? '');

if ($pid === '') {
    echo json_encode(['success' => false, 'error' => 'Missing pid']);
    exit;
}

UpdateProjectLead($pid, $lead ?: null);
echo json_encode(['success' => true]);
exit;
