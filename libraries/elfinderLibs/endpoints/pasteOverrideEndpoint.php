<?php
/**
 * pasteOverrideEndpoint.php
 * 
 * AJAX endpoint called after a paste (move) operation in elFinder.
 * Updates filecomments and lockedfiles database tables to reflect
 * the new file/directory paths so comments and locks travel with them.
 * 
 * POST: mappings[] — array of {oldPath, newPath} objects
 * 
 * For directories, uses SQL REPLACE() to recursively update all sub-paths.
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';

header('Content-Type: application/json');

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'artist' && $role !== 'client') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied.']);
    exit;
}

try {
    $pdo = DBConnect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$mappings = $_POST['mappings'] ?? [];
if (empty($mappings) || !is_array($mappings)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No mappings provided.']);
    exit;
}

$updated = [
    'filecomments' => 0,
    'lockedfiles'  => 0
];
$errors = [];

foreach ($mappings as $map) {
    $oldPath = $map['oldPath'] ?? '';
    $newPath = $map['newPath'] ?? '';
    
    if (empty($oldPath) || empty($newPath)) {
        $errors[] = 'Invalid mapping entry';
        continue;
    }
    
    // Sanitize: prevent directory traversal
    $oldPath = str_replace('..', '', $oldPath);
    $newPath = str_replace('..', '', $newPath);
    
    // Only process paths under /files/
    if (strpos($oldPath, '/files/') !== 0 || strpos($newPath, '/files/') !== 0) {
        $errors[] = "Path not allowed: $oldPath → $newPath";
        continue;
    }
    
    // ── Update filecomments ──
    // Exact match: parent_file_url = oldPath
    $stmt = $pdo->prepare('UPDATE filecomments SET parent_file_url = ? WHERE parent_file_url = ?');
    $stmt->execute([$newPath, $oldPath]);
    $updated['filecomments'] += $stmt->rowCount();
    
    // Sub-path match: parent_file_url LIKE 'oldPath/%'
    $stmt = $pdo->prepare('UPDATE filecomments SET parent_file_url = REPLACE(parent_file_url, ?, ?) WHERE parent_file_url LIKE ?');
    $stmt->execute([$oldPath . '/', $newPath . '/', $oldPath . '/%']);
    $updated['filecomments'] += $stmt->rowCount();
    
    // ── Update lockedfiles ──
    // Exact match: filepath = oldPath
    $stmt = $pdo->prepare('UPDATE lockedfiles SET filepath = ? WHERE filepath = ?');
    $stmt->execute([$newPath, $oldPath]);
    $updated['lockedfiles'] += $stmt->rowCount();
    
    // Sub-path match: filepath LIKE 'oldPath/%'
    $stmt = $pdo->prepare('UPDATE lockedfiles SET filepath = REPLACE(filepath, ?, ?) WHERE filepath LIKE ?');
    $stmt->execute([$oldPath . '/', $newPath . '/', $oldPath . '/%']);
    $updated['lockedfiles'] += $stmt->rowCount();
}

echo json_encode([
    'success' => true,
    'updated' => $updated,
    'errors'  => $errors
]);
