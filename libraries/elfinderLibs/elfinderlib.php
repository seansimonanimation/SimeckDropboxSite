<?php
    define('__ELFINDER_ROOT__','libraries/elfinder/');
    include_once __DIR__ . '/../session.php';
    include_once __ROOT__ . '/libraries/db.php';
    $GLOBALS['db'] = DBConnect();

// Load sub-modules
require_once __DIR__ . '/volumeConfig.php';
require_once __DIR__ . '/lockHelpers.php';

function ConnectorSetup(){
    require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';
}

function AttachOrCreateDropbox(){
    $dropboxpath = __ROOT__ . '/files/Dropboxes/' . $_SESSION['lastname'] . ', ' . $_SESSION['firstname'];
    if(!is_dir($dropboxpath)){
        mkdir($dropboxpath, 0777, true);
        mkdir($dropboxpath . '/.tmb', 0777, true);
        mkdir($dropboxpath . '/new', 0777, true);
        mkdir($dropboxpath . '/older', 0777, true);
    }
    return $dropboxpath;
}

function DetermineMyDropboxURL(){
    return '/files/Dropboxes/' . $_SESSION['lastname'] . '%2C%20' . $_SESSION['firstname'];
}

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

function LoadElfinderJSCommands() {
    $html = '';
    $base = __ROOT__ . '/libraries/elfinderLibs/elfinderCommands/';
    if (!is_dir($base)) return '';
    
    foreach (new DirectoryIterator($base) as $file) {
        if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'js') continue;
        $html .= '<script src="' . $file->getPathname() . '" type="text/javascript" charset="utf-8"></script>' . "\n";
    }
    return $html;
}

function ApplyElfinderCommandOverrides() {
    //Target format
    //elFinder.prototype.i18.en.cmdseecm = 'See Comments';
    //elFinder.prototype.i18.en.cmdtogglelock = 'Lock / Unlock File';
    //elFinder.prototype.i18.en.cmdclientlockoverride = 'Lock Override';
    
    $dirpath = __ROOT__ . '/libraries/elfinderLibs/elfinderCommands/';
    $files = scandir($dirpath);
    $commandArray = array(); // Declare array outside the loop
    
    foreach($files as $file) {
        if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'js') continue;
        
        $filepath = $dirpath . $file;
        $content = file_get_contents($filepath);
        $commandIdMatch = array();
        $niceNameMatch = array();
        
        preg_match('/@commandID\s+(\w+)/', $content, $commandIdMatch);
        preg_match('/@nicename\s+(.+)/', $content, $niceNameMatch);
                    
        // Check if both matches were found
        if (!empty($commandIdMatch) && !empty($niceNameMatch)) {
            $commandArray[] = array(
                'commandID' => trim($commandIdMatch[1]),
                'nicename' => trim($niceNameMatch[1])
            );
        }
    }
    
    // Output the script tags after processing all files
    foreach($commandArray as $command) {
        echo "<script>elFinder.prototype.i18.en.cmd{$command['commandID']} = '{$command['nicename']}';</script>";
    }
}

