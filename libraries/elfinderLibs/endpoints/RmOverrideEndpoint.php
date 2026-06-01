<?php
/**
 * AJAX endpoint for custom rm (delete) override.
 * Handles deletion of files/folders from disk AND cleanup of filecomments and lockedfiles tables.
 * 
 * POST: paths[] — array of root-relative paths (e.g., /files/Projects/...)
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';

header('Content-Type: application/json');

// Only allow admin and artist roles to delete
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'artist') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied. Only admin and artist roles can delete files.']);
    exit;
}

try {
    $pdo = DBConnect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$paths = $_POST['paths'] ?? [];
if (empty($paths) || !is_array($paths)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No paths provided.']);
    exit;
}

$errors = [];
$deleted = [];

foreach ($paths as $relativePath) {
    // Decode URL-encoded characters (e.g., %20 -> space, %2C -> comma)
    $relativePath = urldecode($relativePath);
    
    // Sanitize: prevent directory traversal
    $relativePath = str_replace('..', '', $relativePath);
    
    // Only allow deletions under /files/
    if (strpos($relativePath, '/files/') !== 0) {
        $errors[] = "Path not allowed: $relativePath (must start with /files/)";
        continue;
    }
    
    $absolutePath = __ROOT__ . $relativePath;
    
    // Double-check the resolved path is still under __ROOT__/files/
    $realRoot = realpath(__ROOT__ . '/files');
    $realPath = realpath($absolutePath);
    if ($realPath === false || strpos($realPath, $realRoot) !== 0) {
        $errors[] = "Invalid path: $relativePath";
        continue;
    }
    
    if (is_dir($absolutePath)) {
        // ── FOLDER: Recursive delete ──
        
        // 1. Delete from filecomments (folder and all sub-paths)
        $stmt = $pdo->prepare('DELETE FROM filecomments WHERE parent_file_url LIKE ?');
        $stmt->execute([$relativePath . '/%']);
        $commentCount = $stmt->rowCount();
        
        // Also delete comments on the folder itself
        $stmt = $pdo->prepare('DELETE FROM filecomments WHERE parent_file_url = ?');
        $stmt->execute([$relativePath]);
        $commentCount += $stmt->rowCount();
        
        // 2. Delete from lockedfiles (folder and all sub-paths)
        $stmt = $pdo->prepare('DELETE FROM lockedfiles WHERE filepath LIKE ?');
        $stmt->execute([$relativePath . '/%']);
        $lockCount = $stmt->rowCount();
        
        // 3. Recursively delete folder from disk
        $diskDeleted = deleteDirectoryRecursive($absolutePath);
        
        if ($diskDeleted) {
            $deleted[] = [
                'path' => $relativePath,
                'type' => 'folder',
                'comments_removed' => $commentCount,
                'locks_removed' => $lockCount
            ];
        } else {
            $errors[] = "Failed to delete folder from disk: $relativePath";
        }
        
    } elseif (is_file($absolutePath)) {
        // ── FILE: Single file delete ──
        
        // 1. Delete from filecomments
        $stmt = $pdo->prepare('DELETE FROM filecomments WHERE parent_file_url = ?');
        $stmt->execute([$relativePath]);
        $commentCount = $stmt->rowCount();
        
        // 2. Delete from lockedfiles
        $stmt = $pdo->prepare('DELETE FROM lockedfiles WHERE filepath = ?');
        $stmt->execute([$relativePath]);
        $lockCount = $stmt->rowCount();
        
        // 3. Delete file from disk
        $diskDeleted = @unlink($absolutePath);
        
        if ($diskDeleted) {
            $deleted[] = [
                'path' => $relativePath,
                'type' => 'file',
                'comments_removed' => $commentCount,
                'locks_removed' => $lockCount
            ];
        } else {
            $errors[] = "Failed to delete file from disk: $relativePath";
        }
        
    } else {
        $errors[] = "Path does not exist: $relativePath";
    }
}

echo json_encode([
    'success' => empty($errors),
    'deleted' => $deleted,
    'errors' => $errors
]);
exit;

/**
 * Recursively delete a directory and all its contents.
 * @param string $dir Absolute path to directory
 * @return bool True on success, false on failure
 */
function deleteDirectoryRecursive($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $fileInfo) {
        $filePath = $fileInfo->getPathname();
        if ($fileInfo->isDir()) {
            if (!@rmdir($filePath)) {
                return false;
            }
        } else {
            if (!@unlink($filePath)) {
                return false;
            }
        }
    }
    
    return @rmdir($dir);
}
