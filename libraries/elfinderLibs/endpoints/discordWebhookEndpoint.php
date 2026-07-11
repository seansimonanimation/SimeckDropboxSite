<?php
/**
 * AJAX endpoint for sending files to a Discord channel via webhook.
 *
 * POST ?action=sendToMondayChat   — sends file(s) to the Monday Chat channel
 * POST ?action=sendToThursdayChat — sends file(s) to the Thursday Chat channel
 *
 * POST params:
 *   files  — JSON-encoded array of [{ name: "file.ext", url: "/files/..." }, ...]
 *
 * The endpoint reads each file from disk, chunks them into messages
 * respecting Discord's limits (10 attachments / ~25MB per message),
 * and sends them sequentially.
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';

require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';
require_once __ROOT__ . '/libraries/elfinderLibs/SimeckVolumeDriver.php';
require_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
require_once __ROOT__ . '/libraries/elfinderLibs/volumeConfig.php';

header('Content-Type: application/json');

$rawFiles = $_POST['files'] ?? '';
$rawFolderHash = $_POST['folderHash'] ?? '';

if ($rawFiles === '') {
    error_log('[discordWebhookEndpoint] missing files param; POST keys=' . implode(',', array_keys($_POST)));
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing files parameter.',
        'debug' => 'no files payload',
        'post_keys' => array_keys($_POST)
    ]);
    exit;
}

$files = json_decode($rawFiles, true);
if (!is_array($files)) {
    error_log('[discordWebhookEndpoint] invalid files JSON: ' . json_last_error_msg() . ' raw=' . substr($rawFiles, 0, 2000));
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid files JSON.',
        'json_error' => json_last_error_msg(),
        'raw_files' => $rawFiles
    ]);
    exit;
}

// ---------- Configuration ----------
define('DISCORD_MAX_ATTACHMENTS', 10);
define('DISCORD_MAX_BYTES', 25 * 1024 * 1024); // 25 MB per message

// Map actions to webhook URLs
$webhookMap = [
    'sendToMondayChat'   => 'https://discord.com/api/webhooks/1496728418157592756/l0GV6QEbE9TMOwDLFUNlee2l_pC0FE0B3d5qCpgWvnHmDlj-yVsmgxGs01UfBdPnyxMd',
    'sendToThursdayChat' => 'https://discord.com/api/webhooks/1496728661104136237/YZsWCxb1E4xVd3-c9nFnpQ4oFJmi8ZBiKF9hQJE2VyJqvmoYHJE1z31iC8fXFA_LpASs',
];

// ---------- Auth check ----------
if (empty($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}
$senderName = $_SESSION['firstname'] ?? $_SESSION['username'];

// ---------- Validate action ----------
$action = $_POST['action'] ?? '';
$webhookUrl = $webhookMap[$action] ?? null;
if (!$webhookUrl) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use "sendToMondayChat" or "sendToThursdayChat".']);
    exit;
}
// ---------- Build file list with server paths ----------
$fileEntries = [];
$elfinderOptions = GetRoleElfinderOptions();

foreach ($files as $f) {
    $name = $f['name'] ?? 'unknown';
    $hash = $f['hash'] ?? '';
    $url  = $f['url'] ?? '';
    $serverPath = null;

    // Preferred: resolve from the elFinder hash so we always get the real file path.
    if (!empty($hash)) {
        $decodedPath = DecodeElfinderHash($hash, $elfinderOptions);

        if ($decodedPath !== null) {
            $realPath = realpath($decodedPath);
            if ($realPath !== false && file_exists($realPath) && is_readable($realPath)) {
                $serverPath = $realPath;
            }
        }
    }

    // Fallback for older payloads that still include a URL.
    if ($serverPath === null && !empty($url)) {
        $candidatePath = __ROOT__ . urldecode($url);
        $realPath = realpath($candidatePath);
        if ($realPath !== false && file_exists($realPath) && is_readable($realPath)) {
            $serverPath = $realPath;
        }
    }

    if ($serverPath === null) {
        continue;
    }

    $fileEntries[] = [
        'name' => $name,
        'path' => $serverPath,
        'size' => filesize($serverPath),
    ];
}
if (empty($fileEntries)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'No accessible files to send.',
        'debug_received_urls' => array_map(function($f) { 
            return ['url' => $f['url'] ?? '', 'serverPath' => __ROOT__ . urldecode($f['url'] ?? '')]; 
        }, $files)
    ]);
    exit;
}

// ---------- Chunk files into Discord-safe batches ----------
$batches = [];
$currentBatch = [];
$currentSize  = 0;
$channelLabel = ($action === 'sendToMondayChat') ? 'Monday Chat' : 'Thursday Chat';
$totalFiles   = count($fileEntries);

// ---------- Build folder link from provided hash ----------
$folderLink = '';
$rawFolderHash = $_POST['folderHash'] ?? '';
if (!empty($rawFolderHash)) {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
               . $_SERVER['HTTP_HOST'];
    $folderLink = "\nTo see " . ($totalFiles === 1 ? 'this file in its' : 'these files in their') . " native habitat, click [here]({$baseUrl}/viewfolder.php?folderid=" . urlencode($rawFolderHash) . ")";
}
foreach ($fileEntries as $fe) {
    // Start a new batch if the current one would exceed limits
    $wouldOverflowCount = count($currentBatch) >= DISCORD_MAX_ATTACHMENTS;
    $wouldOverflowSize  = ($currentSize + $fe['size']) > DISCORD_MAX_BYTES;

    if ($wouldOverflowCount || $wouldOverflowSize) {
        if (!empty($currentBatch)) {
            $batches[] = $currentBatch;
        }
        $currentBatch = [];
        $currentSize  = 0;
    }

    $currentBatch[] = $fe;
    $currentSize   += $fe['size'];
}
if (!empty($currentBatch)) {
    $batches[] = $currentBatch;
}

// ---------- Send each batch ----------
$batchCount = count($batches);
$sentCount  = 0;
set_time_limit(0); // prevent timeout for large uploads

foreach ($batches as $idx => $batch) {
    $partLabel = ($batchCount > 1)
        ? " (part " . ($idx + 1) . "/$batchCount)"
        : '';

    // Build the embed message
    $messageContentCount = count($batch);
    $fileList = array_map(function($fe) { return '• `' . $fe['name'] . '`'; }, $batch);
    $content = implode(", ", $fileList) . ($messageContentCount === 1 ? ' was' : ' were all') . " uploaded to the channel by {$senderName}{$partLabel}";
    $note = trim($_POST['note'] ?? '');
    if ($note !== '') {
        $content .= "\n\n> *{$note}*\n";
    }

    $content .= $folderLink;

    // Build multipart form data for the webhook
    $postFields = [
        'content' => $content,
    ];

    // Attach files using curl_file_create
    foreach ($batch as $i => $fe) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fe['path']);
        finfo_close($finfo);

        $postFields['file' . $i] = curl_file_create(
            $fe['path'],
            $mimeType ?: 'application/octet-stream',
            $fe['name']
        );
    }

    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        http_response_code(502);
        echo json_encode([
            'error' => "Discord webhook returned HTTP $httpCode for batch " . ($idx + 1),
            'response_body' => $response,
        ]);
        exit;
    }

    // Small delay between batches to avoid Discord rate limits
    if ($idx < $batchCount - 1) {
        usleep(500000); // 0.5 seconds
    }

    $sentCount += count($batch);
}

// ---------- Success ----------
echo json_encode([
    'success'    => true,
    'files_sent' => $sentCount,
    'batches'    => $batchCount,
]);
