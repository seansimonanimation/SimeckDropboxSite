<?php
    Define('__ELFINDER_ROOT__','libraries/elfinder/');



function getAdminFileBrowserOptions(){
    ConnectorSetup();

    $connectorOptions = array(
        'roots' => array(
            array(
                //Corporate volume
                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                'path'          => 'files/Corporate',                 // path to files (REQUIRED)
                'URL'           => dirname($_SERVER['PHP_SELF']) . 'files/Corporate/', // URL to files (REQUIRED)
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
                'path'          => 'files/Dropboxes',                 // path to files (REQUIRED)
                'URL'           => dirname($_SERVER['PHP_SELF']) . 'files/Dropboxes/', // URL to files (REQUIRED)
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
                'path'          => 'files/Projects',                 // path to files (REQUIRED)
                'URL'           => dirname($_SERVER['PHP_SELF']) . 'files/Project/', // URL to files (REQUIRED)
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
                'path'          => 'files/.trash/',
                'tmbURL'        => dirname($_SERVER['PHP_SELF']) . 'files/.trash/.tmb/',
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
    require __ELFINDER_ROOT__ . 'php/autoload.php';
}
?>