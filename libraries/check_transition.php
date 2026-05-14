<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pid = $_GET['pid'] ?? null;
if (!$pid) { echo json_encode(['transitioning' => 0]); exit; }

$pdo = DBConnect();
$stmt = $pdo->prepare("SELECT transitioning FROM projects WHERE pid = ?");
$stmt->execute([$pid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['transitioning' => $row['transitioning'] ?? 0]);
?>