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
</head>
<body>
    derp.
</body>
</html>