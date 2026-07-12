<?php
/**
 * previewPsd.php
 *
 * PSD preview endpoint.
 * Uses Node.js + psd npm package to convert PSD to PNG.
 *
 * Accepts: ?hash=<elFinder hash>
 */

if (!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}

require_once __ROOT__ . '/libraries/session.php';
require_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

// ─── Auth check ───────────────────────────────────────────────
if (empty($_SESSION['username'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// ─── Parameter parsing ────────────────────────────────────────
$hash = $_GET['hash'] ?? '';
if (!$hash) {
    http_response_code(400);
    echo 'Missing hash parameter';
    exit;
}

$elfinderOptions = GetRoleElfinderOptions();
$filePath = DecodeElfinderHash($hash, $elfinderOptions);
if ($filePath === null) {
    http_response_code(400);
    echo 'Invalid file hash.';
    exit;
}

// ─── Extension validation ─────────────────────────────────────
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if ($ext !== 'psd') {
    http_response_code(400);
    echo 'Unsupported file type';
    exit;
}

// ─── Permission check ─────────────────────────────────────────
$realPath = realpath($filePath);
if ($realPath === false) {
    http_response_code(500);
    echo 'Unable to resolve file path.';
    exit;
}

$root = str_replace('\\', '/', __ROOT__);
$realNormalized = str_replace('\\', '/', $realPath);

$allowedRoots = [
    $root . '/files/Dropboxes',
    $root . '/files/Projects',
    $root . '/files/Resources',
    $root . '/files/Corporate',
];

$dropboxPath = AttachOrCreateDropbox();
$dropboxNormalized = str_replace('\\', '/', $dropboxPath);
$allowedRoots[] = $dropboxNormalized;

$hasAccess = false;
foreach ($allowedRoots as $volumeRoot) {
    if (strpos($realNormalized, $volumeRoot) === 0) {
        $hasAccess = true;
        break;
    }
}

if (!$hasAccess) {
    http_response_code(403);
    echo 'You do not have permission to access this file.';
    exit;
}

// ─── Locate Node.js ───────────────────────────────────────────
$nodePaths = [
    'C:\\Program Files\\nodejs\\node.exe',  // Windows
    '/usr/bin/node',                          // Docker Linux
    '/usr/local/bin/node',                    // Linux alternative
];

$nodeExe = null;
foreach ($nodePaths as $testPath) {
    if (file_exists($testPath)) {
        $nodeExe = $testPath;
        break;
    }
}

if ($nodeExe === null) {
    $whichOutput = trim(shell_exec('which node 2>/dev/null || where node 2>/dev/null'));
    if ($whichOutput && file_exists($whichOutput)) {
        $nodeExe = $whichOutput;
    }
}

if ($nodeExe === null) {
    http_response_code(500);
    echo 'Node.js not found on server.';
    exit;
}

// ─── Set NODE_PATH so require('psd') resolves ────────────────
$nodeModulesPath = __ROOT__ . '/libraries/node/node_modules';
if (!is_dir($nodeModulesPath . '/psd')) {
    http_response_code(500);
    echo 'PSD node module not installed. Run: cd libraries/node && npm init -y && npm install psd';
    exit;
}

// ─── Use wrapper script ───────────────────────────────────────
$wrapperScript = __ROOT__ . '/libraries/node/psd2png.js';

if (!file_exists($wrapperScript)) {
    http_response_code(500);
    echo 'PSD wrapper script not found.';
    exit;
}

$escapedInput = escapeshellarg($realPath);
$escapedWrapper = escapeshellarg($wrapperScript);

// Build the command with proper quoting
$command = '"' . $nodeExe . '" ' . $escapedWrapper . ' ' . $escapedInput . ' 2>&1';

// If ?debug=1, show the command instead of running it
if (!isset($_GET['debug'])) {
    header('Content-Type: text/plain');
    echo "Command: " . $command . "\n\n";
    echo "File exists: " . (file_exists($realPath) ? 'yes' : 'no') . "\n";
    echo "Script exists: " . (file_exists($wrapperScript) ? 'yes' : 'no') . "\n";
    echo "Node exists: " . (file_exists($nodeExe) ? 'yes' : 'no') . "\n";
    echo "Node modules psd: " . (is_dir($nodeModulesPath . '/psd') ? 'yes' : 'no') . "\n";
    echo "shell_exec available: " . (function_exists('shell_exec') ? 'yes' : 'no') . "\n";
    echo "disabled functions: " . ini_get('disable_functions') . "\n\n";
    echo "--- Running command ---\n";
    $output = shell_exec($command);
    echo "Output length: " . strlen($output) . "\n";
    echo "PNG header check: ";
    if (strlen($output) >= 8 && substr($output, 0, 8) === "\x89PNG\r\n\x1a\n") {
        echo "PASS\n";
    } else {
        echo "FAIL - first 20 bytes: " . bin2hex(substr($output, 0, 20)) . "\n";
    }
    echo "Raw output:\n" . $output;
    exit;
}

$pngData = shell_exec($command);


// Check if the output looks like valid PNG data
$pngHeader = substr($pngData, 0, 8);
if ($pngHeader !== "\x89PNG\r\n\x1a\n") {
    http_response_code(500);
    echo 'PSD conversion produced invalid output.';
    exit;
}

// ─── Serve the PNG ────────────────────────────────────────────
$fileName = basename($realPath, '.psd') . '.png';

header('Content-Type: image/png');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pngData));
header('Cache-Control: public, max-age=3600');

echo $pngData;
exit;
