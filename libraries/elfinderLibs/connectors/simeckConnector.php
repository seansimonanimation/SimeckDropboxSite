<?php
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
require __ROOT__ . '/libraries/elfinder/php/autoload.php';

// Get admin options
$elfinderOptions = array();
switch($_SESSION['tempRole']){
    case 'admin':
        $elfinderOptions = getAdminFileBrowserOptions();
        break;
    case 'artist':
        $elfinderOptions = getArtistFileBrowserOptions();
        break;
    case 'client':
        $elfinderOptions = getClientFileBrowserOptions();
}

// Apply lock accessControl to every volume that has a path under /files/Projects
foreach ($elfinderOptions['roots'] as &$root) {
    if (isset($root['path']) && strpos($root['path'], __ROOT__ . '/files/Projects') === 0) {
        $root['accessControl'] = 'lockAccessControl';
    }
}
unset($root);

$connector = new elFinderConnector(new elFinder($elfinderOptions));
$connector->run();
