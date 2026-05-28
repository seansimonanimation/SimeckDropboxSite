<?php
    define('__ELFINDER_ROOT__','libraries/elfinder/');
    include_once __DIR__ . '/../session.php';
    include_once __ROOT__ . '/libraries/db.php';
    $GLOBALS['db'] = DBConnect();

// Load sub-modules
require_once __DIR__ . '/volumeConfig.php';
require_once __DIR__ . '/lockHelpers.php';

function initializeConnector($connectorOptions){
        $connector = new elFinderConnector(new elFinder($connectorOptions));
        $connector->run();
}

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

function ScanForPlugins() {
    $config = ['plugin' => [], 'bind' => []];
    $base   = __DIR__ . '/elfinderplugins';
    
    foreach (new DirectoryIterator($base) as $dir) {
        if ($dir->isDot() || !$dir->isDir()) continue;
        
        $file = $dir->getPathname() . '/plugin.php';
        if (!file_exists($file)) continue;
        
        require_once $file;
        
        $name  = $dir->getFilename();
        $class = 'elFinderPlugin' . $name;
        if (!class_exists($class)) continue;
        
        $config['plugin'][$name] = ['enable' => true];
        $config['bind']['upload.presave'][] = 'Plugin.' . $name . '.onUpLoadPreSave';
    }
    
    return $config;
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
