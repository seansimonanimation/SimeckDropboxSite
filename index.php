<!DOCTYPE html>
<?php
   session_start();
   include_once('login.conf.php');

   if(!isset($_SESSION['login_user'])){
     header("location: index.php");
   }

   //get user data from $_SESSION.
   //$username = $_SESSION['login_user'];



   //Pull the live user data from the db each time we load the page. This way if the user changes their name, it will be reflected here without them having to log out and back in again.



   $ses_sql = mysqli_query($db,"select name from mailbox where name = '$user_check' ");
   $row = mysqli_fetch_array($ses_sql, MYSQLI_ASSOC);
   $login_session = $row['name'];

   if (!isset($_SESSION['login_user'])) {
      header("location:login.php");
   }
?>
<html lang="en">
<head>
</head>
<body>
    derp.
</body>
</html>