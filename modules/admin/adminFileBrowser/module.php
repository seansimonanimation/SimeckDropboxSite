<?php
//The module responsible for displaying the file browser to admins. This is a wrapper for elFinder, which is included in the libraries folder.

/**
 * @module adminFileBrowser
 * @name FileBrowser
 * @role admin
 * @nav-text Full File Browser
 * @nav-icon Files
 * @nav-order 10
 */
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
<script src="modules/admin/adminFileBrowser/elfinderOptionOverride.js"></script>
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

<!-- elFinder override commands and new commands go here -->
<?php echo LoadElfinderJSCommands(); ?>

<!-- elfinder stock commands -->
<?php echo loadElfinderJs(EF_ROOT . '/js/commands'); ?>

<!-- elfinder extras & proxy -->
<script src="<?php echo EF_ROOT; ?>/js/proxy/elFinderSupportVer1.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/editors.default.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/quicklook.googledocs.js"></script>
<script src="libraries/elfinderLibs/elfinderInit.js" type="text/javascript" charset="utf-8"></script>

<!-- elfinder initialization -->
<?php echo ApplyElfinderCommandOverrides(); ?>
<script>
    $(function() {
        $('#elfinder').elfinder({
            cssAutoLoad: false,
            baseUrl: 'libraries/elfinder/',
            url: 'libraries/elfinderLibs/connectors/adminConnector.php',
            height: $(window).height() - $('#elfinder').offset().top,
            width: $(window).width()*0.7,
            role: 'admin',
        });
        
        $(window).on('resize', resizeElfinder);
    });
</script>

<!-- Element where elFinder will be created -->
<div class="file-browser-container">
    <div id="elfinder"><center><h1>If you are seeing this, that means that your file browser is broken. Please reach out to your point of contact.</h1></center></div>
    
    <!-- Preview Pane - part of module content -->
    <div id="preview-pane" class="preview-pane">
        <div class="preview-header">
            <h3>Preview</h3>
            <button class="close-preview" id="close-preview">✕</button>
        </div>
        <div class="preview-content">
            <p>Select a file to view details</p>
        </div>
    </div>
</div>