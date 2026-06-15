<?php
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
require __ROOT__ . '/libraries/elfinder/php/autoload.php';
require_once __ROOT__ . '/libraries/elfinderLibs/SimeckVolumeDriver.php';

// Get role-specific options
$elfinderOptions = GetRoleElfinderOptions();

// Apply tmbVideoConvLen to all volumes only (no accessControl override)
foreach ($elfinderOptions['roots'] as &$root) {
    $root['tmbVideoConvLen'] = 50000000000;
}
unset($root);

$connector = new elFinderConnector(new elFinder($elfinderOptions));
$connector->run();
