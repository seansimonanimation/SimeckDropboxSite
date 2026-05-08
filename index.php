<!DOCTYPE html>
<?php
   //Necessary to begin the session.
   include_once __DIR__ . '/libraries/session.php';
   include_once __DIR__ . '/libraries/auth.php';
   include_once __DIR__ . '/libraries/topBarPhrases.php';

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


   function adminViewToggle(){
      if(GetRole() == 'admin' && GetTempRole() == 'admin'){
         $_SESSION['tempRole'] = 'artist';
      } else if(GetRole() == 'admin' && GetTempRole() == 'artist'){
         $_SESSION['tempRole'] = 'admin';
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


</nav>
    <div class="sidebar-footer"><a href="index.php?action=logout">Logout</a></div>
   </aside>
   <header id="topbar" role="banner">
<div class="topbar-title"> Hi, <?php echo GetHumanName('first'); ?>!</div>
<div class="topbar-right"><?php echo DisplayRandomTopbarPhrase(); ?></div>
</header>



   </body>
</html>