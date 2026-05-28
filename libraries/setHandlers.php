<?php

   //Sends the user back to the login page if there is no session.
   if(!isset($_SESSION['username'])){
     header("location: login.php");
     exit;
   }
   if (isset($_GET['action']) && $_GET['action'] === 'switch_role') {
      adminViewToggle();
      header("Location: index.php");
      exit;
   }

// ——— Impersonation ———
if (isset($_GET['action']) && $_GET['action'] === 'impersonate' && isset($_GET['artist']) && GetRole() === 'admin') {
    $artistData = pull_artistAdmin_data($_GET['artist']);
    if ($artistData) {
        ImpersonateArtist($artistData);
    }
    header("Location: index.php");
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'stop_impersonating') {
    StopImpersonating();
    // Reset to admin dashboard so we don't try loading artist modules as admin
    SetActiveModule('Dashboard');
    header("Location: index.php");
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'impersonate_client' && isset($_GET['client']) && GetRole() === 'admin') {
    $clientData = pull_client_data($_GET['client']);
    if ($clientData) {
        ImpersonateClient($clientData);
    }
    header("Location: index.php");
    exit;
}




   if(isset($_GET['action']) && $_GET['action'] === 'logout'){
      logout();
      header("Location: login.php");
      exit;
   }
if(isset($_GET['action']) && $_GET['action'] === 'set_theme' && isset($_GET['theme']) && !IsImpersonating()){
    $theme = preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['theme']);
    $themesDir = __ROOT__ . '/css/siteThemes';
    if(file_exists($themesDir . '/' . $theme . '.css')){
        SetUserTheme($_SESSION['username'], $theme, $_SESSION['role']);
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['module'])) {
    $moduleName = $_GET['module'];
    // Set the active module in session
    $_SESSION['ActiveModule'] = $_SESSION['tempRole'] . $moduleName;
    
    // Set the active module path and redirect to refresh page with new content
    $_SESSION['ActiveModulePath'] = __ROOT__ . '/modules/' . $_SESSION['tempRole'] . '/' . $_SESSION['ActiveModule'] . '/module.php';
    
    // Redirect to refresh the page with new content
    header("Location: index.php");
    exit;
} else {
    // Initialize default module if none is set
    if (!isset($_SESSION['ActiveModule']) || empty($_SESSION['ActiveModule'])) {
        SetActiveModule('Dashboard');
    }
}
if(isset($_GET['action']) && $_GET['action'] === 'set_timezone' && isset($_GET['timezone']) && !IsImpersonating()){
    $tz = preg_replace('/[^a-zA-Z0-9_\/\-+]/', '', $_GET['timezone']);
    $valid = in_array($tz, DateTimeZone::listIdentifiers(), true);
    if($valid){
        SetUserTimezone($_SESSION['username'], $tz, $_SESSION['role']);
    }
    header("Location: index.php");
    exit;
}
