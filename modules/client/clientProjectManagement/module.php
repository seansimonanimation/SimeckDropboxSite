<?php
//The module responsible for displaying the file browser to admins. This is a wrapper for elFinder, which is included in the libraries folder.

/**
 * @module clientProjectManagement
 * @name ProjectManagement
 * @role client
 * @nav-text Project Management
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
<script src="modules/client/clientProjectManagement/elfinderOptionOverride.js"></script>
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

<!-- elfinder commands -->
<?php echo loadElfinderJs(EF_ROOT . '/js/commands'); ?>

<!-- elfinder extras & proxy -->
<script src="<?php echo EF_ROOT; ?>/js/proxy/elFinderSupportVer1.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/editors.default.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/quicklook.googledocs.js"></script>
<!-- elfinder initialization -->
<script src="libraries/elfinderLibs/elfinderInit.js"></script>
<script>
    $(function() {
        $('#elfinder').elfinder({
            cssAutoLoad: false,
            baseUrl: 'libraries/elfinder/',
            url: 'modules/client/clientProjectManagement/clientConnector.php',
            height: $(window).height() - $('#elfinder').offset().top - 15,
        });
        
        $(window).on('resize', resizeElfinder);
    });
</script>
<!-- Element where elFinder will be created -->
<div id="elfinder" style="height: 100%;"><center><h1>Thank you for your patience while your filebrowser is loading...</h1></center></div>