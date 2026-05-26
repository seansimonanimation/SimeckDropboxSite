<?php
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
require __ROOT__ . '/libraries/elfinder/php/autoload.php';

// Custom attributes callback function
function checkFileLock($attr, $path, $data, $volume) {
    // Only check for files under /files/Projects
    if (strpos($path, __ROOT__ . '/files/Projects') === 0) {
        $normalizedPath = NormalizeFilePath($path);
        if ($normalizedPath) {
            $lock = IsFileLocked($normalizedPath);
            if ($lock) {
                return array('locked' => true);
            }
        }
    }
    return null;
}

// Get admin options and modify the Project volume
$adminOptions = getAdminFileBrowserOptions();
$projectVolumeIndex = 2; // Project volume is the 3rd volume (index 2)
if (isset($adminOptions['roots'][$projectVolumeIndex])) {
    $adminOptions['roots'][$projectVolumeIndex]['attributes'] = array('checkFileLock');
}

$connector = new elFinderConnector(new elFinder($adminOptions));
$connector->run();
?>
