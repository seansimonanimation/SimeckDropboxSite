<?php
    Define('__ELFINDER_ROOT__','libraries/elfinder/');



function getAdminFileBrowserOptions(){
    ConnectorSetup();

    $connectorOptions = array(
        'roots' => array(
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
            //Dropbox volume
            array(
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path' => __ROOT__ . '/files/Dropboxes',                 // path to files (REQUIRED)
                'URL'  => '/files/Dropboxes/', // URL to files (REQUIRED)
                'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
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
            ),
        )
    );

    return $connectorOptions;
}


function displayArtistFileBrowser(){

}

function displayClientFileBrowser(){

}




function initializeConnector($connectorOptions){
        $connector = new elFinderConnector(new elFinder($connectorOptions));
        $connector->run();
}

function ConnectorSetup(){
    require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';

}
?>