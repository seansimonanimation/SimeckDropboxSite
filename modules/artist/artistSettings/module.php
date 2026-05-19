<?php
//The module responsible for dashboard content on the artist portal. 
// yep

/**
 * @module artistSettings
 * @name Settings
 * @role artist
 * @nav-text Artist Settings
 * @nav-icon settings
 * @nav-order 99
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';
include_once __ROOT__ . '/libraries/settingslib.php';

global $errorMessage;
$errorMessage = '';
$successMessage = '';
if(isset($_GET['pw_changed'])){
    $successMessage = 'Password changed successfully.';
}

if(isset($_POST['ArtistChangePW'])){
    $username = $_SESSION['username'];
    $currentPW = $_POST['currentPW'];
    $confirmPW = $_POST['ConfirmNewPW'];
    $newPW = $_POST['newPW'];
    $artistData = pull_artistAdmin_data($_SESSION['username']);
    verifyConfirmation($newPW,$confirmPW);
    if($errorMessage === ''){ verifyCurrentPW($currentPW, $artistData); }



if($errorMessage === ''){
    if(SetArtistPassword($username, $newPW)){
        header("Location: ?pw_changed=1");
        exit;
    } else {
        $errorMessage = 'Database error. Password was not changed.';
    }
}

}

function verifyConfirmation($newPW, $confirmPW){
    global $errorMessage;
    if($confirmPW != $newPW){
        $errorMessage .= 'New password does not match confirmation. Please try again.<br />';
        return false;
    }
    return true;
}
// Updated function:
function verifyCurrentPW($currentPW, $artistData){
    global $errorMessage;
    if(!password_verify($currentPW, $artistData['password'])){
        $errorMessage .= 'Current password is incorrect. Please try again.';
        return false;
    }
    return true;
}

?>


<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Settings</h1>
        <br />
    </div>
    <div class="module-grid">
        <?php echo ArtistSettingsSuccessDisplay($successMessage);?>
        <?php echo ArtistSettingsErrorDisplay($errorMessage); ?>
        <div class="module-card module-card--span-1">Theme Selector</div>
        <div class="module-card module-card--span-2">Some kind of content goes here.</div>
        <div class="module-card module-card--span-1">
        <h1>Password change</h1>
        <form method="POST" class="module-create-form" action="">
        <input class="module-input" type="hidden" name="ArtistChangePW" placeholder="Change Password" />
        <input class="module-input" type="password" name="currentPW" placeholder="Current Password" required/><br />
        <input class="module-input" type="password" name="newPW" placeholder="New Password" required/><br />
        <input class="module-input" type="password" name="ConfirmNewPW" placeholder="Confirm New Password" required/><br />
        <button class="module-button" type="submit">Change password</button>
</form>
    </div>
    </div>