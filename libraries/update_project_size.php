<?php
/**
 * AJAX endpoint for scanning a project folder size.
 * Called asynchronously from the admin project management page.
 * 
 * GET /libraries/update_project_size.php?pid=C01
 * 
 * Returns JSON: { "pid": "C01", "size_bytes": 123456, "size_mb": "0.12" }
 */
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/projectlib.php';

header('Content-Type: application/json');

if (!isset($_GET['pid'])) {
    echo json_encode(['error' => 'Missing pid parameter']);
    exit;
}

$pid = $_GET['pid'];
$safePid = preg_replace('/[^a-zA-Z0-9_-]/', '', $pid);
$lockFile = sys_get_temp_dir() . '/simeck_size_scan_' . $safePid . '.lock';

// Ensure the PHP process continues even if the user navigates away
ignore_user_abort(true);
set_time_limit(150); // 2.5 minutes max per scan

$lockFp = @fopen($lockFile, 'c');

try {
    if ($lockFp && flock($lockFp, LOCK_EX | LOCK_NB)) {
        // We got the lock — we're the scanning process
        try {
            $size = GetProjectFolderSize($pid);
            
            // Write to DB (even if 0, so we stop showing "Calculating...")
            $pdo = DBConnect();
            $stmt = $pdo->prepare("UPDATE projects SET size_on_disk = ? WHERE pid = ?");
            $stmt->execute([$size, $pid]);
        } finally {
            // Always release the lock, even if GetProjectFolderSize() throws
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            @unlink($lockFile);
        }

        echo json_encode([
            'pid' => $pid,
            'size_bytes' => $size,
            'size_mb' => round($size / 1048576, 2)
        ]);
        exit;
    }

    // Lock exists — another process is scanning. Wait for it.
    if ($lockFp) {
        // Try to get the lock in blocking mode — this waits until the scanner finishes
        if (flock($lockFp, LOCK_EX)) {
            // Lock acquired — the other process finished and released it
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            // The size should now be in the DB
            $pdo = DBConnect();
            $stmt = $pdo->prepare("SELECT size_on_disk FROM projects WHERE pid = ?");
            $stmt->execute([$pid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $size = $row ? (int)$row['size_on_disk'] : 0;

            echo json_encode([
                'pid' => $pid,
                'size_bytes' => $size,
                'size_mb' => round($size / 1048576, 2)
            ]);
            exit;
        }
        fclose($lockFp);
    }
} catch (Throwable $e) {
    // Catch any exception and release the lock before re-throwing the error
    if ($lockFp) {
        @flock($lockFp, LOCK_UN);
        @fclose($lockFp);
        @unlink($lockFile);
    }
    
    // Return a useful error instead of a blank response
    http_response_code(500);
    echo json_encode([
        'error' => 'Size scan failed: ' . $e->getMessage(),
        'pid' => $pid
    ]);
    exit;
}

// Fallback: just read whatever is in DB
$pdo = DBConnect();
$stmt = $pdo->prepare("SELECT size_on_disk FROM projects WHERE pid = ?");
$stmt->execute([$pid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$size = $row ? (int)$row['size_on_disk'] : 0;

echo json_encode([
    'pid' => $pid,
    'size_bytes' => $size,
    'size_mb' => round($size / 1048576, 2)
]);
