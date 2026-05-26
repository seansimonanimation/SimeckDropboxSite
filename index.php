<?php
ob_start();
   //Necessary to begin the session.

   include_once __DIR__ . '/libraries/session.php'; //One of only 2 __DIR__s in the entire codebase, since session.php needs to be included before we can use __ROOT__.
   include_once __ROOT__ . '/libraries/auth.php';
   include_once __ROOT__ . '/libraries/topBarPhrases.php';
   include_once __ROOT__ . '/libraries/moduleLoader.php';

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


function adminViewToggle(){
    if(GetRole() == 'admin' && GetTempRole() == 'admin'){
        $_SESSION['tempRole'] = 'artist';
    } else if(GetRole() == 'admin' && GetTempRole() == 'artist'){
        $_SESSION['tempRole'] = 'admin';
    }
   $moduleName = $_SESSION['CurrentModuleName'] ?? 'Dashboard'; // Default to Dashboard if not set
    
    try {
        SetActiveModule($moduleName);
    } catch (Exception $e) {
        // Handle the case where ActiveModule is not set or invalid
        SetActiveModule('Dashboard'); // Default to Dashboard if there's an issue
    }
}
function adminSwitchViewButtonActivation(){
    if(GetRole() == 'admin' && !IsImpersonating()){
        return '<a href="index.php?action=switch_role">Switch Role</a>';
    } else {
        return '';
    }
}

   ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="stylesheet" href="css/portal.css">
   <link rel="stylesheet" href="css/moduleStyle.css">
   <?php
      $currentTheme = $_SESSION['theme'] ?? 'dark-boo';
      $themeFile = 'css/siteThemes/' . $currentTheme . '.css';
      if(file_exists(__ROOT__ . '/' . $themeFile)){
         echo '<link rel="stylesheet" href="' . $themeFile . '?v=' . filemtime(__ROOT__ . '/' . $themeFile) . '">';
      } else {
         // Fallback to Dark Boo
         echo '<link rel="stylesheet" href="css/siteThemes/dark-boo.css">';
      }
?>
   <title>Simeck Entertainment <?php echo GetTempRole(); ?> Portal<br /></title>
</head>
<body class="portal-layout <?php echo GetThemeClass(); ?>">

   <aside id="sidebar" role="navigation">
      <div class="sidebar-header"><?php echo $_SESSION['tempRole']; ?> portal
   <br /> <?php echo adminSwitchViewButtonActivation(); ?></div>
   <nav>
   <!-- Sidebar content goes here -->
    <?php echo LoadNavbarContent(); ?>
</nav>

     <div class="sidebar-footer">
         <?php if(IsImpersonating()): ?>
             <a href="index.php?action=stop_impersonating" class="stop-impersonate-btn">← Stop Impersonating</a>
         <?php elseif(GetRole() === 'admin' && GetTempRole() === 'admin'): ?>
             <form method="GET" action="index.php" class="impersonate-form">
                 <input type="hidden" name="action" value="impersonate_client" />
                 <label class="sidebar-label">Impersonate Client</label>
                 <select name="client" class="impersonate-select" onchange="this.form.submit()">
                     <option value="">— Select —</option>
                     <?php
                         $clients = ListAllActiveClients();
                         foreach($clients as $c){
                             echo '<option value="' . htmlspecialchars($c['email']) . '">'
                                  . htmlspecialchars($c['firstname'] . ' ' . $c['lastname'])
                                  . '</option>';
                         }
                     ?>
                 </select>
             </form>
             <form method="GET" action="index.php" class="impersonate-form">
                 <input type="hidden" name="action" value="impersonate" />
                 <label class="sidebar-label">Impersonate Artist</label>
                 <select name="artist" class="impersonate-select" onchange="this.form.submit()">
                     <option value="">— Select —</option>
                     <?php
                         $artists = ListAllActiveArtists();
                         foreach($artists as $a){
                             echo '<option value="' . htmlspecialchars($a['username']) . '">'
                                  . htmlspecialchars($a['firstname'] . ' ' . $a['lastname'])
                                  . '</option>';
                         }
                     ?>
                 </select>
             </form>
         <?php endif; ?>
         <a href="index.php?action=logout">Logout</a>
     </div>


   </aside>
   <header id="topbar" role="banner">
<div class="topbar-title">
    <?php if(IsImpersonating()): ?>
        <span class="read-only-badge">❄️❄️READ ONLY MODE ENGAGED❄️❄️</span>
    <?php endif; ?>
    Hi, <?php echo GetHumanName('first'); ?>!
</div>
<div class="topbar-right"><?php echo DisplayRandomTopbarPhrase(); ?></div>
</header>
<main id="content">
   <?php echo DisplayActiveModuleContent(); ?>
</main>
   </body>
</html>