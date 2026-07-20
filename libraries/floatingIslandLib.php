<?php
/**
 * floatingIslandLib.php
 * 
 * A library that allows us to call in a floating window and populate it easily.
 * All functions return HTML strings ready to echo into any module page.
 */

// ─── Guard to ensure includes only happen once per request ───────────────
if (!defined('__FLOATING_ISLAND_LIB_LOADED__')) {
    define('__FLOATING_ISLAND_LIB_LOADED__', true);

    include_once __DIR__ . '/session.php';
    include_once __ROOT__ . '/libraries/db.php';
    include_once __ROOT__ . '/libraries/projectlib.php';
}


// ──────────────────────────────────────────────────────────────────────────
//  1.  SPAWN FLOATING ISLAND  (the wrapper)
// ──────────────────────────────────────────────────────────────────────────
/**
 * Generate the outer overlay + card shell for a floating island.
 * Includes built-in drag-by-header behavior.
 *
 * @param  string  $contents   The pre-rendered HTML body content.
 * @param  string  $title      The title shown in the island header bar.
 * @return string              The complete floating-island HTML.
 */
function SpawnFloatingIsland($contents, $title = 'Floating Island')
{
    $sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', $title));

    $html = <<<HTML
<div class="floating-island" id="fi-{$sanitizedId}" role="dialog" aria-labelledby="fi-title-{$sanitizedId}">
    <div class="floating-island__header">
        <h3 class="floating-island__title" id="fi-title-{$sanitizedId}">
            {$title}
        </h3>
        <button class="floating-island__close"
                onclick="this.closest('.floating-island').remove()"
                aria-label="Close">
            ✕
        </button>
    </div>
    <div class="floating-island__body">
        {$contents}
    </div>
    <div class="floating-island__resize-handle"></div>
</div>
HTML;

    return $html;
}

// ──────────────────────────────────────────────────────────────────────────
//  3.  LOAD SEND-TO-DISCORD ISLAND  (form + AJAX)
// ──────────────────────────────────────────────────────────────────────────
/**
 * Build a floating island that lets the user send files to a Discord channel
 * with an optional note.
 *
 * @param  array  $files   Array of ['name' => '…', 'url' => '…', …]
 * @return string          HTML snippet.
 */
function LoadSendToDiscordIsland($files, $folderHash = '')
{
    if (empty($files) || !is_array($files)) {
        return SpawnFloatingIsland(
            '<p class="seeComments-status-empty">No files selected to send.</p>',
            'Send to Discord'
        );
    }

    // Build the file list summary
    $fileCount = count($files);
    $fileListHtml = '';
    foreach ($files as $f) {
        $fileName = htmlspecialchars($f['name'] ?? 'unknown', ENT_QUOTES, 'UTF-8');
        $fileListHtml .= '<li>• ' . $fileName . '</li>';
    }

    $filesJson = json_encode(
        $files,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    $filesJsonString = json_encode($filesJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // We need a unique ID per instance so multiple islands can coexist
    $uid = 'fi-discord-' . md5(uniqid('', true));

    $bodyHtml = <<<ISLANDBODYHTML
<p><strong>{$fileCount} file(s) selected:</strong></p>
<ul style="margin: 8px 0 16px 20px; padding: 0;">{$fileListHtml}</ul>
<label style="display:block;margin-bottom:4px;font-weight:600;color:var(--color-heading);">
    Note (optional):
</label>
<textarea id="{$uid}-note"
    style="width:100%;height:72px;box-sizing:border-box;padding:10px 12px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;resize:vertical;"
    placeholder="Add a message…"></textarea>

<div style="margin:14px 0 10px;">
    <label style="display:block;margin-bottom:4px;font-weight:600;color:var(--color-heading);">Channel:</label>
    <label style="display:inline-flex;align-items:center;gap:6px;margin-right:20px;cursor:pointer;">
        <input type="radio" name="{$uid}-channel" value="sendToMondayChat" checked>
        Monday Chat
    </label>
    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
        <input type="radio" name="{$uid}-channel" value="sendToThursdayChat">
        Thursday Chat
    </label>
</div>
<input type="hidden" id="{$uid}-folderHash" value="{$folderHash}" />

<button id="{$uid}-send" style="margin-top:4px;">Send to Discord</button>
<div id="{$uid}-status" style="margin-top:10px;"></div>
ISLANDBODYHTML;

    $js = <<<JS
<script>
(function() {
    var btn = document.getElementById('{$uid}-send');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var channel = document.querySelector('input[name="{$uid}-channel"]:checked');
        if (!channel) { alert('Please select a channel.'); return; }

        var note = document.getElementById('{$uid}-note').value.trim();
        var statusDiv = document.getElementById('{$uid}-status');
        statusDiv.innerHTML = '<p style="color:var(--color-text-muted);">Sending…</p>';
        btn.disabled = true;

        var discordFiles = JSON.parse({$filesJsonString});

        var formData = new FormData();
        formData.append('action', channel.value);
        formData.append('files', JSON.stringify(discordFiles));
        if (note) {
            formData.append('note', note);
        }
        var fh = document.getElementById('{$uid}-folderHash');
        if (fh && fh.value) formData.append('folderHash', fh.value);
        fetch('libraries/elfinderLibs/endpoints/discordWebhookEndpoint.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                statusDiv.innerHTML = '<p style="color:var(--color-success);">✅ Sent! (' + data.files_sent + ' file(s) in ' + data.batches + ' batch(es))</p>';
            } else {
                statusDiv.innerHTML = '<p style="color:var(--color-danger);">Error: ' + (data.error || 'unknown') + '</p>';
            }
        })
        .catch(function(err) {
            statusDiv.innerHTML = '<p style="color:var(--color-danger);">Request failed: ' + err.message + '</p>';
        })
        .finally(function() {
            btn.disabled = false;
        });
    });
})();

