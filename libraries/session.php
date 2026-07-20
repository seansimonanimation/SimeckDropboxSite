<?php
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
];
session_set_cookie_params($cookieParams);

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect);
    exit;
}
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

session_start();
//
//libraries/session.php - Session helpers for Simeck Entertainment's Dropbox.
//
//Let's throw any defines we need right here.
if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}
include_once __ROOT__ . '/libraries/encryptlib.php';
function PutArtistDataInSession($artistData){
    $_SESSION['username'] = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['password'] = $artistData['password'];
    $_SESSION['lastname'] = $artistData['lastname'];
    $_SESSION['userID'] = $artistData['userID'];
    $_SESSION['role'] = $artistData['role'];
    $_SESSION['secondary-roles'] = $artistData ['secondary_roles']; //Secondary roles are secondary_role in the db but in the app it's secondary-role. This is the changepoint that keeps both naming conventions happy.
    $_SESSION['theme'] = $artistData['theme'] ?? 'dark-boo';
    $_SESSION['timezone'] = $artistData['timezone'] ?? 'UTC';
    $_SESSION['availability'] = $artistData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['availability_this_week'] = $artistData['availability_this_week'] ?? '0|0|0|0|0|0|0'; 
    $_SESSION['nickname'] = $artistData['nickname'] ?? '';
    $_SESSION['phone_country_code'] = decryptImportantData($artistData['phone_country_code'] ?? 1);
    $_SESSION['phone_number'] = decryptImportantData($artistData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $artistData['receive_texts'] ?? 0;
    $_SESSION['bgvid_visibility'] = $artistData['bgvid_visibility'] ?? 1;
    $_SESSION['enjoy_the_view_visibility'] = $artistData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['tempRole'] = $artistData['role']; // Store the original role in a temporary variable so admins can view as artist role.
    $_SESSION['activeModulePath'] = null; // Initialize the active module path in the session
}

function PutClientDataInSession($clientData){
    $_SESSION['username'] = $clientData['username'];
    $_SESSION['firstname'] = $clientData['firstname'];
    $_SESSION['lastname'] = $clientData['lastname'];
    $_SESSION['password'] = $clientData['password'];
    $_SESSION['project_assignments'] = $clientData['project_assignments'];
    $_SESSION['point_of_contact'] = $clientData['point_of_contact'];
    $_SESSION['timezone'] = $clientData['timezone'] ?? 'UTC';
    $_SESSION['availability'] = $clientData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['role'] = 'client';
    $_SESSION['lock_overrides'] = $clientData['lock_overrides'];
    $_SESSION['theme'] = $clientData['theme'] ?? 'dark-boo';
    $_SESSION['phone_country_code'] = decryptImportantData($clientData['phone_country_code'] ?? '+1');
    $_SESSION['phone_number'] = decryptImportantData($clientData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $clientData['receive_texts'] ?? 0;
    $_SESSION['bgvid_visibility'] = $clientData['bgvid_visibility'] ?? 1;
    $_SESSION['enjoy_the_view_visibility'] = $clientData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['tempRole'] = 'client'; // Store the original role in a temporary variable for consistency, even though clients don't have multiple roles.
    $_SESSION['activeModulePath'] = null; // Initialize the active module path in the session
    if($_SESSION['username'] === 'client') $_SESSION['impersonating'] = true; 
}
function PutVendorDataInSession($vendorData){
    $_SESSION['username'] = $vendorData['username'];
    $_SESSION['company_name'] = $vendorData['company_name'];
    $_SESSION['firstname'] = $vendorData['vendor_poc_firstname'];
    $_SESSION['lastname'] = $vendorData['vendor_poc_lastname'];
    $_SESSION['password'] = $vendorData['password'];
    $_SESSION['project_assignments'] = $vendorData['project_assignments'];
    $_SESSION['point_of_contact'] = $vendorData['point_of_contact'];
    $_SESSION['timezone'] = $vendorData['timezone'] ?? 'UTC';
    $_SESSION['availability'] = $vendorData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['role'] = 'vendor';
    $_SESSION['theme'] = $vendorData['theme'] ?? 'dark-boo';
    $_SESSION['phone_country_code'] = decryptImportantData($vendorData['phone_country_code'] ?? 1);
    $_SESSION['phone_number'] = decryptImportantData($vendorData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $vendorData['receive_texts'] ?? 0;
    $_SESSION['bgvid_visibility'] = $vendorData['bgvid_visibility'] ?? 0;
    $_SESSION['enjoy_the_view_visibility'] = $vendorData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['tempRole'] = 'vendor';
    $_SESSION['activeModulePath'] = null;
}


function GetUserTheme(){
    return $_SESSION['theme'] ?? 'dark-boo';
}


function GetUserName(){
    return $_SESSION['username'];
}

function GetHumanName($format){
    // Vendors use company_name or POC name
    $isVendor = ($_SESSION['role'] ?? '') === 'vendor';
    switch($format){
        case 'first':
            return $_SESSION['firstname'];
        case 'last':
            return $_SESSION['lastname'];
        case 'firstlast':
            return $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
        case 'lastfirst':
            return $_SESSION['lastname'] . ', ' . $_SESSION['firstname'];
        case 'nickname':
            return (!empty($_SESSION['nickname'])) ? $_SESSION['nickname'] : $_SESSION['firstname'];
        case 'greeting':
            if($isVendor && !empty($_SESSION['company_name'])){
                return $_SESSION['company_name'];
            }
            return (!empty($_SESSION['nickname'])) ? $_SESSION['nickname'] : $_SESSION['firstname'];
        case 'name':
            // Returns company name for vendors, first+last for others
            if($isVendor && !empty($_SESSION['company_name'])){
                return $_SESSION['company_name'];
            }
            return $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
        default:
            return $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    }
}


function GetRole(){

        return $_SESSION['role'];
}

function GetTempRole(){
    return $_SESSION['tempRole'];
}
function RefreshPortal(){
    // AJAX requests should get JSON, not a redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
}

function ImpersonateArtist($artistData){
    //Log the impersonation action
        LogSimeckAction('Started impersonation',$_SESSION['username'] . " started impersonating artist '{$artistData['username']}'.", 'System');
    // Save original admin data
    $_SESSION['_imp_orig_username']  = $_SESSION['username'];
    $_SESSION['_imp_orig_firstname'] = $_SESSION['firstname'];
    $_SESSION['_imp_orig_lastname']  = $_SESSION['lastname'];
    $_SESSION['_imp_orig_userID']    = $_SESSION['userID'];
    $_SESSION['_imp_orig_secondary-roles'] = $_SESSION['secondary-roles'];
    $_SESSION['_imp_orig_availability'] = $_SESSION['availability'];
    $_SESSION['_imp_orig_nickname']  = $_SESSION['nickname'] ?? '';
    $_SESSION['_imp_orig_phone_country_code'] = $_SESSION['phone_country_code'] ?? 1;
    $_SESSION['_imp_orig_phone_number'] = $_SESSION['phone_number'] ?? null;
    $_SESSION['_imp_orig_receive_texts'] = $_SESSION['receive_texts'] ?? 0;
    $_SESSION['_imp_orig_enjoy_the_view_visibility'] = $_SESSION['enjoy_the_view_visibility'] ?? 1;


    // Override with impersonated artist's data
    $_SESSION['username']  = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['lastname']  = $artistData['lastname'];
    $_SESSION['nickname']  = $artistData['nickname'] ?? '';
    $_SESSION['userID']    = $artistData['userID'];
    $_SESSION['secondary-roles'] = $artistData['secondary_roles'];
    $_SESSION['availability'] = $artistData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['phone_country_code'] = decryptImportantData($artistData['phone_country_code'] ?? 1);
    $_SESSION['phone_number'] = decryptImportantData($artistData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $artistData['receive_texts'] ?? 0;
    $_SESSION['enjoy_the_view_visibility'] = $artistData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['impersonating'] = true;
    
    $_SESSION['tempRole'] = 'artist'; // Shows artist modules

}
function ImpersonateClient($clientData){
    //Log the impersonation action
        LogSimeckAction('Started impersonation',$_SESSION['username'] . " started impersonating client '{$clientData['username']}'.", 'System');
    // Save original admin data
    $_SESSION['_imp_orig_username']  = $_SESSION['username'];
    $_SESSION['_imp_orig_firstname'] = $_SESSION['firstname'];
    $_SESSION['_imp_orig_lastname']  = $_SESSION['lastname'];
    $_SESSION['_imp_orig_userID']    = $_SESSION['userID'];
    $_SESSION['_imp_orig_availability'] = $_SESSION['availability'];
    $_SESSION['_imp_orig_phone_country_code'] = $_SESSION['phone_country_code'] ?? '+1';
    $_SESSION['_imp_orig_phone_number'] = $_SESSION['phone_number'] ?? null;
    $_SESSION['_imp_orig_receive_texts'] = $_SESSION['receive_texts'] ?? 0;
    $_SESSION['_imp_orig_enjoy_the_view_visibility'] = $_SESSION['enjoy_the_view_visibility'] ?? 1;


    // Override with impersonated client's data
    $_SESSION['username']      = $clientData['username'];
    $_SESSION['firstname']     = $clientData['firstname'];
    $_SESSION['lastname']      = $clientData['lastname'];
    $_SESSION['project_assignments'] = $clientData['project_assignments'];
    $_SESSION['point_of_contact'] = $clientData['point_of_contact'];
    $_SESSION['lock_overrides'] = $clientData['lock_overrides'];
    $_SESSION['availability'] = $clientData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['phone_country_code'] = decryptImportantData($clientData['phone_country_code'] ?? '+1');
    $_SESSION['phone_number'] = decryptImportantData($clientData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $clientData['receive_texts'] ?? 0;
    $_SESSION['enjoy_the_view_visibility'] = $clientData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['impersonating'] = true;
    $_SESSION['tempRole'] = 'client';
}
function ImpersonateVendor($vendorData){
    LogSimeckAction('Started impersonation', $_SESSION['username'] . " started impersonating vendor '{$vendorData['username']}'.", 'System');
    // Save original admin data
    $_SESSION['_imp_orig_username']  = $_SESSION['username'];
    $_SESSION['_imp_orig_firstname'] = $_SESSION['firstname'];
    $_SESSION['_imp_orig_lastname']  = $_SESSION['lastname'];
    $_SESSION['_imp_orig_userID']    = $_SESSION['userID'];
    $_SESSION['_imp_orig_availability'] = $_SESSION['availability'];
    $_SESSION['_imp_orig_company_name'] = $_SESSION['company_name'] ?? '';
    $_SESSION['_imp_orig_phone_country_code'] = $_SESSION['phone_country_code'] ?? 1;
    $_SESSION['_imp_orig_phone_number'] = $_SESSION['phone_number'] ?? null;
    $_SESSION['_imp_orig_receive_texts'] = $_SESSION['receive_texts'] ?? 0;
    $_SESSION['_imp_orig_secondary-roles'] = $_SESSION['secondary-roles'] ?? '';
    $_SESSION['_imp_orig_enjoy_the_view_visibility'] = $_SESSION['enjoy_the_view_visibility'] ?? 1;

    // Override with impersonated vendor's data
    $_SESSION['username']      = $vendorData['username'];
    $_SESSION['company_name']  = $vendorData['company_name'] ?? '';
    $_SESSION['firstname']     = $vendorData['vendor_poc_firstname'] ?? '';
    $_SESSION['lastname']      = $vendorData['vendor_poc_lastname'] ?? '';
    $_SESSION['project_assignments'] = $vendorData['project_assignments'] ?? '';
    $_SESSION['point_of_contact'] = $vendorData['point_of_contact'] ?? '';
    $_SESSION['availability'] = $vendorData['availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['phone_country_code'] = decryptImportantData($vendorData['phone_country_code'] ?? 1);
    $_SESSION['phone_number'] = decryptImportantData($vendorData['phone_number'] ?? null);
    $_SESSION['receive_texts'] = $vendorData['receive_texts'] ?? 0;
    $_SESSION['enjoy_the_view_visibility'] = $vendorData['enjoy_the_view_visibility'] ?? 1;
    $_SESSION['impersonating'] = true;
    $_SESSION['tempRole'] = 'vendor';
}

function StopImpersonating(){
    if(!isset($_SESSION['_imp_orig_username'])) return;
    //Log the end of the impersonation action
        LogSimeckAction('Stopped impersonation',$_SESSION['_imp_orig_username'] . " stopped impersonating. Reverted back from '{$_SESSION['username']}'.", 'System');
    $_SESSION['username']  = $_SESSION['_imp_orig_username'];
    $_SESSION['firstname'] = $_SESSION['_imp_orig_firstname'];
    $_SESSION['lastname']  = $_SESSION['_imp_orig_lastname'];
    $_SESSION['nickname']  = $_SESSION['_imp_orig_nickname'] ?? '';
    $_SESSION['userID']    = $_SESSION['_imp_orig_userID'];
    $_SESSION['secondary-roles'] = $_SESSION['_imp_orig_secondary-roles'];
    $_SESSION['phone_country_code'] = $_SESSION['_imp_orig_phone_country_code'] ?? 1;
    $_SESSION['phone_number'] = $_SESSION['_imp_orig_phone_number'] ?? null;
    $_SESSION['receive_texts'] = $_SESSION['_imp_orig_receive_texts'] ?? 0;
    $_SESSION['availability'] = $_SESSION['_imp_orig_availability'] ?? '0|0|0|0|0|0|0';
    $_SESSION['enjoy_the_view_visibility'] = $_SESSION['_imp_orig_enjoy_the_view_visibility'] ?? 1;

    if(isset($_SESSION['point_of_contact'])) unset($_SESSION['point_of_contact']);
    unset($_SESSION['_imp_orig_username']);
    unset($_SESSION['_imp_orig_firstname']);
    unset($_SESSION['_imp_orig_lastname']);
    unset($_SESSION['_imp_orig_userID']);
    unset($_SESSION['_imp_orig_nickname']);
    unset($_SESSION['_imp_orig_phone_country_code']);
    unset($_SESSION['_imp_orig_phone_number']);
    unset($_SESSION['_imp_orig_receive_texts']);
    unset($_SESSION['_imp_orig_secondary-roles']);
    unset($_SESSION['_imp_orig_enjoy_the_view_visibility']);
    unset($_SESSION['impersonating']);
    unset($_SESSION['clientProjects']);
    unset($_SESSION['lock_overrides']);
    unset($_SESSION['_imp_orig_availability']);
    unset($_SESSION['_imp_orig_company_name']);
    unset($_SESSION['company_name']);

    $_SESSION['tempRole'] = 'admin';
}


function IsImpersonating(){
    return $_SESSION['impersonating'] ?? false;
}
function GetArtistNicknameOrLegalFallback($artistData){
    if(!empty($artistData['nickname'])){
        return $artistData['nickname'];
    } else {
        return $artistData['firstname'] . ' ' . $artistData['lastname'];
    }
}
function GetArtistNicknameAndLegalName($artistData){
    if(!empty($artistData['nickname'])){
        return $artistData['nickname'] . ' (' . $artistData['firstname'] . ' ' . $artistData['lastname'] . ')';
    } else {
        return $artistData['firstname'] . ' ' . $artistData['lastname'];
    }
}