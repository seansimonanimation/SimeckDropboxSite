<?php
ob_start();
   //Necessary to begin the session.

   include_once __DIR__ . '/libraries/session.php'; //One of only 2 __DIR__s in the entire codebase, since session.php needs to be included before we can use __ROOT__.
   include_once __ROOT__ . '/libraries/auth.php';
   include_once __ROOT__ . '/libraries/topBarPhrases.php';
   include_once __ROOT__ . '/libraries/moduleLoader.php';
   include_once __ROOT__ . '/libraries/setHandlers.php';
   require_once __ROOT__ . '/vendor/autoload.php';
   require_once __ROOT__ . '/libraries/logging.php';

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
<body class="portal-layout <?php echo GetThemeClass(); ?> <?php echo htmlspecialchars($_SESSION['ActiveModule'] ?? ''); ?>">

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
                             echo '<option value="' . htmlspecialchars($c['username']) . '">'
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
                                 . htmlspecialchars(GetArtistNicknameAndLegalName($a))
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
            <?php if(IsImpersonating()){
                echo '<span class="read-only-badge">❄️❄️READ ONLY MODE ENGAGED!!! Impersonating: ' . GetHumanName('greeting') . ' (' . GetTempRole() . ')❄️❄️</span>';
            } else {
                echo 'Hi, ' . GetHumanName('greeting') . '!';

            }
             ?>
             </div>
             <div class="topbar-phrase">
</div>
<div class="topbar-right"><?php echo DisplayRandomTopbarPhrase(); ?></div>
</header>
<main id="content">
   <?php echo DisplayActiveModuleContent(); ?>
</main>
   </body>
</html>