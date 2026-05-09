<!DOCTYPE html>
<?php

   //Necessary to begin the session.

   include_once __DIR__ . '/libraries/session.php'; //One of only 2 __DIR__s in the entire codebase, since session.php needs to be included before we can use __ROOT__.
   include_once __ROOT__ . '/libraries/auth.php';
   include_once __ROOT__ . '/libraries/topBarPhrases.php';
   include_once __ROOT__ . '/libraries/moduleLoader.php';

   //Sends the user back to the login page if there is no session.
   if(!isset($_SESSION['username'])){
     header("location: login.php");
   }
   if (isset($_GET['action']) && $_GET['action'] === 'switch_role') {
      adminViewToggle();
      header("Location: index.php");
      exit;
   }
   if(isset($_GET['action']) && $_GET['action'] === 'logout'){
      logout();
      header("Location: login.php");
      exit;
   }

if (isset($_GET['module'])) {
    $moduleName = $_GET['module'];
    // Set the active module in session
    $_SESSION['ActiveModule'] = $_SESSION['tempRole'] . $moduleName;
}


   function adminViewToggle(){
      if(GetRole() == 'admin' && GetTempRole() == 'admin'){
         $_SESSION['tempRole'] = 'artist';
      } else if(GetRole() == 'admin' && GetTempRole() == 'artist'){
         $_SESSION['tempRole'] = 'admin';
      }
      try {
         SetActiveModule($_SESSION['ActiveModule']);
      } catch (Exception $e) {
         // Handle the case where ActiveModule is not set or invalid
         SetActiveModule('Dashboard'); // Default to Dashboard if there's an issue
      }
   }
   function adminSwitchViewButtonActivation(){
      if(GetRole() == 'admin'){
         return '<a href="index.php?action=switch_role">Switch Role</a>';
      } else {
         return '';
      }
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="stylesheet" href="css/portal.css">
   <title>Simeck Entertainment <?php echo GetTempRole(); ?> Portal<br /></title>
</head>
<body class="portal-layout">
   <aside id="sidebar" role="navigation">
      <div class="sidebar-header"><?php echo $_SESSION['tempRole']; ?> portal
   <br /> <?php echo adminSwitchViewButtonActivation(); ?></div>
   <nav>
   <!-- Sidebar content goes here -->
    <?php echo LoadNavbarContent(); ?>
</nav>
    <div class="sidebar-footer"><a href="index.php?action=logout">Logout</a></div>
   </aside>
   <header id="topbar" role="banner">
<div class="topbar-title"> Hi, <?php echo GetHumanName('first'); ?>!</div>
<div class="topbar-right"><?php echo DisplayRandomTopbarPhrase(); ?></div>
</header>
<main id="content">
   <?php echo DisplayActiveModuleContent(); ?>
</main>
   </body>
</html>