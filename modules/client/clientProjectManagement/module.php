<?php
//The module responsible for displaying the file browser to admins. This is a wrapper for elFinder, which is included in the libraries folder.

/**
 * @module clientProjectManagement
 * @name ProjectManagement
 * @role client
 * @nav-text Project Files
 * @nav-icon Files
 * @nav-order 10
 */
if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

// Absolute root-relative path to elFinder
define('EF_ROOT', 'libraries/elfinder');

// Helper to load a glob of CSS files
?>
<!-- elFinder CSS and addon theme -->
<?php echo loadElfinderCss(EF_ROOT . '/css'); ?>
<link href="css/portal.css" rel="stylesheet">
<link href="css/elfinderThemes/simeck-responsive/theme.css" rel="stylesheet">
<link href="css/comments.css" rel="stylesheet">
<!-- jQuery and jQuery UI (REQUIRED) -->
<script src="https://code.jquery.com/jquery-4.0.0.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
<!-- elfinder core (load in specific order) -->
<script src="<?php echo EF_ROOT; ?>/js/elFinder.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.version.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/jquery.elfinder.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.mimetypes.js"></script>
<?php echo OutputElfinderCommandsMeta(); ?>
<?php OutputSimeckSessionScript(); ?>
<script src="libraries/elfinderLibs/opt/elfinderOptionsCommands.js"></script>
<script src="libraries/elfinderLibs/opt/<?php echo $_SESSION['tempRole'];?>ElfinderOptions.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.options.netmount.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.history.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.command.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/elFinder.resources.js"></script>
<!-- elfinder dialog -->
<script src="<?php echo EF_ROOT; ?>/js/jquery.dialogelfinder.js"></script>
<!-- elfinder default lang -->
<script src="<?php echo EF_ROOT; ?>/js/i18n/elfinder.en.js"></script>
<!-- elfinder ui -->
<?php echo loadElfinderJs(EF_ROOT . '/js/ui'); ?>

<script src="/libraries/linklib.js"></script>
<script src="/libraries/clipboardlib.js"></script>

<!-- elFinder override commands and new commands go here -->
<?php echo ApplyElfinderCommandOverrides(); ?>
<!-- elfinder stock commands -->
<?php echo loadElfinderJs(EF_ROOT . '/js/commands'); ?>
<?php echo LoadElfinderJSCommands(); ?>
<!-- elfinder extras & proxy -->
<script src="<?php echo EF_ROOT; ?>/js/proxy/elFinderSupportVer1.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/editors.default.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/quicklook.googledocs.js"></script>

<!-- elfinder initialization -->
<?php OutputSimeckSessionScript(); ?>
 <script src="libraries/elfinderLibs/elfinderInit.js"></script>
<script>
$(function() {
        var fm = $('#elfinder').elfinder({
        cssAutoLoad: false,
        baseUrl: 'libraries/elfinder/',
        url: `libraries/elfinderLibs/connectors/simeckConnector.php`,
        height: $(window).height() - $('#elfinder').offset().top,
        role: window.simeckSession.tempRole,
        });
        fm = $('#elfinder').elfinder('instance');
        fm.simeckSession = window.simeckSession;
        populateLockCache(fm);
        bindLockRefreshOnNavigate(fm);
        // Update preview pane when file selection changes
        fm.bind('select', function() {
            updatePreviewPane(fm);
        });
        $(window).on('resize', resizeElfinder);
        // Toggle preview pane on button click
        $('#preview-toggle').on('click', togglePreviewPane);
        // Override open command for preview island
        overrideOpenCommand(fm);


});
</script>


<!-- Element where elFinder will be created -->
<div class="file-browser-container">
    <div id="elfinder"><center><h1>If you are seeing this, that means that your file browser is broken. Please reach out to your point of contact.</h1></center></div>
    
    <!-- Preview Pane - part of module content -->
<div class="preview-sidebar">
    <button class="preview-toggle" id="preview-toggle">❯</button>
    <div id="preview-pane" class="preview-pane">
        <div class="preview-header">
            <h3>Preview</h3>
        </div>
        <div class="preview-content">
            <p>Select a file to view details</p>
        </div>
    </div>
</div>
