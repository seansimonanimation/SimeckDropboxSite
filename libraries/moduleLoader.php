<?php
//This file dynamically loads modules from the modules directory.
//It is included in the index.php file, and it looks for all 
// files in the modules subdirectories that are named module.php and 
// includes them.

function LoadNavbarContent(){

    //Secondary roles to look for: marketing, programmer, butters
    $moduleDir = '';
    if($_SESSION['tempRole'] == 'admin'){
        $moduleDir = __ROOT__ . '/modules/admin';
    } else if($_SESSION['tempRole'] == 'artist'){
        $moduleDir = __ROOT__ . '/modules/artist';
    } else if($_SESSION['tempRole'] == 'client'){
        $moduleDir = __ROOT__ . '/modules/client';
    }
    
    $moduleFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($moduleDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $activeModules = array();

    try {
        foreach ($moduleFiles as $file) {
            if ($file->isFile() && $file->getFilename() === 'module.php') {
                // Read the file content to parse the metadata comments
                $fileContent = file_get_contents($file->getPathname());
                
                // Define patterns for each metadata type
                $metadata = array();
                
                // Match @module
                if (preg_match('/@module\s+(.+)/', $fileContent, $matches)) {
                    $metadata['module'] = trim($matches[1]);
                }
                
                // Match @name  
                if (preg_match('/@name\s+(.+)/', $fileContent, $matches)) {
                    $metadata['name'] = trim($matches[1]);
                }
                
                // Match @role
                if (preg_match('/@role\s+(.+)/', $fileContent, $matches)) {
                    $metadata['role'] = trim($matches[1]);
                }
                // Match @secondary-role
                
                if (preg_match('/@secondary-role\s+(.+)/', $fileContent, $matches)) {
                    $metadata['secondary-role'] = trim($matches[1]);
                    $userSecondaryRoles = array_map('trim', explode(',', $_SESSION['secondary-roles'] ?? ''));
                    if (!in_array($metadata['secondary-role'], $userSecondaryRoles)) {
                        continue;
                    }
                }
                //echo $_SESSION['secondary-roles'];
                //Secondary role checks.
                //If the secondary role isn't included in the secondary roles in the session, skip past because we're not loading the module.

                // Match @nav-text
                if (preg_match('/@nav-text\s+(.+)/', $fileContent, $matches)) {
                    $metadata['nav-text'] = trim($matches[1]);
                }
                
                // Match @nav-icon
                if (preg_match('/@nav-icon\s+(.+)/', $fileContent, $matches)) {
                    $metadata['nav-icon'] = trim($matches[1]);
                }
                
                // Match @nav-order
                if (preg_match('/@nav-order\s+(.+)/', $fileContent, $matches)) {
                    $metadata['nav-order'] = intval(trim($matches[1]));
                }
                
                // Create module info array
                $moduleInfo = array();
                $moduleInfo['name'] = $metadata['name'] ?? 'Unnamed Module';
                $moduleInfo['module'] = $metadata['module'] ?? '';
                $moduleInfo['role'] = $metadata['role'] ?? '';
                if(isset($metadata['secondary-role'])){$moduleInfo['secondary-role'] = $metadata['secondary-role'];}
                $moduleInfo['nav-text'] = $metadata['nav-text'] ?? '';
                $moduleInfo['nav-icon'] = $metadata['nav-icon'] ?? '';
                $moduleInfo['nav-order'] = $metadata['nav-order'] ?? 0;
                $moduleInfo['path'] = $file->getPathname();
                

                //Set the session module data
                SetCurrentModuleSessionData($moduleInfo);



                // Only include modules that match the current role
                if (empty($metadata['role']) || $metadata['role'] == $_SESSION['tempRole']) {
                    $activeModules[] = $moduleInfo;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error loading modules: " . $e->getMessage());
    }
    
    // Sort modules by nav-order
    usort($activeModules, function($a, $b) {
        return $a['nav-order'] <=> $b['nav-order'];
    });
    
    return SidebarHTMLGenerator($activeModules);
}


function SidebarHTMLGenerator($activeModuleArray){
    $html = '';
    foreach($activeModuleArray as $module){
        $html .= '<a href="index.php?module=' . urlencode($module['name']) . '">' . $module['nav-text'] . '</a>';

    }
    return $html;
}

function SetActiveModule($moduleName){
    // Make sure we don't double-prefix the module name
    $role = $_SESSION['tempRole'];
    
    // If the module name already starts with the current role, don't prefix it again
    if (strpos($moduleName, $role) === 0) {
        $_SESSION['ActiveModule'] = $moduleName;
    } else {
        $_SESSION['ActiveModule'] = $role . $moduleName;
    }
    
    $_SESSION['ActiveModulePath'] = __ROOT__ . '/modules/' . $_SESSION['tempRole'] . '/' . $_SESSION['ActiveModule'] . '/module.php';
    header("Location: index.php");
}


function DisplayActiveModuleContent(){
    if(isset($_SESSION['ActiveModulePath'])){
        ob_start();
        include $_SESSION["ActiveModulePath"];
        return ob_get_clean();
    } else {
        return '';
    }

}

function SetCurrentModuleSessionData($moduleData){
    $_SESSION['CurrentModuleModule'] = $moduleData['module'];
    $_SESSION['CurrentModulePath'] = $moduleData['path'];
    $_SESSION['CurrentModuleRole'] = $moduleData['role'];
    $_SESSION['CurrentModuleNavText'] = $moduleData['nav-text'];
    $_SESSION['CurrentModuleNavIcon'] = $moduleData['nav-icon'];
    $_SESSION['CurrentModuleNavOrder'] = $moduleData['nav-order'];
}

/**
 * Scan css/siteThemes/ for theme files and return an array of theme metadata.
 * Each CSS file should have a comment like /* @name Theme Name *​/ at the top.
 * Falls back to humanizing the filename if no @name is found.
 */
function DiscoverThemes(){
    $themesDir = __ROOT__ . '/css/siteThemes';
    $themes = [];
    if(!is_dir($themesDir)){
        // Fallback: at least Dark Boo exists as default
        return ['dark-boo' => ['id' => 'dark-boo', 'name' => 'Dark Boo']];
    }
    $files = glob($themesDir . '/*.css');
    sort($files);
    foreach($files as $file){
        $filename = basename($file, '.css');
        $id = $filename;
        $name = ucwords(str_replace(['-', '_'], ' ', $filename)); // fallback
        // Try to extract @name from file comments
        $content = file_get_contents($file);
        if(preg_match('/@name\s+(.+)/', $content, $matches)){
            $name = trim($matches[1]);
        }
        $themes[$id] = [
            'id' => $id,
            'name' => $name,
            'file' => 'css/siteThemes/' . $filename . '.css'
        ];
    }
    return $themes;
}

/**
 * Get the current user's theme class for the <body> tag.
 */
function GetThemeClass(){
    return 'theme-' . ($_SESSION['theme'] ?? 'dark-boo');
}
