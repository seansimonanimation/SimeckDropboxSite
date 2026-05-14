<?php
// This script is invoked as a background CLI process.
// Usage: php background_project_toggle.php <pid> <archive|unarchive>
//
// It expects __ROOT__ to be defined, or we hardcode the path to libraries.

// Bootstrap — we need DB access
// If __ROOT__ isn't defined yet (CLI context), set it:
if (!defined('__ROOT__')) {
    define('__ROOT__', dirname(__DIR__));
}

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';


$pid = $argv[1] ?? null;
$action = $argv[2] ?? null;

if (!$pid || !in_array($action, ['archive', 'unarchive'])) {
    exit("Usage: php background_project_toggle.php <pid> <archive|unarchive>\n");
}

$pdo = DBConnect();

// Fetch project info
$stmt = $pdo->prepare("SELECT * FROM projects WHERE pid = ?");
$stmt->execute([$pid]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    exit("Project not found.\n");
}

$siteRoot = __ROOT__; // root of the site, where index.php lives
$activePath = $siteRoot . '/' . $project['active_path'];
$inactiveZipPath = $siteRoot . '/' . $project['inactive_zip_path'];

if ($action === 'archive') {
    // 1. Zip the active folder
    $zip = new ZipArchive();
    if ($zip->open($inactiveZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Add the entire directory recursively
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($activePath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $relativePath = substr($file->getRealPath(), strlen(realpath($activePath)) + 1);
                $zip->addFile($file->getRealPath(), $relativePath);
            }
        }
        $zip->close();
    } else {
        // Couldn't create zip — set transitioning back to 0 and exit
        $pdo->prepare("UPDATE projects SET transitioning = 0 WHERE pid = ?")->execute([$pid]);
        exit("Failed to create zip archive.\n");
    }

    // 2. Delete the original project folder
    // Use a recursive directory delete function
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
deleteDirectory($activePath);


} elseif ($action === 'unarchive') {
    // 1. Extract the zip to the active path
    $zip = new ZipArchive();
    if ($zip->open($inactiveZipPath) === TRUE) {
        $zip->extractTo($activePath);
        $zip->close();
    } else {
        $pdo->prepare("UPDATE projects SET transitioning = 0 WHERE pid = ?")->execute([$pid]);
        exit("Failed to extract zip archive.\n");
    }

    // 2. Delete the zip file
    unlink($inactiveZipPath);
}

// 3. Set transitioning back to 0
$pdo->prepare("UPDATE projects SET transitioning = 0 WHERE pid = ?")->execute([$pid]);
