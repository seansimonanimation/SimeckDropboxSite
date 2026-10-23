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
                'tmpLink'            => true,   // Enable instant merging
                'uploadTempPath'     => __ROOT__ . '/files/Dropboxes/.tmp/',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
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
                'tmpLink'            => true,   // Enable instant merging
                'uploadTempPath'     => __ROOT__ . '/files/Dropboxes/.tmp/',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
            ),
            //Project volume
            array(
                'driver'        => 'SimeckVolume',
                'path' => __ROOT__ . '/files/Projects',
                'URL'  => '/files/Projects/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmpLink'            => true,   // Enable instant merging
                'uploadTempPath'     => __ROOT__ . '/files/Projects/.tmp/',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
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
                'tmpLink'            => true,   // Enable instant merging
                'uploadTempPath'     => __ROOT__ . '/files/Resources/.tmp/',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
            ),
            array(
                //Corporate volume
                'driver'        => 'SimeckVolume',
                'path' => __ROOT__ . '/files/Corporate',
                'URL'  => '/files/Corporate/',
                'trashHash'     => 't1_Lw',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                'accessControl' => 'access',
                'tmpLink'            => true,   // Enable instant merging
                'uploadTempPath'     => __ROOT__ . '/files/Corporate/.tmp/',
                'tmbPath' => __ROOT__ . '/files/.tmb',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
            ),
            // Trash volume
            array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'   => __ROOT__ . '/files/.trash/',
                'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
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
    $roots = array();

    //Personal Dropbox volume
    $roots[] = array(
        'driver'        => 'SimeckVolume',
        'alias'        => "My Dropbox",
        'path' => AttachOrCreateDropbox(),
        'URL'  => DetermineMyDropboxURL(),
        'trashHash'     => 't1_Lw',
        'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
        'accessControl' => 'access',
        'dotFiles' => false,
        'tmpLink'            => true,
        'uploadTempPath'     => __ROOT__ . '/files/Dropboxes/.tmp/',
        'tmbPath' => __ROOT__ . '/files/.tmb',
        'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
    );

    //Everyone's Dropboxes volume
    $roots[] = array(
        'driver'        => 'SimeckVolume',
        'alias'        => "Everyone's Dropboxes",
        'path' => __ROOT__ . '/files/Dropboxes',
        'URL'  => '/files/Dropboxes/',
        'trashHash'     => 't1_Lw',
        'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
        'accessControl' => 'access',
        'dotFiles' => false,
        'tmpLink'            => true,
        'uploadTempPath'     => __ROOT__ . '/files/Dropboxes/.tmp/',
        'tmbPath' => __ROOT__ . '/files/.tmb',
        'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
    );

    //Project volumes — filtered by security level and artist assignment
    $artistAssignments = GetArtistProjectAssignments();
    $projectQuery = "SELECT pid, project_name, active_path, security_level FROM projects WHERE active = 1 AND transitioning = 0";
    $stmt = $GLOBALS['db']->prepare($projectQuery);
    $stmt->execute();
    $allProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allProjects as $project) {
        $securityLevel = $project['security_level'] ?? 'standard';
        $isAssigned = in_array($project['pid'], $artistAssignments, true);

        // Restricted projects: only show if artist is assigned
        if ($securityLevel === 'restricted' && !$isAssigned) {
            continue;
        }

        $projectPath = __ROOT__ . rtrim($project['active_path'], '/');
        if (!is_dir($projectPath)) {
            mkdir($projectPath, 0777, true);
        }

        $roots[] = array(
            'driver'        => 'SimeckVolume',
            'alias'        => $project['project_name'],
            'path' => $projectPath,
            'URL'  => rtrim($project['active_path'], '/') . '/',
            'trashHash'     => 't1_Lw',
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
            'accessControl' => 'access',
            'dotFiles' => false,
            'tmpLink'            => true,
            'uploadTempPath'     => __ROOT__ . '/files/Projects/.tmp/',
            'tmbPath' => __ROOT__ . '/files/.tmb',
            'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
        );
    }

    // Persistent_Projects volume (static shared projects folder — last project volume)
    $roots[] = array(
        'driver'        => 'SimeckVolume',
        'alias'        => "Persistent Projects",
        'path' => __ROOT__ . '/files/Projects/Persistent_Projects',
        'URL'  => '/files/Projects/Persistent_Projects/',
        'trashHash'     => 't1_Lw',
        'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
        'accessControl' => 'access',
        'dotFiles' => false,
        'tmpLink'            => true,
        'uploadTempPath'     => __ROOT__ . '/files/Projects/Persistent_Projects/.tmp/',
        'tmbPath' => __ROOT__ . '/files/.tmb',
        'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
    );

    //Resources volume
    $roots[] = array(
        'driver'        => 'SimeckVolume',
        'alias'        => "Studio Resources",
        'path' => __ROOT__ . '/files/Resources',
        'URL'  => '/files/Resources/',
        'trashHash'     => 't1_Lw',
        'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
        'accessControl' => 'access',
        'dotFiles' => false,
        'tmpLink'            => true,
        'uploadTempPath'     => __ROOT__ . '/files/Resources/.tmp/',
        'tmbPath' => __ROOT__ . '/files/.tmb',
        'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
    );

    // Trash volume
    $roots[] = array(
        'id'            => '1',
        'driver'        => 'Trash',
        'path'   => __ROOT__ . '/files/.trash/',
        'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
        'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
        'accessControl' => 'access',
        'dotFiles' => false,
        'tmbPath' => __ROOT__ . '/files/.tmb',
    );

    return array('roots' => $roots);
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
            'tmpLink'            => true,   // Enable instant merging
            'uploadTempPath'     => __ROOT__ . '/files/Projects/.tmp/',
            'tmbPath' => __ROOT__ . '/files/.tmb',
            'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
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

