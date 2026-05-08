<?php
//
//libraries/session.php - Session helpers for Simeck Entertainment's Dropbox.
//
session_start();
function PutArtistDataInSession($artistData){
    $_SESSION['username'] = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['lastname'] = $artistData['lastname'];
    $_SESSION['role'] = $artistData['role'];
    $_SESSION['tempRole'] = $artistData['role']; // Store the original role in a temporary variable so admins can view as artist role.
}

function PutClientDataInSession($clientData){
    $_SESSION['username'] = $clientData['email'];
    $_SESSION['firstname'] = $clientData['firstname'];
    $_SESSION['lastname'] = $clientData['lastname'];
    $_SESSION['role'] = 'client';
    $_SESSION['tempRole'] = 'client'; // Store the original role in a temporary variable for consistency, even though clients don't have multiple roles.
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
?>