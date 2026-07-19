<?php
/**
 * @module vendorSettings
 * @name Settings
 * @role vendor
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

if(!IsImpersonating()){
    // ── Phone Info Save ──
    if(isset($_POST['save_phone_info'])){
        $countryCode = $_POST['phone_country_code'];
        $phoneNumber = preg_replace('/[^0-9]/', '', $_POST['phone_number']);
        $receiveTexts = isset($_POST['receive_texts']) ? (int)$_POST['receive_texts'] : 0;
        if(SetVendorPhoneInfo($_SESSION['username'], $countryCode, $phoneNumber, $receiveTexts)){
            $_SESSION['phone_country_code'] = $countryCode;
            $_SESSION['phone_number'] = $phoneNumber;
            $_SESSION['receive_texts'] = $receiveTexts;
            LogSimeckAction('Phone number updated', "Vendor updated their phone settings.", 'System');
            $successMessage = 'Phone number saved successfully.';
        } else {
            $errorMessage = 'Database error. Phone number was not saved.';
        }
    }

    // ── Password Change ──
    if(isset($_POST['VendorChangePW'])){
        $username = $_SESSION['username'];
        $currentPW = $_POST['currentPW'];
        $confirmPW = $_POST['ConfirmNewPW'];
        $newPW = $_POST['newPW'];
        $vendorData = pull_vendor_data($_SESSION['username']);
        verifyConfirmation($newPW,$confirmPW);
        if($errorMessage === ''){ verifyCurrentPW($currentPW, $vendorData); }

        if($errorMessage === ''){
            if(SetVendorPassword($username, $newPW)){
                LogSimeckAction('Password changed', 'Vendor changed their password.', 'System');
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
        if(SetVendorAvailability($username, $_POST['av_data'])){
            $_SESSION['availability'] = $_POST['av_data'];
            LogSimeckAction('Availability updated', 'Vendor updated their availability.', 'System');
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
function verifyCurrentPW($currentPW, $userData){
    global $errorMessage;
    if(!password_verify($currentPW, $userData['password'])){
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
        <?php VendorSettingsSuccessDisplay($successMessage); ?>
        <?php VendorSettingsErrorDisplay($errorMessage); ?>

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
                <div class="bgvid-toggle-row">
                    <span>Background Video</span>
                    <a href="index.php?action=toggle_bgvid" class="bgvid-toggle-link">
                        <?php echo !empty($_SESSION['bgvid_visibility']) ? '✅' : '❌'; ?>
                    </a>
                </div>
                <?php if(!empty($_SESSION['bgvid_visibility'] && $_SESSION['bgvid_visibility'] === 1)): ?>
                <div class="bgvid-toggle-row">
                    <span>Enjoy the View</span>
                    <a href="index.php?action=toggle_enjoy_view" class="bgvid-toggle-link">
                        <?php echo !empty($_SESSION['enjoy_the_view_visibility']) ? '✅' : '❌'; ?>
                    </a>
                </div>  
                <?php endif; ?>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════ -->
        <!--  TIMEZONE                                        -->
        <!-- ════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Timezone</h3>
            </div>
            <div class="module-card__content">
                <form method="get" action="index.php">
                    <label class="module-form-group" style="margin-bottom:12px;">
                        <span style="margin-bottom:4px;">Your local timezone</span>
                        <select name="timezone" id="timezone-select" class="module-input" onchange="this.form.submit()">
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

        <!-- ════════════════════════════════════════════════ -->
        <!--  PHONE NUMBER                                    -->
        <!-- ════════════════════════════════════════════════ -->
        <?php
        $phoneInfo = GetVendorPhoneInfo($_SESSION['username']);
        $ccOption  = $phoneInfo['phone_country_code'] ?? 1;
        $phoneVal  = $phoneInfo['phone_number'] ?? '';
        $receive   = (int)($phoneInfo['receive_texts'] ?? 0);
        ?>
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Phone Number (For notifications)</h3>
            </div>
            <div class="module-card__content">
                <form method="POST" action="">
                    <label class="module-form-group" style="margin-bottom:8px;">
                        <span style="margin-bottom:4px;">Country Code</span>
                        <select name="phone_country_code" class="module-input" style="width:auto;min-width:200px;">
                            <?php echo GetCountryCodeOptions('+' . $ccOption); ?>
                        </select>
                    </label>
                    <label class="module-form-group" style="margin-bottom:8px;">
                        <span style="margin-bottom:4px;">Phone Number</span>
                        <input class="module-input" type="text" name="phone_number"
                               value="<?php echo htmlspecialchars($phoneVal); ?>"
                               placeholder="5551234567" style="width:auto;min-width:200px;" />
                    </label>
                    <div style="margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                        <span>Receive notification texts:</span>
                        <a href="#" class="toggle-receive-texts"
                           data-receive="<?php echo $receive; ?>"
                           style="font-size:1.5rem;text-decoration:none;">
                            <?php echo $receive ? '✅' : '❌'; ?>
                        </a>
                        <input type="hidden" name="receive_texts" id="receive_texts_input" value="<?php echo $receive; ?>" />
                    </div>
                    <input type="hidden" name="save_phone_info" value="1" />
                    <button type="submit" class="module-button" style="padding:6px 18px;">Save Phone Info</button>
                </form>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════ -->
        <!--  PASSWORD CHANGE                                 -->
        <!-- ════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-1">
            <h1>Password change</h1>
            <form method="POST" class="module-create-form" action="">
                <input class="module-input" type="hidden" name="VendorChangePW" placeholder="Change Password" />
                <input class="module-input" type="password" name="currentPW" placeholder="Current Password" required/><br />
                <input class="module-input" type="password" name="newPW" placeholder="New Password" required/><br />
                <input class="module-input" type="password" name="ConfirmNewPW" placeholder="Confirm New Password" required/><br />
                <button class="module-button" type="submit">Change password</button>
            </form>
        </div>

        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--placeholder"></div>

        <!-- ════════════════════════════════════════════════ -->
        <!--  AVAILABILITY GRID                              -->
        <!-- ════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-3">
            <div class="module-card__header">
                <h3 class="module-card__title">Weekly Availability</h3>
            </div>
            <div class="module-card__content">
                <p style="font-size:0.85rem;color:var(--color-text-muted,#888);margin:0 0 12px 0;">
                    Click and drag over half-hour blocks to toggle your availability. Click <strong>Apply</strong> when done.
                </p>

                <form id="av-form" method="post" action="">
                    <input type="hidden" name="save_availability" value="1" />
                    <input type="hidden" name="av_data" id="av-data" value="" />

                    <div id="av-grid-container"></div>

                    <div style="margin-top:14px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                        <button type="button" id="av-apply-btn" class="module-button" style="padding:8px 24px;font-weight:600;">Apply</button>
                        <span style="font-size:0.82rem;color:var(--color-text-muted,#888);">
                            Changes are not saved until you click Apply.
                        </span>
                    </div>
                </form>
            </div>
        </div>
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
    document.querySelectorAll('.toggle-receive-texts').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            var input = document.getElementById('receive_texts_input');
            var current = parseInt(this.dataset.receive);
            var newVal = current ? 0 : 1;
            this.dataset.receive = newVal;
            this.innerHTML = newVal ? '✅' : '❌';
            input.value = newVal;
        });
    });
});
</script>
