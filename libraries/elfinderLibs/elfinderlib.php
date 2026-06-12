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

function LoadElfinderJSCommands() { //loads the command and command override functions dynamically.
    $html = '';
    $webBase = '/libraries/elfinderLibs/elfinderCommands/';
    $fsBase = __ROOT__ . $webBase;

    if (!is_dir($fsBase)) return '';
    //Hardcode in the shared lib so that it loads fist.
    $html .= '<script src="' . $webBase . 'CommonFuncs.js" type="text/javascript" charset="utf-8"></script>' . "\n";
    
    foreach (new DirectoryIterator($fsBase) as $file) {
        if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'js' || $file->getFilename() === 'CommonFuncs.js') continue;
        // Convert filesystem path to web path
        $webPath = str_replace(__ROOT__, '', $file->getPathname());
        $webPath = str_replace('\\', '/', $webPath); // Windows backslashes → forward slashes
        $html .= '<script src="' . $webPath . '" type="text/javascript" charset="utf-8"></script>' . "\n";
    }
    return $html;
}

function ApplyElfinderCommandOverrides() { //actually applies the commands that have been loaded.
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
function OutputSimeckSessionScript() {
    $sessionData = [
        'username'             => $_SESSION['username'] ?? null,
        'firstname'            => $_SESSION['firstname'] ?? null,
        'lastname'             => $_SESSION['lastname'] ?? null,
        'userID'               => $_SESSION['userID'] ?? null,
        'role'                 => $_SESSION['role'] ?? null,
        'tempRole'             => $_SESSION['tempRole'] ?? null,
        'theme'                => $_SESSION['theme'] ?? 'dark-boo',
        'timezone'             => $_SESSION['timezone'] ?? 'UTC',
        'impersonating'        => $_SESSION['impersonating'] ?? false,
        'project_assignments'  => $_SESSION['project_assignments'] ?? null,
        'point_of_contact'     => $_SESSION['point_of_contact'] ?? null,
        'lock_overrides'       => $_SESSION['lock_overrides'] ?? null,
    ];
    echo '<script>window.simeckSession = ' . json_encode($sessionData, JSON_PRETTY_PRINT) . ';</script>' . "\n";
}
function DecodeElfinderHash($hash, $elfinderOptions) {
    // elFinder hash format: "<volumeId><base64_of_path>"
    $underscorePos = strpos($hash, '_');
    if ($underscorePos === false) return null;
    
    $volumeId = substr($hash, 0, $underscorePos + 1);  // e.g., "s1_"
    $encodedPath = substr($hash, $underscorePos + 1);
    
    // Decode elFinder's custom base64 alphabet: - → +, _ → /, . → =
    $encodedPath = str_replace(['-', '_', '.'], ['+', '/', '='], $encodedPath);
    $relativePath = base64_decode($encodedPath, true);
    if ($relativePath === false) return null;
    
    // Get the volume ID number (the number after the driver letter)
    preg_match('/\d+/', $volumeId, $matches);
    $volumeNumber = isset($matches[0]) ? (int)$matches[0] : 0;
    
    // Attempt to find the correct root by its volume ID or position
    // elFinder assigns IDs as {driverLetter}{indexInRoots+1}_
    // But the Trash volume overrides its ID explicitly with 'id' => '1'
    if (isset($elfinderOptions['roots'][$volumeNumber - 1]['path'])) {
        // Use position-based lookup (works for all non-Trash volumes)
        $volumePath = rtrim(str_replace('\\', '/', $elfinderOptions['roots'][$volumeNumber - 1]['path']), '/');
        $fullPath = $volumePath . '/' . ltrim($relativePath, '/');
        
        // Verify the file actually exists at this path
        if (file_exists($fullPath) || is_dir($fullPath)) {
            return $fullPath;
        }
    }
    
    // Fallback: iterate all roots and look for the one whose path matches
    // This catches the Trash volume case
    foreach ($elfinderOptions['roots'] as $root) {
        if (!isset($root['path'])) continue;
        $volumePath = rtrim(str_replace('\\', '/', $root['path']), '/');
        $fullPath = $volumePath . '/' . ltrim($relativePath, '/');
        if (file_exists($fullPath) || is_dir($fullPath)) {
            return $fullPath;
        }
    }
    
    return null;
}
function OutputElfinderCommandsMeta() {
    $dirpath = __ROOT__ . '/libraries/elfinderLibs/elfinderCommands/';
    $metaArray = array();
    
    foreach (new DirectoryIterator($dirpath) as $file) {
        if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'js' || $file->getFilename() === 'CommonFuncs.js') continue;
        
        $content = file_get_contents($file->getPathname());
        
        preg_match('/@commandID\s+(\w+)/', $content, $cmdId);
        preg_match('/@role\s+(\w+)/', $content, $role);
        preg_match('/@loc\s+(.+)/', $content, $loc);
        preg_match('/@order\s+(\d+)/', $content, $order);
        preg_match('/@contextMenuDividers\s+(\w+)/', $content, $divider);
        
        if (empty($cmdId)) continue;
        
        $locStr = trim($loc[1] ?? 'files');
        $locArray = preg_split('/[\s,]+/', $locStr);
        
        $metaArray[] = array(
            'commandID' => trim($cmdId[1]),
            'role' => trim($role[1] ?? 'client'),
            'loc' => $locArray,
            'order' => (int)($order[1] ?? 99),
            'divider' => trim($divider[1] ?? 'none')
        );
    }
    
    echo '<script>window.elfinderCommandsMeta = ' . json_encode($metaArray, JSON_PRETTY_PRINT) . ';</script>' . "\n";
}
