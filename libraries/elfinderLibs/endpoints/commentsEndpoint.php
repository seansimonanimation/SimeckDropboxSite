<?php
/**
 * AJAX endpoint for file comments (seecm feature).
 * 
 * GET  ?action=fetch&file_url=...  — returns JSON array of comments
 * POST ?action=add&file_url=...    — body: content=...  — adds a comment
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';

header('Content-Type: application/json');

try {
    $pdo = DBConnect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'fetch') {
    $fileUrl = $_GET['file_url'] ?? '';
    if (empty($fileUrl)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing file_url parameter.']);
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT owner, comment_time, parent_file_url, comment_order, comment_content
        FROM filecomments
        WHERE parent_file_url = ?
        ORDER BY comment_order ASC, comment_time ASC
    ');
    $stmt->execute([$fileUrl]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;

} elseif ($action === 'add') {
    $fileUrl  = $_POST['file_url'] ?? '';
    $content = htmlspecialchars(trim($_POST['content'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($fileUrl) || empty(trim($content))) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing file_url or content.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT COALESCE(MAX(comment_order), 0) + 1 AS next_order FROM filecomments WHERE parent_file_url = ?');
    $stmt->execute([$fileUrl]);
    $nextOrder = (int)$stmt->fetch(PDO::FETCH_ASSOC)['next_order'];

    $stmt = $pdo->prepare('
        INSERT INTO filecomments (owner, comment_time, parent_file_url, comment_order, comment_content)
        VALUES (?, NOW(), ?, ?, ?)
    ');
    $stmt->execute([
        $_SESSION['username'] ?? 'unknown',
        $fileUrl,
        $nextOrder,
        trim($content)
    ]);

    echo json_encode(['success' => true, 'comment_id' => $pdo->lastInsertId()]);
    exit;

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use "fetch" or "add".']);
    exit;
}
