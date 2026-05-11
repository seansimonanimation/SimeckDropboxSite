<?php
    Define('__ELFINDER_ROOT__','libraries/elfinder/');
    include_once __DIR__ . '/session.php';


function getAdminFileBrowserOptions(){
    ConnectorSetup();

    $connectorOptions = array(
        'roots' => array(
            //Personal dropbox volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'alias'        => "My Dropbox",                    // display this instead of real root name
                'path' => AttachOrCreateDropbox(),                 // path to files (REQUIRED)
                'URL'  => '/files/Dropboxes/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
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
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
            ),
            //Project volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Projects',               // path to files (REQUIRED)
                'URL'  => '/files/Projects/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
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
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            array(
                //Corporate volume
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Corporate',                 // path to files (REQUIRED)
                'URL'  => '/files/Corporate/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.trash/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
                'uploadOrder'   => array('deny', 'allow'),      // Same as above
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
                'URL'  => '/files/Dropboxes/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
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
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access',                // disable and hide dot starting files (OPTIONAL)
            ),
            //Project volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Projects',               // path to files (REQUIRED)
                'URL'  => '/files/Projects/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
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
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.trash/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
                'uploadOrder'   => array('deny', 'allow'),      // Same as above
                'accessControl' => 'access',                    // Same as above
            )
        )

    );

    return $connectorOptions;
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
?>