<?php
/**
 * @module artistPortfolioEditor
 * @name PortfolioEditor
 * @role artist
 * @nav-text Portfolio Editor
 * @nav-icon Files
 * @nav-order 30
 */

include_once __ROOT__ . '/libraries/portfoliolib.php';

// Only artists and admins (impersonating) can access
$allowedRoles = ['artist', 'admin'];
if (!in_array(GetTempRole(), $allowedRoles)) {
    echo '<div class="module"><p>Access denied.</p></div>';
    return;
}

$username = $_SESSION['username'];
$portfolioDir = GetPortfolioWebPath($username);

// --- Read-only check ---
$readOnly = IsImpersonating();

// Load portfolio data for initial state
$portfolio = LoadPortfolio($username);
$pfpFile = FindPortfolioPfp($username);
$pfpUrl = $pfpFile ? $portfolioDir . '/' . $pfpFile : '';
$files = ListPortfolioFiles($username);

?>
<div class="module portfolio-editor-module">
    <div class="portfolio-editor-container">
        <!-- Toolbar -->
        <div class="portfolio-toolbar">
            <div class="portfolio-toolbar-left">
                <?php if (!$readOnly): ?>
                <button class="portfolio-btn" id="portfolio-import-btn" title="Add a file to the canvas">Add Piece</button>
                <button class="portfolio-btn" id="portfolio-add-text-btn" title="Add a text box">Add Text</button>
                <?php endif; ?>
                <button class="portfolio-btn" id="portfolio-undo-btn" title="Undo" disabled>Undo</button>
                <button class="portfolio-btn" id="portfolio-redo-btn" title="Redo" disabled>Redo</button>
                <?php if (!$readOnly): ?>
                <button class="portfolio-btn portfolio-save-btn" id="portfolio-save-btn" title="Save portfolio">Save</button>
                <?php endif; ?>
            </div>
            <div class="portfolio-toolbar-right">
                <button class="portfolio-btn" id="portfolio-zoom-fit-btn" title="Fit all pieces to screen">Fit to Screen</button>
                <button class="portfolio-btn" id="portfolio-gallery-order-btn" title="Arrange gallery order">Arrange Gallery Order</button>
                <?php if (!$readOnly): ?>
                <label class="portfolio-publish-toggle" title="Publish portfolio to main site">
                    <span class="portfolio-publish-label">Publish</span>
                    <input type="checkbox" id="portfolio-publish-checkbox" <?= ($portfolio['publish_portfolio'] ?? 0) ? 'checked' : '' ?>>
                    <span class="portfolio-publish-slider"></span>
                </label>
                <?php else: ?>
                <span class="portfolio-readonly-badge">Read Only</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main layout -->
        <div class="portfolio-main-layout">
            <!-- Canvas area -->
            <div class="portfolio-canvas-wrapper" id="portfolio-canvas-wrapper">
                <div class="portfolio-canvas" id="portfolio-canvas">
                    <!-- Pieces rendered here by JS -->
                </div>
                    <div class="portfolio-zoom-controls" id="portfolio-zoom-controls">
                    <button class="portfolio-zoom-btn" id="portfolio-zoom-out-btn" title="Zoom Out">−</button>
                    <span class="portfolio-zoom-level" id="portfolio-zoom-text">100%</span>
                    <button class="portfolio-zoom-btn" id="portfolio-zoom-in-btn" title="Zoom In">+</button>
                    <button class="portfolio-zoom-btn" id="portfolio-zoom-fit-btn" title="Fit to Screen">⊡</button>
                </div>
                <div class="portfolio-canvas-zoom-indicator" id="portfolio-zoom-indicator">
                    <span id="portfolio-zoom-text">100%</span>
                </div>
            </div>

            <!-- Right panel -->
            <div class="portfolio-right-panel" id="portfolio-right-panel">
                <!-- Profile Section -->
                <div class="portfolio-panel-section" id="portfolio-profile-section">
                    <div class="portfolio-panel-section-title">Profile</div>
                    <div class="portfolio-pfp-area" id="portfolio-pfp-area">
                        <?php if ($pfpUrl): ?>
                        <img src="<?= htmlspecialchars($pfpUrl) ?>" id="portfolio-pfp-img" class="portfolio-pfp-img" alt="Profile">
                        <?php else: ?>
                        <div class="portfolio-pfp-placeholder" id="portfolio-pfp-img"><?= strtoupper(substr($_SESSION['firstname'], 0, 1) . substr($_SESSION['lastname'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <?php if (!$readOnly): ?>
                        <input type="file" id="portfolio-pfp-input" accept="image/*" style="display:none">
                        <div class="portfolio-pfp-overlay">Change</div>
                        <?php endif; ?>
                    </div>
                    <input type="text" class="portfolio-input" id="portfolio-display-name" placeholder="Display Name" value="<?= htmlspecialchars($portfolio['artist']['display_name'] ?? GetHumanName('firstlast')) ?>" <?= $readOnly ? 'disabled' : '' ?>>
                    <textarea class="portfolio-textarea" id="portfolio-bio" placeholder="Tell us about yourself..." <?= $readOnly ? 'disabled' : '' ?>><?= htmlspecialchars($portfolio['artist']['bio'] ?? '') ?></textarea>

                    <div class="portfolio-links-section">
                        <div class="portfolio-links-header">
                            <span>Links</span>
                            <?php if (!$readOnly): ?>
                            <button class="portfolio-link-add-btn" id="portfolio-link-add-btn">+ Add Link</button>
                            <?php endif; ?>
                        </div>
                        <div class="portfolio-links-list" id="portfolio-links-list">
                            <!-- Links rendered here by JS -->
                        </div>
                    </div>
                </div>

                <!-- Channel Box (Properties Panel) -->
                <div class="portfolio-panel-section portfolio-channel-box" id="portfolio-channel-box" style="display:none;">
                    <div class="portfolio-panel-section-title">Channel Box</div>
                    <div class="portfolio-channel-fields">
                        <div class="portfolio-channel-row">
                            <label>X</label>
                            <input type="number" class="portfolio-channel-input" id="channel-x" step="1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Y</label>
                            <input type="number" class="portfolio-channel-input" id="channel-y" step="1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Z-Depth</label>
                            <input type="number" class="portfolio-channel-input" id="channel-z" step="1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Rot</label>
                            <input type="number" class="portfolio-channel-input" id="channel-rot" step="0.1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Scale X</label>
                            <input type="number" class="portfolio-channel-input" id="channel-scalex" step="0.01" min="0.01" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Scale Y</label>
                            <input type="number" class="portfolio-channel-input" id="channel-scaley" step="0.01" min="0.01" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Caption</label>
                            <input type="text" class="portfolio-channel-input" id="channel-caption" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row">
                            <label>Gallery Order</label>
                            <input type="number" class="portfolio-channel-input" id="channel-gallery-order" step="1" min="1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <div class="portfolio-channel-row" id="channel-fontsize-row" style="display:none;">
                            <label>Font Size</label>
                            <input type="number" class="portfolio-channel-input" id="channel-fontsize" step="1" min="1" <?= $readOnly ? 'disabled' : '' ?>>
                        </div>
                        <?php if (!$readOnly): ?>
                        <button class="portfolio-remove-btn" id="portfolio-remove-piece-btn">Remove</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden file input for import -->
<input type="file" id="portfolio-file-input" accept="image/*,video/mp4,application/pdf,text/plain" style="display:none" multiple>

<!-- Gallery Order Floating Island -->
<div class="portfolio-floating-island" id="portfolio-gallery-island" style="display:none;">
    <div class="portfolio-island-header">
        <span>Gallery Order</span>
        <button class="portfolio-island-close-btn" id="portfolio-gallery-close-btn">&times;</button>
    </div>
    <div class="portfolio-island-body" id="portfolio-gallery-list">
        <!-- Draggable list rendered by JS -->
    </div>
    <div class="portfolio-island-footer">
        <button class="portfolio-btn" id="portfolio-gallery-done-btn">Done</button>
    </div>
</div>

<script>
// Pass PHP data to JS
window.__PORTFOLIO_CONFIG__ = {
    username: '<?= addslashes($username) ?>',
    readOnly: <?= $readOnly ? 'true' : 'false' ?>,
    portfolioDir: '<?= addslashes($portfolioDir) ?>',
    pfpUrl: '<?= addslashes($pfpUrl) ?>',
    portfolioJson: <?= json_encode($portfolio, JSON_UNESCAPED_SLASHES) ?>,
    files: <?= json_encode($files) ?>,
    displayName: '<?= addslashes(GetHumanName('firstlast')) ?>'
};
</script>

<!-- Load portfolio editor scripts -->
<script src="libraries/portfolioEditor/portfolioSerializer.js"></script>
<script src="libraries/portfolioEditor/portfolioRenderer.js"></script>
<script src="libraries/portfolioEditor/portfolioInteraction.js"></script>
<script src="libraries/portfolioEditor/portfolioUploader.js"></script>
<script src="libraries/portfolioEditor/portfolioProfilePanel.js"></script>
<script src="libraries/portfolioEditor/portfolioChannelBox.js"></script>
<script src="libraries/portfolioEditor/portfolioEditor.js"></script>

<!-- Load CSS -->
<link rel="stylesheet" href="libraries/portfolioEditor/portfolioEditor.css">
