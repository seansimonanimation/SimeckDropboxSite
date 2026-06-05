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
    // ── Time Off Submission ──
    if(isset($_POST['submit_timeoff'])){
        $username = $_SESSION['username'];
        $dateStart = $_POST['timeoff_date_start'] ?? '';
        $dateEnd   = $_POST['timeoff_date_end'] ?? '';
        $startTime = null;
        $endTime   = null;

        // Handle "all day" — leave times as NULL
        if (!isset($_POST['timeoff_all_day'])) {
            $startTime = $_POST['timeoff_start_time'] ?? null;
            $endTime   = $_POST['timeoff_end_time'] ?? null;
        }

        // Single-day: dateEnd stays null
        if (!isset($_POST['timeoff_multi_day'])) {
            $dateEnd = null;
        }

        $result = SubmitDayOff($username, $dateStart, $dateEnd ?: null, $startTime, $endTime);
        if ($result === true) {
            $successMessage = 'Time off request submitted successfully.';
            // If availability was adjusted, update the session
            if (isset($_POST['timeoff_same_week'])) {
                // AdjustAvailabilityThisWeek already updated $_SESSION['availability']
            }
        } else {
            $errorMessage = $result;
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
        <!-- ════════════════════════════════════════════════════════════════ -->
        <!--  AVAILABILITY GRID — Half Width                                -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-3" style="overflow:visible;">
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
        <!-- ════════════════════════════════════════════════════════════════ -->
        <!--  TIME OFF REQUEST                                              -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Request Time Off</h3>
            </div>
            <div class="module-card__content" style="padding:10px 12px;">
                <form id="timeoff-form" method="post" action="" style="text-align:center;">
                    <input type="hidden" name="submit_timeoff" value="1" />

                    <!-- Date row -->
                    <div style="display:flex;gap:8px;justify-content:center;margin-bottom:4px;">
                        <div>
                            <label for="timeoff_date_start" style="display:block;margin-bottom:2px;font-size:0.75rem;">Date</label>
                            <input type="date" id="timeoff_date_start" name="timeoff_date_start" class="module-input" required style="width:auto;max-width:200px;font-size:0.82rem;" />
                        </div>
                        <div id="timeoff_end_date_group" style="display:none;">
                            <label for="timeoff_date_end" style="display:block;margin-bottom:2px;font-size:0.75rem;">End</label>
                            <input type="date" id="timeoff_date_end" name="timeoff_date_end" class="module-input" style="width:auto;max-width:200px;font-size:0.82rem;" />
                        </div>
                    </div>

                    <!-- Checkboxes row -->
                    <div style="display:flex;gap:16px;justify-content:center;margin-bottom:6px;font-size:0.82rem;">
                        <label style="display:flex;align-items:center;gap:3px;cursor:pointer;">
                            <input type="checkbox" id="timeoff_multi_day" name="timeoff_multi_day" value="1" style="margin:0;" />
                            Multi-day
                        </label>
                        <label style="display:flex;align-items:center;gap:3px;cursor:pointer;">
                            <input type="checkbox" id="timeoff_all_day" name="timeoff_all_day" value="1" style="margin:0;" />
                            All day
                        </label>
                    </div>

                    <!-- Time fields row -->
                    <div id="timeoff_time_fields" style="display:flex;gap:8px;justify-content:center;margin-bottom:8px;">
                        <div>
                            <label for="timeoff_start_time" style="display:block;margin-bottom:2px;font-size:0.75rem;">Start</label>
                            <input type="time" id="timeoff_start_time" name="timeoff_start_time" class="module-input" style="width:auto;max-width:120px;font-size:0.82rem;" />
                        </div>
                        <div>
                            <label for="timeoff_end_time" style="display:block;margin-bottom:2px;font-size:0.75rem;">End</label>
                            <input type="time" id="timeoff_end_time" name="timeoff_end_time" class="module-input" style="width:auto;max-width:120px;font-size:0.82rem;" />
                        </div>
                    </div>

                    <button type="submit" class="module-button" style="padding:5px 16px;font-size:0.85rem;">Submit</button>
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
<script>
document.addEventListener('DOMContentLoaded', function(){
    var multiDayCheckbox = document.getElementById('timeoff_multi_day');
    var endDateGroup = document.getElementById('timeoff_end_date_group');
    var allDayCheckbox = document.getElementById('timeoff_all_day');
    var timeFields = document.getElementById('timeoff_time_fields');

    if(multiDayCheckbox && endDateGroup) {
        multiDayCheckbox.addEventListener('change', function(){
            endDateGroup.style.display = this.checked ? 'block' : 'none';
        });
    }

    if(allDayCheckbox && timeFields) {
        allDayCheckbox.addEventListener('change', function(){
            timeFields.style.display = this.checked ? 'none' : 'block';
        });
    }
});
</script>
