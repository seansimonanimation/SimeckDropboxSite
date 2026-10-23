<?php
include_once __DIR__ . '/../../../libraries/session.php';
include_once __DIR__ . '/../../../libraries/db.php';
include_once __DIR__ . '/../../../libraries/projectlib.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$action = $_POST['action'];
$pid = trim($_POST['pid'] ?? '');

if ($pid === '') {
    echo json_encode(['success' => false, 'error' => 'Missing pid']);
    exit;
}

if ($action === 'update_lead') {
    $lead = trim($_POST['lead'] ?? '');
    UpdateProjectLead($pid, $lead ?: null);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update_security_level') {
    $level = trim($_POST['level'] ?? '');
    $result = UpdateProjectSecurityLevel($pid, $level);
    echo json_encode(['success' => (bool)$result]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
exit;
