
<?php
//This file dynamically loads modules from the modules directory.
//It is included in the index.php file, and it looks for all 
// files in the modules subdirectories that are named module.php and 
// includes them.

    include_once __ROOT__ . '/libraries/session.php';
function RegisterModules(){


}

function LoadNavbarContent(){
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
                $moduleInfo['nav-icon'] = $metadata['nav-icon'] ?? '';
                $moduleInfo['nav-order'] = $metadata['nav-order'] ?? 0;
                $moduleInfo['path'] = $file->getPathname();
                
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
        $html .= '<a href="' . $module['nav-icon'] . '">' . $module['name'] . '</a>';
    }
    return $html;
}

?>





