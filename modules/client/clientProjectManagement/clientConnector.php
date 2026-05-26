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

// Get client options and modify the Project volume
$clientOptions = getClientFileBrowserOptions();
// Note: Client connectors have different volume structure, so we need to modify all volumes
foreach ($clientOptions['roots'] as $index => $root) {
    if (isset($root['path']) && strpos($root['path'], '/files/Projects') !== false) {
        $clientOptions['roots'][$index]['attributes'] = array('checkFileLock');
    }
}

$connector = new elFinderConnector(new elFinder($clientOptions));
$connector->run();
?>
