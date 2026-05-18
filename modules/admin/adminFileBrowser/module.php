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
include_once __ROOT__ . '/libraries/elfinderlib.php';

// Absolute root-relative path to elFinder
define('EF_ROOT', 'libraries/elfinder');

// Helper to load a glob of CSS files
function loadElfinderCss($dir) {
    $html = '';
    $files = glob($dir . '/*.css');
    if (!$files) return '';
    sort($files);
    foreach ($files as $file) {
        $html .= '<link rel="stylesheet" href="' . $file . '" type="text/css">' . "\n";
    }
    return $html;
}

// Helper to load a glob of JS files
function loadElfinderJs($dir) {
    $html = '';
    $files = glob($dir . '/*.js');
    if (!$files) return '';
    sort($files);
    foreach ($files as $file) {
        $html .= '<script src="' . $file . '" type="text/javascript" charset="utf-8"></script>' . "\n";
    }
    return $html;
}
?>
<!-- elFinder CSS and addon theme -->
<?php echo loadElfinderCss(EF_ROOT . '/css'); ?>
<link href="css/elfinderThemes/win10/css/theme.css" rel="stylesheet">

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

<!-- elfinder commands -->
<?php echo loadElfinderJs(EF_ROOT . '/js/commands'); ?>

<!-- elfinder extras & proxy -->
<script src="<?php echo EF_ROOT; ?>/js/proxy/elFinderSupportVer1.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/editors.default.js"></script>
<script src="<?php echo EF_ROOT; ?>/js/extras/quicklook.googledocs.js"></script>

<!-- elfinder initialization -->
<script>

$(function() {
    function resizeElfinder() {
        var winH = $(window).height();
        var offset = $('#elfinder').offset().top;
        var h = winH - offset - 15; // 15px breathing room
        if (h < 300) h = 300;
        $('#elfinder').height(h);
        
        var instance = $('#elfinder').elfinder('instance');
        if (instance) {
            instance.resize();
        }
    }


elFinder.prototype.commands.sendToMondayChat = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Monday Chat';
    };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        alert('If this was implemented, it would have sent ' + files.length + ' file(s) To Monday Chat!');
        return $.Deferred().resolve();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};

elFinder.prototype.commands.sendToThursdayChat = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Thursday Chat';
    };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        alert('If this was implemented, it would have sent ' + files.length + ' file(s) To Thursday Chat!');
        return $.Deferred().resolve();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};






elFinder.prototype.commands.seecm = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'See Comments';
    };
    
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            alert(' Once comments is implemented, you will be able to see comments for: ' + files[0].name);
        } else {
            alert('You can only see comments for one file at a time, you silly goose!');
        }
        return $.Deferred().resolve();
    };
    
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
elFinder.prototype.i18.en.cmdseecm = 'See Comments';
    $('#elfinder').elfinder({
        cssAutoLoad: false,
        baseUrl: '<?php echo EF_ROOT; ?>/',
        url: 'modules/admin/adminFileBrowser/adminConnector.php',
        height: $(window).height() - $('#elfinder').offset().top - 15,
    });
    
    $(window).on('resize', resizeElfinder);
});
</script>

<!-- Element where elFinder will be created -->
<div id="elfinder" style="height: 100%;"><center><h1>Thank you for your patience while your filebrowser is loading...</h1></center></div>