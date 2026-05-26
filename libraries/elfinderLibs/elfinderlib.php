<?php
    Define('__ELFINDER_ROOT__','libraries/elfinder/');
    include_once __DIR__ . '/../session.php';
    include_once __ROOT__ . '/libraries/db.php';
    $GLOBALS['db'] = DBConnect();

function getAdminFileBrowserOptions(){
    ConnectorSetup();

    $connectorOptions = array(
        'roots' => array(
            //Personal dropbox volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "My Dropbox",                    // display this instead of real root name
                'path' => AttachOrCreateDropbox(),                 // path to files (REQUIRED)
                'URL'  => DetermineMyDropboxURL(), // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
            ),
            //Everyone's Dropboxes volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "Everyone's Dropboxes",                    // display this instead of real root name
                'path' => __ROOT__ . '/files/Dropboxes',                 // path to files (REQUIRED)
                'URL'  => '/files/Dropboxes/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
            ),
            //Project volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Projects',               // path to files (REQUIRED)
                'URL'  => '/files/Projects/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            //Resources volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "Studio Resources",                    // display this instead of real root name
                'path' => __ROOT__ . '/files/Resources',               // path to files (REQUIRED)
                'URL'  => '/files/Resources/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            array(
                //Corporate volume
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Corporate',                 // path to files (REQUIRED)
                'URL'  => '/files/Corporate/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.trash/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
                //'uploadOrder'   => array('deny', 'allow'),      // Same as above
                'accessControl' => 'access',                    // Same as above
            )
        )

    );

    return $connectorOptions;
}


function getArtistFileBrowserOptions(){
    ConnectorSetup();
    $connectorOptions = array(
        'roots' => array(
            //Personal Dropbox volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "My Dropbox",                    // display this instead of real root name
                'path' => AttachOrCreateDropbox(),                 // path to files (REQUIRED)
                'URL'  => DetermineMyDropboxURL(), // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
                'dotFiles' => false,        // <-- No dotfiles!
            ),
            //Everyone's Dropboxes volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "Everyone's Dropboxes",                    // display this instead of real root name
                'path' => __ROOT__ . '/files/Dropboxes',                 // path to files (REQUIRED)
                'URL'  => '/files/Dropboxes/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
                'dotFiles' => false,        // <-- No dotfiles!
            ),
            //Project volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Projects',               // path to files (REQUIRED)
                'URL'  => '/files/Projects/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access' ,                    // disable and hide dot starting files (OPTIONAL)
                'dotFiles' => false,        // <-- No dotfiles!
            ),
            //Resources volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "Studio Resources",                    // display this instead of real root name
                'path' => __ROOT__ . '/files/Resources',               // path to files (REQUIRED)
                'URL'  => '/files/Resources/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
                'dotFiles' => false,        // <-- No dotfiles!
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.trash/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                //'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
                //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
                //'uploadOrder'   => array('deny', 'allow'),      // Same as above
                'accessControl' => 'access',                    // Same as above
                'dotFiles' => false,        // <-- No dotfiles!
            )
        )

    );

    return $connectorOptions;
}


function getClientFileBrowserOptions(){
    ConnectorSetup();
    $clientassignments = GetClientProjectAssignments();
    $roots = array();
    if(empty($clientassignments)){
        //Client has no project assignments, return empty options to prevent access to any files.
        return $roots;
    }
    
    $placeholders = implode(',', array_fill(0, count($clientassignments), '?'));
    $projectQuery = "SELECT pid, project_name, active_path FROM projects WHERE pid IN ($placeholders)";

    $stmt = $GLOBALS['db']->prepare($projectQuery);
    $stmt->execute($clientassignments);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $connectorOptions = array('roots' => array());

foreach ($projects as $project){
    $ProjectPath = __ROOT__ . $project['active_path'];
    $clientUploadPath = __ROOT__ . $project['active_path'] . 'clientUpload/';
    if(!is_dir($ProjectPath)){ //Create the project folder if it doesn't exist, this should always be true since the project is active, but just in case.
        mkdir($ProjectPath, 0777, true);
    }

    if (!is_dir($clientUploadPath)) {
        mkdir($clientUploadPath, 0777, true);
        @mkdir($clientUploadPath . '.tmb', 0777, true);
    }
        $roots[] = array(
            'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
            'alias'        => $project['project_name'],                    // display this instead of real root name
            'path' => __ROOT__ . $project['active_path'] . 'clientUpload/',                 // path to files (REQUIRED)
            'URL'  => $project['active_path'] . 'clientUpload/', // URL to files (REQUIRED)
            'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
            //'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
            //'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
            'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
            'dotFiles' => false,        // <-- No dotfiles!
        );
    }


    return array('roots' => $roots);
}

