<?php
//The module responsible for dashboard content on the client portal. 
// yep

/**
 * @module clientSettings
 * @name Settings
 * @role client
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

if(!IsImpersonating()){
    if(isset($_POST['ClientChangePW'])){
        $username = $_SESSION['username'];
        $currentPW = $_POST['currentPW'];
        $confirmPW = $_POST['ConfirmNewPW'];
        $newPW = $_POST['newPW'];
        $clientData = pull_client_data($_SESSION['username']);
        verifyConfirmation($newPW,$confirmPW);
        if($errorMessage === ''){ verifyCurrentPW($currentPW, $clientData); }

        if($errorMessage === ''){
            if(SetClientPassword($username, $newPW)){
                header("Location: ?pw_changed=1");
                exit;
            } else {
                $errorMessage = 'Database error. Password was not changed.';
            }
        }
    }
    // ── Phone Info Save ──
    if(isset($_POST['save_phone_info'])){
        $countryCode = $_POST['phone_country_code'];
        $phoneNumber = preg_replace('/[^0-9]/', '', $_POST['phone_number']);
        $receiveTexts = isset($_POST['receive_texts']) ? (int)$_POST['receive_texts'] : 0;
        if(SetClientPhoneInfo($_SESSION['username'], $countryCode, $phoneNumber, $receiveTexts)){
            $_SESSION['phone_country_code'] = $countryCode;
            $_SESSION['phone_number'] = $phoneNumber;
            $_SESSION['receive_texts'] = $receiveTexts;
            LogSimeckAction('Phone number updated', "Client updated their phone settings.", 'System');
            $successMessage = 'Phone number saved successfully.';
        } else {
            $errorMessage = 'Database error. Phone number was not saved.';
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
        <?php echo ClientSettingsSuccessDisplay($successMessage);?>
        <?php echo ClientSettingsErrorDisplay($errorMessage); ?>
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

            </div>
        </div>
        <!-- ════════════════════════════════════════════════════════════════ -->
        <!--  PHONE NUMBER (For notifications)                               -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <?php
        $phoneInfo = GetClientPhoneInfo($_SESSION['username']);
        $ccOption  = $phoneInfo['phone_country_code'] ?? '+1';
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
                            <?php echo GetCountryCodeOptions($ccOption); ?>
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

        <div class="module-card module-card--span-1"><center><h1> File Lock Overrides remaining:</h1><h2><?php echo GetClientLockOverrideCount(); ?></h2>Please speak to Randy or Carl to purchase new overrides.</center></div>
        <div class="module-card module-card--span-1">
            <h1>Password change</h1>
            <form method="POST" class="module-create-form" action="">
                <input class="module-input" type="hidden" name="ClientChangePW" placeholder="Change Password" />
                <input class="module-input" type="password" name="currentPW" placeholder="Current Password" required/><br />
                <input class="module-input" type="password" name="newPW" placeholder="New Password" required/><br />
                <input class="module-input" type="password" name="ConfirmNewPW" placeholder="Confirm New Password" required/><br />
                <button class="module-button" type="submit">Change password</button>
            </form>
        </div>
    </div>
</div>
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
