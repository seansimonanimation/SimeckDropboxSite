<?php
include_once __DIR__ . '/encryptlib.php';

function ArtistSettingsErrorDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ArtistSettingsSuccessDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ClientSettingsErrorDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ClientSettingsSuccessDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function GetClientLockOverrideCount(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare('SELECT lock_overrides FROM clients WHERE username = ?');
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['lock_overrides'] : 0;
}
function GetArtistAvailability($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT availability FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetchColumn();
    return $result ?: '0|0|0|0|0|0|0';
}

function SetArtistAvailability($username, $availabilityString){
    $parts = explode('|', $availabilityString);
    if(count($parts) !== 7){
        return false;
    }
    foreach($parts as $part){
        if(!ctype_digit($part)){
            return false;
        }
        // Max value for 48 bits = 2^48 - 1 = 281474976710655
        if((int)$part > 281474976710655){
            return false;
        }
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE artists SET availability = ? WHERE username = ?");
    return $stmt->execute([$availabilityString, $username]);
}
// ════════════════════════════════════════════════════════════════
//  PHONE NUMBER / NOTIFICATION SETTINGS  (ENCRYPTED)
// ════════════════════════════════════════════════════════════════

function GetArtistPhoneInfo($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT phone_country_code, phone_number, receive_texts FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return [
            'phone_country_code' => 1,
            'phone_number' => null,
            'receive_texts' => 0
        ];
    }
    return [
        'phone_country_code' => (int)decryptImportantData($row['phone_country_code']),
        'phone_number'       => decryptImportantData($row['phone_number']),
        'receive_texts'      => (int)$row['receive_texts']
    ];
}

function SetArtistPhoneInfo($username, $countryCode, $phoneNumber, $receiveTexts){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE artists SET phone_country_code = ?, phone_number = ?, receive_texts = ? WHERE username = ?");
    return $stmt->execute([
        encryptImportantData((string)(int)$countryCode),
        encryptImportantData($phoneNumber),
        (int)$receiveTexts,
        $username
    ]);
}

function GetClientPhoneInfo($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT phone_country_code, phone_number, receive_texts FROM clients WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return [
            'phone_country_code' => '+1',
            'phone_number' => null,
            'receive_texts' => 0
        ];
    }
    return [
        'phone_country_code' => decryptImportantData($row['phone_country_code']),
        'phone_number'       => decryptImportantData($row['phone_number']),
        'receive_texts'      => (int)$row['receive_texts']
    ];
}

function SetClientPhoneInfo($username, $countryCode, $phoneNumber, $receiveTexts){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE clients SET phone_country_code = ?, phone_number = ?, receive_texts = ? WHERE username = ?");
    return $stmt->execute([
        encryptImportantData($countryCode),
        encryptImportantData($phoneNumber),
        (int)$receiveTexts,
        $username
    ]);
}
function VendorSettingsErrorDisplay($inputMessage){
    if($inputMessage == ""){ return "";}
    echo '<div class="module-card module-card--span-4">';
    echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
    echo '</h1></center>';
    echo '</div>';
}

function VendorSettingsSuccessDisplay($inputMessage){
    if($inputMessage == ""){ return "";}
    echo '<div class="module-card module-card--span-4">';
    echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
    echo '</h1></center>';
    echo '</div>';
}

function GetVendorPhoneInfo($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT phone_country_code, phone_number, receive_texts FROM vendors WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return [
            'phone_country_code' => 1,
            'phone_number' => null,
            'receive_texts' => 0
        ];
    }
    return [
        'phone_country_code' => (int)decryptImportantData($row['phone_country_code']),
        'phone_number'       => decryptImportantData($row['phone_number']),
        'receive_texts'      => (int)$row['receive_texts']
    ];
}

function SetVendorPhoneInfo($username, $countryCode, $phoneNumber, $receiveTexts){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE vendors SET phone_country_code = ?, phone_number = ?, receive_texts = ? WHERE username = ?");
    return $stmt->execute([
        encryptImportantData((string)(int)$countryCode),
        encryptImportantData($phoneNumber),
        (int)$receiveTexts,
        $username
    ]);
}

function GetCountryCodeOptions($selected = '+1'){
    $autoloadPath = __ROOT__ . '/vendor/autoload.php';
    if(!file_exists($autoloadPath)){
        // Fallback if Composer not available (dev without Docker)
        return '<option value="+1" selected>United States (+1)</option>';
    }
    require_once $autoloadPath;
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    $regions = $phoneUtil->getSupportedRegions();
    $codes = [];
    foreach($regions as $region){
        $code = $phoneUtil->getCountryCodeForRegion($region);
        $label = $phoneUtil->getRegionCodeForCountryCode($code) . ' (+' . $code . ')';
        $key = '+' . $code;
        $codes[$key] = $label;
    }
    ksort($codes);
    $html = '';
    foreach($codes as $code => $label){
        $sel = ($code === $selected) ? ' selected' : '';
        $html .= '<option value="' . $code . '"' . $sel . '>' . $label . '</option>';
    }
    return $html;
}