</script>
JS;

    return SpawnFloatingIsland($bodyHtml . $js, 'Send to Discord');
}


// ──────────────────────────────────────────────────────────────────────────
//  4.  LOAD MOVE FILES TO PROJECT ISLAND  (stub)
// ──────────────────────────────────────────────────────────────────────────
/**
 * Build a floating island that lets the user move selected files to one
 * of their assigned projects.
 *
 * @param  array  $files   Array of ['name' => '…', 'url' => '…', …]
 * @return string          HTML snippet (stub for now).
 */
function LoadMoveFilesToProjectIsland($files)
{
    if (empty($files) || !is_array($files)) {
        return SpawnFloatingIsland(
            '<p class="seeComments-status-empty">No files selected to move.</p>',
            'Move Files to Project'
        );
    }

    // Build file summary
    $fileCount = count($files);
    $fileListHtml = '';
    foreach ($files as $f) {
        $fileName = htmlspecialchars($f['name'] ?? 'unknown', ENT_QUOTES, 'UTF-8');
        $fileListHtml .= '<li>• ' . $fileName . '</li>';
    }

    // Build project dropdown (reuse existing projectlib function)
    ob_start();
    echo '<select id="fi-move-project-select" style="width:100%;padding:8px 10px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;">';
    echo '<option value="">— Select a project —</option>';
    GetAssignedArtistProjectOptionListHTML();
    echo '</select>';
    $projectDropdown = ob_get_clean();

    $bodyHtml = <<<BODY
<p><strong>{$fileCount} file(s) to move:</strong></p>
<ul style="margin: 8px 0 16px 20px; padding: 0;">{$fileListHtml}</ul>

<label style="display:block;margin-bottom:4px;font-weight:600;color:var(--color-heading);">
    Destination Project:
</label>
{$projectDropdown}

<p style="margin-top:10px;color:var(--color-text-dim);font-size:0.82rem;">
    Files will be placed in <code>{projectFolder}/newAssets/</code> and any
    associated comments will be updated to reflect the new location.
</p>

<button id="fi-move-submit" style="margin-top:12px;" disabled title="Not yet implemented">Move Files</button>
<p style="margin-top:6px;color:var(--color-text-muted);font-style:italic;">🚧 This feature is not yet implemented.</p>
BODY;

    $js = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    var moveBtn = document.getElementById('fi-move-submit');
    if (moveBtn) {
        moveBtn.addEventListener('click', function() {
            alert('Move Files to Project is not yet implemented.');
        });
        // Enable the button just so the user can click it for the alert
        moveBtn.disabled = false;
    }
});
</script>
JS;

    return SpawnFloatingIsland($bodyHtml . $js, 'Move Files to Project');
}
