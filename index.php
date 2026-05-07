<!DOCTYPE html>
<?php
   include('login.conf.php');
   session_start();

   $user_check = $_SESSION['login_user'];

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