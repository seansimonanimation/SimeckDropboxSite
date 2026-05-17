<?php
// AJAX endpoint: /libraries/archive_project.php
// Called via POST with: pid=<number>&action=archive|unarchive
//
// Sets transitioning=1, spawns the background worker, returns immediately.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/projectlib.php';

header('Content-Type: application/json');

$pid = $_POST['pid'] ?? $_GET['pid'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$pid || !in_array($action, ['archive', 'unarchive'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid parameters']);
    exit;
}

// 1. Flip active status and set transitioning = 1
ToggleProjectActivation($pid);

// 2. Spawn the background worker
// Determine the php executable path and script path
$workerScript = __DIR__ . '/background_project_toggle.php';

if (PHP_OS_FAMILY === 'Windows') {
    // PHP_BINARY is php-cgi.exe in Laragon — we need php.exe
    $phpBin = dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'php.exe';
    pclose(popen("start /B \"\" \"{$phpBin}\" \"{$workerScript}\" {$pid} {$action}", "r"));

} else {
    // Linux (Docker): PHP_BINARY in php-fpm points to php-fpm, not the CLI binary
    // Try to locate the PHP CLI binary
    $phpBin = PHP_BINARY;
    if (strpos($phpBin, 'php-fpm') !== false) {
        // php-fpm can't run CLI scripts — look for the CLI binary
        $cliBin = trim(exec('which php 2>/dev/null'));
        $phpBin = $cliBin ?: '/usr/local/bin/php';
    }
    exec("nohup \"{$phpBin}\" \"{$workerScript}\" {$pid} {$action} > /dev/null 2>&1 &");
}



// 3. Return success immediately
echo json_encode(['status' => 'started']);
