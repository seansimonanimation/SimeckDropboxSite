<?php
Define('__ELFINDER_ROOT__','libraries/elfinder/');
include_once __ROOT__ . '/libraries/elfinderlib.php';
require __ELFINDER_ROOT__ . 'php/autoload.php';
$connector = new elFinderConnector(new elFinder(getAdminFileBrowserOptions()));
$connector->run();

?>