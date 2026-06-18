<?php
/**
 * notifyClientEndpoint.php
 * 
 * Dual-purpose:
 *   GET / POST without 'message' → Returns floating island HTML with thumbnail preview + form
 *   POST with 'filepath' + 'message' → Sends SMS via Twilio, returns JSON
 */
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __DIR__ . '/../../session.php';
require_once __ROOT__ . '/libraries/floatingIslandLib.php';
require_once __ROOT__ . '/libraries/db.php';
require_once __ROOT__ . '/libraries/logging.php';
require_once __ROOT__ . '/download.php';
require_once __ROOT__ . '/vendor/autoload.php';

// Load dbconfig for Twilio constants (same pattern as download.php)
if (file_exists('/var/www/dbconfig.php')) {
    include_once '/var/www/dbconfig.php';
} elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
    include_once __ROOT__ . '/dbconfig.php';
}

use Twilio\Rest\Client;

// ─── Mode 1: Twilio send (POST with both filepath + message) ──────────────
$sendMessage = trim($_POST['message'] ?? '');
$sendFilepath = trim($_POST['filepath'] ?? '');

if (!empty($sendFilepath) && !empty($sendMessage)) {
    header('Content-Type: application/json');

    // Auth check
    if (empty($_SESSION['username']) || $_SESSION['tempRole'] !== 'artist') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated as artist.']);
        exit;
    }

    $senderName = $_SESSION['firstname'] ?? $_SESSION['username'];

    // Resolve absolute path
    $absPath = __ROOT__ . $sendFilepath;
    if (!file_exists($absPath)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'File not found on server.']);
        exit;
    }

    // Extract project folder from path
    if (!preg_match('#clientProjects/([^/]+)#', $sendFilepath, $matches)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File is not inside a client project.']);
        exit;
    }
    $projectFolder = $matches[1];

    // Look up project leader (client username)
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT leader, project_name FROM projects WHERE active_path LIKE ?");
    $stmt->execute(['%/clientProjects/' . $projectFolder]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project || empty($project['leader'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No client assigned to this project.']);
        exit;
    }

    $clientUsername = $project['leader'];
    $projectName    = $project['project_name'];

    // Look up client's phone number
    $stmt = $pdo->prepare("SELECT phone_country_code, phone_number, receive_texts FROM clients WHERE username = ? AND active = 1");
    $stmt->execute([$clientUsername]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Client not found.']);
        exit;
    }

    if (empty($client['phone_number']) || (int)$client['receive_texts'] !== 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Client has no phone number or has opted out of texts.'
        ]);
        exit;
    }

    // Format phone number
    $countryCode = $client['phone_country_code'] ?? '1';
    $phoneNumber = $client['phone_number'];
    $to = '+' . $countryCode . $phoneNumber;

    // Generate signed download link (thumbnail mode)
    $downloadToken = GenerateElfinderDownloadToken($absPath);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $fileLink = $baseUrl . '/download.php?download=' . urlencode($downloadToken) . '&thumb=1';

    // Build SMS body
    $fileName = basename($absPath);
    $smsBody = "New file from {$senderName}: {$sendMessage} - {$fileLink}";

    try {
        $twilio = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
        $twilioMessage = $twilio->messages->create($to, [
            'from' => TWILIO_FROM_NUMBER,
            'body' => $smsBody,
        ]);
    } catch (Exception $e) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Failed to send SMS: ' . $e->getMessage()]);
        exit;
    }

    // Log the action
    LogSimeckAction(
        'Client notification sent',
        "{$senderName} sent notification about '{$fileName}' to client '{$clientUsername}' (project: {$projectName})",
        'System'
    );

    echo json_encode(['success' => true, 'message_sid' => $twilioMessage->sid]);
    exit;
}

// ─── Mode 2: Render floating island (no message sent yet) ──────────────
$filepath = $_POST['filepath'] ?? $_GET['filepath'] ?? '';
if (empty($filepath)) {
    echo SpawnFloatingIsland('<p>No file selected.</p>', 'Notify Client');
    exit;
}

$absPath = __ROOT__ . $filepath;
if (!file_exists($absPath)) {
    echo SpawnFloatingIsland('<p>File not found.</p>', 'Notify Client');
    exit;
}

// Generate thumbnail preview token
$thumbToken = GenerateElfinderDownloadToken($absPath);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
$thumbUrl = $baseUrl . '/download.php?download=' . urlencode($thumbToken) . '&thumb=1';

$fileName = htmlspecialchars(basename($absPath), ENT_QUOTES, 'UTF-8');
$safeFilepath = htmlspecialchars($filepath, ENT_QUOTES, 'UTF-8');

$uid = 'fi-notify-' . md5(uniqid('', true));

$bodyHtml = <<<HTML
<div style="text-align:center;margin-bottom:14px;">
    <img src="{$thumbUrl}" alt="{$fileName}" 
         style="max-width:100%;max-height:200px;border-radius:var(--radius-sm);border:1px solid var(--color-border-bright);"
         onerror="this.style.display='none'">
</div>
<p style="margin:0 0 4px;font-weight:600;color:var(--color-heading);">File: {$fileName}</p>

<label style="display:block;margin:12px 0 4px;font-weight:600;color:var(--color-heading);">
    Message to Client:
</label>
<textarea id="{$uid}-message"
    style="width:100%;height:80px;box-sizing:border-box;padding:10px 12px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;resize:vertical;"
    placeholder="Type a message for the client…"></textarea>

<input type="hidden" id="{$uid}-filepath" value="{$safeFilepath}">

<button id="{$uid}-send" style="margin-top:12px;">Send Notification</button>
<div id="{$uid}-status" style="margin-top:10px;"></div>
HTML;

// The JS posts back to THIS same file
$js = <<<JS
<script>
(function() {
    var btn = document.getElementById('{$uid}-send');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var message = document.getElementById('{$uid}-message').value.trim();
        if (!message) { alert('Please enter a message.'); return; }

        var filepath = document.getElementById('{$uid}-filepath').value;
        var statusDiv = document.getElementById('{$uid}-status');
        statusDiv.innerHTML = '<p style="color:var(--color-text-muted);">Sending…</p>';
        btn.disabled = true;

        fetch('libraries/elfinderLibs/endpoints/notifyClientEndpoint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ filepath: filepath, message: message })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                statusDiv.innerHTML = '<p style="color:var(--color-success);">✅ Notification sent to client!</p>';
                setTimeout(function() {
                    var island = btn.closest('.floating-island');
                    if (island) island.remove();
                }, 2000);
            } else {
                statusDiv.innerHTML = '<p style="color:var(--color-danger);">Error: ' + (data.error || 'unknown') + '</p>';
            }
        })
        .catch(function(err) {
            statusDiv.innerHTML = '<p style="color:var(--color-danger);">Request failed: ' + err.message + '</p>';
        })
        .finally(function() { btn.disabled = false; });
    });
})();
</script>
JS;

echo SpawnFloatingIsland($bodyHtml . $js, 'Notify Client');
