<?php
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/elfinderlib.php';
require __ROOT__ . '/libraries/elfinder/php/autoload.php';
$connector = new elFinderConnector(new elFinder(getAdminFileBrowserOptions()));
$connector->run();
?>