function GetArtistProjectAssignments(){
    $assignments = [];
    $query = "SELECT project_assignments FROM artists WHERE username = ?";
    $stmt = $GLOBALS['db']->prepare($query);
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($result['project_assignments'])) {
        return [];
    }
    return array_filter(array_map('trim', explode(',', $result['project_assignments'])));
}

function getVendorFileBrowserOptions(){
    ConnectorSetup();
    $vendorAssignments = GetVendorProjectAssignments();
    $roots = array();
    if(empty($vendorAssignments)){
        return $roots;
    }
    
    $placeholders = implode(',', array_fill(0, count($vendorAssignments), '?'));
    $projectQuery = "SELECT pid, project_name, active_path FROM projects WHERE pid IN ($placeholders)";

    $stmt = $GLOBALS['db']->prepare($projectQuery);
    $stmt->execute($vendorAssignments);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $connectorOptions = array('roots' => array());

    foreach ($projects as $project){
        $ProjectPath = __ROOT__ . $project['active_path'];
        $vendorUploadPath = __ROOT__ . $project['active_path'] . '/vendorUpload/';
        if(!is_dir($ProjectPath)){
            mkdir($ProjectPath, 0777, true);
        }

        if (!is_dir($vendorUploadPath)) {
            mkdir($vendorUploadPath, 0777, true);
            @mkdir($vendorUploadPath . '.tmb', 0777, true);
        }
        $roots[] = array(
            'driver'        => 'SimeckVolume',
            'alias'        => $project['project_name'],
            'path' => __ROOT__ . rtrim($project['active_path'], '/') . '/vendorUpload/',
            'URL'  => rtrim($project['active_path'], '/') . '/vendorUpload/',
            'trashHash'     => 't1_Lw',
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
            'accessControl' => 'access',
            'dotFiles' => false,
            'tmbPath' => __ROOT__ . '/files/.tmb',
            'tmbURL' => '/libraries/elfinderLibs/endpoints/serveTmb.php?file=',
            'vendorMode' => true,
        );
    }

    return array('roots' => $roots);
}

function GetVendorProjectAssignments(){
    $vendorassignments = [];
    $query = "SELECT project_assignments FROM vendors WHERE username = ?";
    $stmt = $GLOBALS['db']->prepare($query);
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (empty($result['project_assignments'])) {
        return [];
    }
    return explode(',', $result['project_assignments']);
}
