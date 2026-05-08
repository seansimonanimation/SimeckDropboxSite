<!DOCTYPE html>
<?php
   //Necessary to begin the session.
   include_once __DIR__ . '/libraries/session.php';

   //Sends the user back to the login page if there is no session.
   if(!isset($_SESSION['username'])){
     header("location: login.php");
   }



?>
<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="stylesheet" href="css/portal.css">
   <title>Simeck Entertainment <?php echo GetRole(); ?> Portal</title>
</head>
<body class="portal-layout">
   <aside id="sidebar" role="navigation">
      <div class="sidebar-header"><?php echo $_SESSION['role']; ?> portal</div>
   <nav>
</nav>
    derps.
</body>
</html>