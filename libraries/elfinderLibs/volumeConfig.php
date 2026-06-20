<?php
/**
 * Volume configuration for each role's elFinder file browser.
 * Contains the options arrays that define which folders each role sees.
 */

function getAdminFileBrowserOptions(){
    ConnectorSetup();

    $connectorOptions = array(
        'roots' => array(
            //Personal dropbox volume
            array(
                'driver'        => 'SimeckVolume',
                'alias'        => "My Dropbox",
                'path' => AttachOrCreateDropbox(),
                'URL'  => DetermineMyDropboxURL(),
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Everyone's Dropboxes volume
            array(
                'driver'        => 'SimeckVolume',
                'alias'        => "Everyone's Dropboxes",
                'path' => __ROOT__ . '/files/Dropboxes',
                'URL'  => '/files/Dropboxes/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Project volume
            array(
                'driver'        => 'SimeckVolume',
                'path' => __ROOT__ . '/files/Projects',
                'URL'  => '/files/Projects/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Resources volume
            array(
                'driver'        => 'SimeckVolume',
                'alias'        => "Studio Resources",
                'path' => __ROOT__ . '/files/Resources',
                'URL'  => '/files/Resources/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            array(
                //Corporate volume
                'driver'        => 'SimeckVolume',
                'path' => __ROOT__ . '/files/Corporate',
                'URL'  => '/files/Corporate/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmbPath' => __ROOT__ . '/files/.tmb',

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
                'driver'        => 'SimeckVolume',
                'alias'        => "My Dropbox",
                'path' => AttachOrCreateDropbox(),
                'URL'  => DetermineMyDropboxURL(),
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'dotFiles' => false,
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Everyone's Dropboxes volume
            array(
                'driver'        => 'SimeckVolume',
                'alias'        => "Everyone's Dropboxes",
                'path' => __ROOT__ . '/files/Dropboxes',
                'URL'  => '/files/Dropboxes/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'dotFiles' => false,
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Project volume
            array(
                'driver'        => 'SimeckVolume',
                'path' => __ROOT__ . '/files/Projects',
                'URL'  => '/files/Projects/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'dotFiles' => false,
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            //Resources volume
            array(
                'driver'        => 'SimeckVolume',
                'alias'        => "Studio Resources",
                'path' => __ROOT__ . '/files/Resources',
                'URL'  => '/files/Resources/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'dotFiles' => false,
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/files/.tmb/',
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/files/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'dotFiles' => false,
                'tmbPath' => __ROOT__ . '/files/.tmb',

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
        $clientUploadPath = __ROOT__ . $project['active_path'] . '/clientUpload/';
        if(!is_dir($ProjectPath)){
            mkdir($ProjectPath, 0777, true);
        }

        if (!is_dir($clientUploadPath)) {
            mkdir($clientUploadPath, 0777, true);
            @mkdir($clientUploadPath . '.tmb', 0777, true);
        }
        $roots[] = array(
            'driver'        => 'SimeckVolume',
            'alias'        => $project['project_name'],
            'path' => __ROOT__ . rtrim($project['active_path'], '/') . '/clientUpload/',
            'URL'  => rtrim($project['active_path'], '/') . '/clientUpload/',
            'trashHash'     => 't1_Lw',
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
            'accessControl' => 'access',
            'dotFiles' => false,
            'tmbPath' => __ROOT__ . '/files/.tmb',
            'tmbURL' => '/files/.tmb/',
            'clientMode' => true,
        );
    }

    return array('roots' => $roots);
}

function GetClientProjectAssignments(){
    $clientassignments = [];
    $clientid = $_SESSION['username'];
    $query = "SELECT project_assignments FROM clients WHERE username = ?";
    $stmt = $GLOBALS['db']->prepare($query);
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (empty($result['project_assignments'])) {
        return [];
    }
    return explode(',', $result['project_assignments']);
}
