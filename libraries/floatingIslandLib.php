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

    // Inline drag logic
    $dragJs = <<<JS
<script>
(function() {
    var island = document.getElementById('fi-{$sanitizedId}');
    if (!island) return;

    var header = island.querySelector('.floating-island__header');
    if (!header) return;

    var offsetX = 0, offsetY = 0, dragging = false;

    header.addEventListener('mousedown', function(e) {
        if (e.target.closest('.floating-island__close')) return;

        dragging = true;
        var rect = island.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        island.style.cursor = 'grabbing';
        island.style.transition = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        island.style.left = (e.clientX - offsetX) + 'px';
        island.style.top  = (e.clientY - offsetY) + 'px';
        island.style.transform = 'none';
    });

    document.addEventListener('mouseup', function() {
        if (!dragging) return;
        dragging = false;
        island.style.cursor = '';
        island.style.transition = '';
    });
})();
</script>
JS;

    // Inline resize logic
    $resizeJs = <<<JS
<script>
(function() {
    var island = document.getElementById('fi-{$sanitizedId}');
    if (!island) return;

    var handle = island.querySelector('.floating-island__resize-handle');
    if (!handle) return;

    var resizing = false, startX, startY, startW, startH;

    handle.addEventListener('mousedown', function(e) {
        resizing = true;
        var rect = island.getBoundingClientRect();
        startX = e.clientX;
        startY = e.clientY;
        startW = rect.width;
        startH = rect.height;
        island.style.transition = 'none';
        e.preventDefault();
        e.stopPropagation();
    });

    document.addEventListener('mousemove', function(e) {
        if (!resizing) return;
        var w = Math.max(300, startW + (e.clientX - startX));
        var h = Math.max(200, startH + (e.clientY - startY));
        island.style.width  = w + 'px';
        island.style.height = h + 'px';
    });

    document.addEventListener('mouseup', function() {
        if (!resizing) return;
        resizing = false;
        island.style.transition = '';
    });
})();
</script>
JS;

    return $html . $dragJs . $resizeJs;
}



// ──────────────────────────────────────────────────────────────────────────
//  2.  LOAD COMMENTS ISLAND  (dynamic — fetches comments via AJAX)
// ──────────────────────────────────────────────────────────────────────────
/**
 * Build a floating island that loads + displays file comments on-the-fly.
 *
 * Uses the existing commentsEndpoint.php AJAX backend so it stays in sync
 * with the same data the elFinder "See Comments" command reads/writes.
 *
 * @param  string  $filepath   The URL-encoded server path to the file/folder.
 * @return string              An HTML snippet (island shell + inline script).
 */
function LoadCommentsIsland($filepath)
{
    $fileUrl = htmlspecialchars($filepath, ENT_QUOTES, 'UTF-8');

    // Check if comments are locked for clients
    $isCommentLocked = false;
    $role = $_SESSION['role'] ?? '';
    if ($role === 'client') {
        include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
        $GLOBALS['db'] = DBConnect();
        $lock = IsFileLocked($filepath);

        if ($lock && (int)$lock['commentlock'] === 1) {
            $isCommentLocked = true;
        }
    }

    $containerId = 'fi-comments-' . md5($filepath);

    // The body starts with a loading indicator; the script below replaces it.
    $bodyHtml = <<<BODY
<div id="{$containerId}">
    <p class="seecm-loading">Loading comments…</p>
</div>
BODY;

    // Before the $js heredoc, determine the add-form HTML to inject
    if ($isCommentLocked) {
        $addFormHtml = "'<p style=\"color:var(--color-text-muted);font-style:italic;margin-top:12px;\">Comments are locked on this file.</p>'";
    } else {
        $addFormHtml = <<<FORM
        '<hr class="seecm-divider">' +
        '<div class="seecm-add-form">' +
        '<textarea id="{$containerId}-input" placeholder="Write a comment…" style="width:100%;height:64px;box-sizing:border-box;padding:10px 12px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;resize:vertical;"></textarea>' +
        '<button id="{$containerId}-submit" style="margin-top:8px;">Add Comment</button>' +
        '</div>'
    FORM;
    }


    $js = <<<JS
<script>
(function() {
    var container = document.getElementById('{$containerId}');
    if (!container) return;

    function loadComments() {
        container.innerHTML = '<p class="seecm-loading">Loading comments…</p>';

        $.get('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
            action: 'fetch',
            file_url: '{$fileUrl}'
        }, function(response) {
            if (!response.success) {
                container.innerHTML = '<p class="seecm-status-error">Failed to load comments.</p>';
                return;
            }

            var html = '<div class="seecm-comments-list">';

            if (response.comments.length === 0) {
                html += '<p class="seecm-status-empty">No comments yet.</p>';
            } else {
                $.each(response.comments, function(i, c) {
                    html += '<div class="seecm-comment">';
                    html += '<div class="seecm-comment__header">';
                    html += '<span class="seecm-comment__author">' + $('<span>').text(c.owner).html() + '</span>';
                    html += '<span class="seecm-comment__time">' + $('<span>').text(c.comment_time).html() + '</span>';
                    html += '</div>';
                    html += '<p class="seecm-comment__body">' + c.comment_content + '</p>';
                    html += '</div>';
                });
            }
            html += '</div>';
            html += {$addFormHtml};


            container.innerHTML = html;

            // Wire up the submit button
            $('#{$containerId}-submit').on('click', function() {
                var content = $('#{$containerId}-input').val().trim();
                if (!content) return;

                $.post('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
                    action: 'add',
                    file_url: '{$fileUrl}',
                    content: content
                }, function(addResponse) {
                    if (addResponse.success) {
                        $('#{$containerId}-input').val('');
                        loadComments();  // re-fetch to show new comment
                    } else {
                        alert('Failed to add comment: ' + (addResponse.error || 'unknown error'));
                    }
                }, 'json').fail(function() {
                    alert('Failed to add comment due to a server error.');
                });
            });

            // Stop elFinder hotkeys from firing while typing
            $('#{$containerId}-input').on('keydown', function(e) { e.stopPropagation(); });

        }, 'json').fail(function() {
            container.innerHTML = '<p class="seecm-status-error">Failed to load comments due to a server error.</p>';
        });
    }

    loadComments();
})();
</script>
JS;

    return SpawnFloatingIsland($bodyHtml . $js, 'Comments');
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
function LoadSendToDiscordIsland($files)
{
    if (empty($files) || !is_array($files)) {
        return SpawnFloatingIsland(
            '<p class="seecm-status-empty">No files selected to send.</p>',
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

    // Build a JSON blob the endpoint expects: [{ name: "…", url: "…" }, …]
    $filesJson = json_encode($files);

    // We need a unique ID per instance so multiple islands can coexist
    $uid = 'fi-discord-' . md5(uniqid('', true));

    $bodyHtml = <<<BODY
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

<button id="{$uid}-send" style="margin-top:4px;">Send to Discord</button>
<div id="{$uid}-status" style="margin-top:10px;"></div>
BODY;

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

        var formData = new FormData();
        formData.append('action', channel.value);
        formData.append('files', '{$filesJson}');
        if (note) {
            formData.append('note', note);
        }

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
            '<p class="seecm-status-empty">No files selected to move.</p>',
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
