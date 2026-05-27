<?php
session_start();
//
//libraries/session.php - Session helpers for Simeck Entertainment's Dropbox.
//
//Let's throw any defines we need right here.
if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}

function PutArtistDataInSession($artistData){
    $_SESSION['username'] = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['lastname'] = $artistData['lastname'];
    $_SESSION['userID'] = $artistData['userID'];
    $_SESSION['role'] = $artistData['role'];
    $_SESSION['theme'] = $artistData['theme'] ?? 'dark-boo';
    $_SESSION['tempRole'] = $artistData['role']; // Store the original role in a temporary variable so admins can view as artist role.
    $_SESSION['activeModulePath'] = null; // Initialize the active module path in the session
}

function PutClientDataInSession($clientData){
    $_SESSION['username'] = $clientData['username'];
    $_SESSION['firstname'] = $clientData['firstname'];
    $_SESSION['lastname'] = $clientData['lastname'];
    $_SESSION['project_assignments'] = $clientData['project_assignments'];
    $_SESSION['point_of_contact'] = $clientData['point_of_contact'];
    $_SESSION['role'] = 'client';
    $_SESSION['theme'] = $clientData['theme'] ?? 'dark-boo';
    $_SESSION['tempRole'] = 'client'; // Store the original role in a temporary variable for consistency, even though clients don't have multiple roles.
    $_SESSION['activeModulePath'] = null; // Initialize the active module path in the session
}

function GetUserTheme(){
    return $_SESSION['theme'] ?? 'dark-boo';
}


function GetUserName(){
    return $_SESSION['username'];
}

function GetHumanName($format){
    switch($format){
        case 'first':
            return $_SESSION['firstname'];
        case 'last':
            return $_SESSION['lastname'];
        case 'firstlast':
            return $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
        case 'lastfirst':
            return $_SESSION['lastname'] . ', ' . $_SESSION['firstname'];
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
    // Save original admin data
    $_SESSION['_imp_orig_username']  = $_SESSION['username'];
    $_SESSION['_imp_orig_firstname'] = $_SESSION['firstname'];
    $_SESSION['_imp_orig_lastname']  = $_SESSION['lastname'];
    $_SESSION['_imp_orig_userID']    = $_SESSION['userID'];

    // Override with impersonated artist's data
    $_SESSION['username']  = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['lastname']  = $artistData['lastname'];
    $_SESSION['userID']    = $artistData['userID'];
    $_SESSION['impersonating'] = true;
    $_SESSION['tempRole'] = 'artist'; // Shows artist modules
}
function ImpersonateClient($clientData){
    // Save original admin data
    $_SESSION['_imp_orig_username']  = $_SESSION['username'];
    $_SESSION['_imp_orig_firstname'] = $_SESSION['firstname'];
    $_SESSION['_imp_orig_lastname']  = $_SESSION['lastname'];
    $_SESSION['_imp_orig_userID']    = $_SESSION['userID'];

    // Override with impersonated client's data
    $_SESSION['username']      = $clientData['username'];
    $_SESSION['firstname']     = $clientData['firstname'];
    $_SESSION['lastname']      = $clientData['lastname'];
    $_SESSION['project_assignments'] = $clientData['project_assignments'];
    $_SESSION['point_of_contact'] = $clientData['point_of_contact'];
    $_SESSION['impersonating'] = true;
    $_SESSION['tempRole'] = 'client';
}

function StopImpersonating(){
    if(!isset($_SESSION['_imp_orig_username'])) return;

    $_SESSION['username']  = $_SESSION['_imp_orig_username'];
    $_SESSION['firstname'] = $_SESSION['_imp_orig_firstname'];
    $_SESSION['lastname']  = $_SESSION['_imp_orig_lastname'];
    $_SESSION['userID']    = $_SESSION['_imp_orig_userID'];

    if(isset($_SESSION['point_of_contact'])) unset($_SESSION['point_of_contact']);
    unset($_SESSION['_imp_orig_username']);
    unset($_SESSION['_imp_orig_firstname']);
    unset($_SESSION['_imp_orig_lastname']);
    unset($_SESSION['_imp_orig_userID']);
    unset($_SESSION['impersonating']);
    unset($_SESSION['clientProjects']);

    $_SESSION['tempRole'] = 'admin';
}


function IsImpersonating(){
    return $_SESSION['impersonating'] ?? false;
}
function IsReadOnly(){
    return $_SESSION['impersonating'] ?? false;
}
