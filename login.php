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
         <img src="https://files.simeck.com/simeck-small-icon.png"
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
         <p>Select your name and sign in with<br>your simeck password.</p>
      </div>

      <div class="divider"></div>

    <form method="POST" action="">
        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <label for="username">Username or Email:</label><br>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>