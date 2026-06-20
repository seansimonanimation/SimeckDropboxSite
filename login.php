<?php
//
// login.php - Login page for Simeck Entertainment's Dropbox
// This page is a shared login page for all roles.
//
// Auth sequence:
// 1) Try artist/admin against simeckdb.artists table (sha512-crypt)
// 2) On failure, assume client and try against simeckdb.clients table (sha512-crypt)
// 3) On client table failure, redirect back to login.php with error message
// 4) On success, set session and redirect to index.php
//

    include_once __DIR__ . '/libraries/session.php';
    include_once __ROOT__ . '/libraries/auth.php';
    include_once __ROOT__ . '/libraries/moduleLoader.php'; //Needed to set up the session and determine the first module to load. Hint: It's the dashboard module for the user's role.
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        execute_login();
    }


function execute_login(){

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        attempt_login($_POST['username'], $_POST['password']);
    }
    if(isset($_SESSION['username'])){
        if(isset($_SESSION['login_redirect'])){
            if($_SESSION['login_redirect'] === '/login.php' || $_SESSION['login_redirect'] === 'login.php'){unset($_SESSION['login_redirect']);}
            $redirect = $_SESSION['login_redirect'];
            unset($_SESSION['login_redirect']);
            header("location: $redirect");
            exit;
        }
        SetActiveModule('Dashboard');
        header("location: index.php");
        exit;
    } else {
            header("location: login.php?error=Invalid%20username%20or%20password");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/login.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Simeck Entertainment Dropbox</title>
</head>
<body>
       <img class="bg-logo"
        src="globalSiteAssets/simeck-logo.png"
        alt="" aria-hidden="true" />

    <div class="site-header">
      <div class="logo-mark">
         <img src="globalSiteAssets/simeck-small-icon.png"
              alt="Simeck Entertainment" />
      </div>
      <div class="site-title">
         Simeck Entertainment
         <small>Dropbox</small>
      </div>
    </div>
    <div class="card">

      <div class="card-header">
         <h1>Welcome back!</h1>
         <p>Please enter your username or email and password to begin.</p>
      </div>

      <div class="divider"></div>

    <form method="POST" action="">
        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <label for="username">Username or email</label>
        <center><input type="username" id="usernameBox" name="username" required></center>
        <label for="password">Password</label>
        <center><input type="password" id="passwordBox" name="password" required></center>
        <br /><center><button type="submit" class = "loginButton" method="POST" >Log in</button><br />
        <?php
        if(isset($_GET['error'])){
            echo "Username and password error.";
        }
        ?>
    </center>
    </form>
</body>
</html>