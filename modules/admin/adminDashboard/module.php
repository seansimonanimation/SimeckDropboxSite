<?php
//The module responsible for Dashboard content on the admin portal. 
// yep

/**
 * @module adminDashboard
 * @name Dashboard
 * @role admin
 * @nav-text Admin Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/dashboardlib.php';

?>
<div class="module">
    <div class="module-header">
        <h1 class="module-title">Welcome to the Simeck Admin Portal!</h1>
        <br />
    </div>
    <div class="module-grid">
        <!-- Row 1: 4 cards, each 1 column (no span class needed) -->
        <div class="module-card"><center><h3> Number of active clients</h3>
        <p><?php echo GetClientCount(false); ?></p></center>
    </div>
        <div class="module-card"><center><h3> Number of active artists</h3>
        <p><?php echo GetArtistCount(false); ?></p></center>
    </div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card"><h1> Drive Status</h1>
    <h2>Drive Name:</h2>Larson<br />
    <h2> Drive Usage:</h2>
    <?php echo GetNASUsage(); ?>
    </div>
        
        <!-- Row 2: 2 cards, each spanning 2 columns -->
        <div class="module-card module-card--span-2"><center><h1>Total number of comments</h1>
        <h1><?php echo GetTotalCommentCount(); ?> </h1>
        </div>

        <div class="module-card module-card--span-2">
            <h1>Download link analyzer</h1>
            <div style="margin-top:12px;">
                <input type="text" id="link-analyzer-input"
                       placeholder="Paste a download link or token..."
                       style="width:100%;padding:8px 12px;box-sizing:border-box;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;">
                <button id="link-analyzer-btn" style="margin-top:8px;">Analyze</button>
            </div>
            <div id="link-analyzer-results" style="margin-top:16px;display:none;">
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr>
                        <td style="font-weight:600;padding:6px 8px;width:120px;white-space:nowrap;vertical-align:top;">Link Creator</td>
                        <td id="la-creator" style="padding:6px 8px;"></td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;padding:6px 8px;white-space:nowrap;vertical-align:top;">Link Type</td>
                        <td id="la-type" style="padding:6px 8px;"></td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;padding:6px 8px;white-space:nowrap;vertical-align:top;">Link Content</td>
                        <td id="la-content" style="padding:6px 8px;"></td>
                    </tr>
                </table>
            </div>
            <div id="link-analyzer-error" style="margin-top:12px;color:var(--color-danger);display:none;"></div>
        </div>
        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="module-card module-card--span-4"><h1>Changelog</h1>
    <p><?php echo DisplayChangelog(); ?></p></div>
    </div>
</div>

<script>
(function() {
    var input = document.getElementById('link-analyzer-input');
    var btn = document.getElementById('link-analyzer-btn');
    var resultsDiv = document.getElementById('link-analyzer-results');
    var errorDiv = document.getElementById('link-analyzer-error');
    var laCreator = document.getElementById('la-creator');
    var laType = document.getElementById('la-type');
    var laContent = document.getElementById('la-content');

    function analyze() {
        var raw = input.value.trim();
        if (!raw) return;

        var token = raw;
        var dlPrefix = 'download=';
        var dlIndex = token.indexOf(dlPrefix);
        if (dlIndex !== -1) {
            token = token.substring(dlIndex + dlPrefix.length);
            var ampIndex = token.indexOf('&');
            if (ampIndex !== -1) token = token.substring(0, ampIndex);
        }
        token = decodeURIComponent(token);

        errorDiv.style.display = 'none';
        resultsDiv.style.display = 'none';
        Helpers.loading(btn, true);

        Helpers.get('index.php', { action: 'analyze_download_token', token: token })
            .then(function(data) {
                if (!data.success) {
                    errorDiv.textContent = data.error || 'Failed to analyze token.';
                    errorDiv.style.display = 'block';
                    Helpers.alertIsland('Analysis Failed', data.error || 'Failed to analyze token.', 'error');
                    return;
                }

                var modeNames = {
                    'internal':       'Internal',
                    'clientPreview':  'Client Preview',
                    'thumbnail':      'Thumbnail',
                    'deliverable':    'Deliverable',
                    'document':       'Document'
                };
                var modeDisplay = modeNames[data.mode] || data.mode;

                var contentHtml = '';
                if (data.thumbnail_url) {
                    var thumbUrl = 'download.php?download=' + encodeURIComponent(data.thumbnail_url);
                    contentHtml = '<img src="' + thumbUrl + '" height="80" style="cursor:pointer;border:1px solid var(--color-border-dim);border-radius:4px;" onclick="openPreviewIsland(\'' + encodeURIComponent(data.filepath) + '\')" title="Click to preview">';
                } else {
                    contentHtml = '<span style="color:var(--color-text-muted);">No thumbnail available</span>';
                }

                laCreator.textContent = data.author_name || data.author;
                laType.textContent = modeDisplay;
                laContent.innerHTML = contentHtml;
                resultsDiv.style.display = 'block';
            })
            .catch(function(err) {
                Helpers.alertIsland('Request Failed', 'Could not complete request: ' + err.message, 'error');
            })
            .finally(function() {
                Helpers.loading(btn, false, 'Analyze');
            });
    }

    btn.addEventListener('click', analyze);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') analyze();
    });
})();

function openPreviewIsland(filepath) {
    Helpers.get('index.php', { action: 'get_preview_island', filepath: filepath })
        .then(function(data) {
            if (data.success && data.html) {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = data.html;
                while (wrapper.firstChild) {
                    document.body.appendChild(wrapper.firstChild);
                }
            } else {
                Helpers.alertIsland('Preview Failed', data.error || 'Failed to load preview.', 'error');
            }
        })
        .catch(function(err) {
            Helpers.alertIsland('Request Failed', 'Could not load preview: ' + err.message, 'error');
        });
}
</script>
