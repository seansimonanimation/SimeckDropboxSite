<?php
//The module responsible for dashboard content on the artist portal. 
// yep

/**
 * @module artistSettings
 * @name Settings
 * @role artist
 * @nav-text Settings
 * @nav-icon settings
 * @nav-order 99
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';
include_once __ROOT__ . '/libraries/settingslib.php';
include_once __ROOT__ . '/libraries/logging.php';

global $errorMessage;
$errorMessage = '';
$successMessage = '';
if(isset($_GET['pw_changed'])){
    $successMessage = 'Password changed successfully.';
}
if(isset($_GET['av_saved'])){
    $successMessage = 'Availability saved successfully.';
}

if(!IsReadOnly()){
    // ── Password Change ──
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
                LogSimeckAction('Password changed', 'Artist changed their password.', 'System');
                header("Location: ?pw_changed=1");
                exit;
            } else {
                $errorMessage = 'Database error. Password was not changed.';
            }
        }
    }

    // ── Availability Save ──
    if(isset($_POST['save_availability']) && isset($_POST['av_data'])){
        $username = $_SESSION['username'];
        if(SetArtistAvailability($username, $_POST['av_data'])){
            $_SESSION['availability'] = $_POST['av_data'];
            LogSimeckAction('Availability updated', 'Artist updated their availability.', 'System');
            header("Location: ?av_saved=1");

            exit;
        } else {
            $errorMessage = 'Invalid availability data. Please try again.';
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
        <?php if($_SESSION['password'] == '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy') { echo ArtistSettingsSuccessDisplay('You are logged in with the default password. Please change your password.'); }?>

        <?php
        $themes = DiscoverThemes();
        $currentTheme = $_SESSION['theme'] ?? 'dark-boo';
        ?>
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Theme Settings</h3>
            </div>
            <div class="module-card__content">
                <form method="get" action="index.php">
                    <label for="theme-select" class="module-form-group" style="margin-bottom:12px;">
                        <span style="margin-bottom:4px;">Choose your theme</span>
                        <select name="theme" id="theme-select" class="module-input" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                            <?php foreach($themes as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] === $currentTheme) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <input type="hidden" name="action" value="set_theme">
                    <noscript><button type="submit" class="btn--sm">Apply</button></noscript>
                </form>
            </div>
        </div>
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Timezone</h3>
            </div>
            <div class="module-card__content">
                <form method="get" action="index.php">
                    <label for="timezone-select" class="module-form-group" style="margin-bottom:12px;">
                        <span style="margin-bottom:4px;">Your local timezone</span>
                        <select name="timezone" id="timezone-select" class="module-input" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                            <?php
                            $currentTz = $_SESSION['timezone'] ?? 'UTC';
                            $tzIds = DateTimeZone::listIdentifiers();
                            foreach($tzIds as $tz):
                            ?>
                                <option value="<?php echo $tz; ?>" <?php echo ($tz === $currentTz) ? 'selected' : ''; ?>>
                                    <?php echo $tz; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <input type="hidden" name="action" value="set_timezone">
                    <noscript><button type="submit" class="btn--sm">Apply</button></noscript>
                </form>
            </div>
        </div>
        <div class="module-card module-card--placeholder"></div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!--  PASSWORD CHANGE                                                -->
        <!-- ════════════════════════════════════════════════════════════════ -->
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
        <div class="module-card module-card--placeholder"></div>
        <!-- ════════════════════════════════════════════════════════════════ -->
        <!--  AVAILABILITY GRID — Half Width                                -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-2" style="overflow:visible;">
            <div class="module-card__header">
                <h3 class="module-card__title">Weekly Availability</h3>
            </div>
            <div class="module-card__content">
                <p style="font-size:0.85rem;color:var(--color-text-muted,#888);margin:0 0 12px 0;">
                    Click any half-hour block to toggle your availability. Click <strong>Apply</strong> when done.
                </p>

                <form id="av-form" method="post" action="">
                    <input type="hidden" name="save_availability" value="1" />
                    <input type="hidden" name="av_data" id="av-data" value="" />

                    <div id="av-grid-container"></div>

                    <div style="margin-top:14px;display:flex;gap:12px;align-items:center;">
                        <button type="button" id="av-apply-btn" class="module-button" style="padding:8px 24px;font-weight:600;">Apply</button>
                        <span style="font-size:0.82rem;color:var(--color-text-muted,#888);">
                            Changes are not saved until you click Apply.
                        </span>
                    </div>
                </form>
            </div>
        </div>


<!-- ═══ Availability Grid JavaScript ═══ -->
<script src="/modules/artist/artistSettings/availability.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var initialStr = <?php echo json_encode($_SESSION['availability'] ?? '0|0|0|0|0|0|0'); ?>;
    AvailabilityGrid.init('av-grid-container', initialStr);
});
</script>