function GetClientProjectAssignments(){
    $clientassignments = [];
    $clientid = $_SESSION['username'];
    $query = "SELECT project_assignments FROM clients WHERE email = ?";
    $stmt = $GLOBALS['db']->prepare($query);
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (empty($result['project_assignments'])) {
        return [];
    }
    return explode(',', $result['project_assignments']);
}



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
        //Dropbox doesn't exist, create it
        mkdir($dropboxpath, 0777, true);
        mkdir($dropboxpath . '/.tmb', 0777, true); // Create the .tmb folder for thumbnails
        mkdir($dropboxpath . '/new', 0777, true); // Create the new folder for new uploads
        mkdir($dropboxpath . '/older', 0777, true); // Create the older folder for moved files
    }
    return $dropboxpath;
}

function DetermineMyDropboxURL(){
    return '/files/Dropboxes/' . $_SESSION['lastname'] . '%2C%20' . $_SESSION['firstname'];
}
// ── Lock helpers ──────────────────────────────────────────────

/**
 * Check if a file is locked.
 * @param string $filepath  Root-relative path, e.g. "/files/Projects/..."
 * @return array|false  Lock row if locked, false otherwise.
 */
function IsFileLocked($filepath) {
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lockid, locktime, assetlock, commentlock 
         FROM lockedfiles WHERE filepath = ?'
    );
    $stmt->execute([$filepath]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
}

/**
 * Get all locked file paths under a given directory.
 * @param string $directory  Root-relative dir, e.g. "/files/Projects/internal/P01_C City/"
 * @return array  List of locked file paths.
 */
function GetLockedFilesForDirectory($directory) {
    $directory = rtrim($directory, '/') . '/%';
    $stmt = $GLOBALS['db']->prepare(
        'SELECT filepath FROM lockedfiles WHERE filepath LIKE ?'
    );
    $stmt->execute([$directory]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get a client's available lock overrides.
 * @param string $email  Client's email (username).
 * @return int
 */
function GetClientLockOverrides($email) {
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lock_overrides FROM clients WHERE email = ?'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['lock_overrides'] : 0;
}

/**
 * Consume (decrement by 1) a client's lock override.
 * @param string $email  Client's email (username).
 * @return void
 */
function ConsumeClientLockOverride($email) {
    $stmt = $GLOBALS['db']->prepare(
        'UPDATE clients SET lock_overrides = lock_overrides - 1 
         WHERE email = ? AND lock_overrides > 0'
    );
    $stmt->execute([$email]);
}

/**
 * Normalize an absolute filesystem path to a root-relative /files/… path.
 * @param string $absPath  Absolute path, e.g. "C:/xampp/htdocs/files/Projects/…"
 * @return string|false    e.g. "/files/Projects/…" or false if outside __ROOT__.
 */
function NormalizeFilePath($absPath) {
    $absPath = str_replace('\\', '/', $absPath);
    $root    = str_replace('\\', '/', __ROOT__);
    if (strpos($absPath, $root) !== 0) {
        return false;
    }
    $relative = substr($absPath, strlen($root));
    // Ensure leading slash
    if (strpos($relative, '/') !== 0) {
        $relative = '/' . $relative;
    }
    return $relative;
}



function access($attr, $path, $data, $volume, $isDir, $relpath) {
    $basename = basename($path);
    
    // Deny write operations when impersonating
    if (isset($_SESSION['impersonating']) && $_SESSION['impersonating'] === true) {
        if ($attr == 'write' || $attr == 'locked') {
            return false;
        }
        return $attr == 'read' ? true : null;
    }
    
    // ── File locking enforcement (only on write, for files, under /files/Projects) ──
    if ($attr === 'write' && !$isDir) {
        $normalized = NormalizeFilePath($path);
        // Only enforce locks under /files/Projects
        if ($normalized && strpos($normalized, '/files/Projects') === 0) {
            $lock = IsFileLocked($normalized);
            if ($lock) {
                $role = $_SESSION['role'] ?? '';
                // Admins and artists bypass the lock
                if ($role === 'admin' || $role === 'artist') {
                    // allow — fall through to dotfile check
                } elseif ($role === 'client') {
                    $overrides = GetClientLockOverrides($_SESSION['username']);
                    if ($overrides > 0) {
                        ConsumeClientLockOverride($_SESSION['username']);
                        // allow — fall through to dotfile check
                    } else {
                        return false; // deny write
                    }
                } else {
                    return false; // unknown role, deny
                }
            }
        }
    }
    
    // Dot-file hiding (existing logic)
    return $basename[0] === '.'
             && strlen($relpath) !== 1
        ? !($attr == 'read' || $attr == 'write')
        :  null;
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
