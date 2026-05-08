<?php
//
//libraries/session.php - Session helpers for Simeck Entertainment's Dropbox.
//

function PutArtistDataInSession($artistData){
    $_SESSION['username'] = $artistData['username'];
    $_SESSION['firstname'] = $artistData['firstname'];
    $_SESSION['lastname'] = $artistData['lastname'];
    $_SESSION['role'] = $artistData['role'];
}

function PutClientDataInSession($clientData){
    $_SESSION['username'] = $clientData['email'];
    $_SESSION['firstname'] = $clientData['firstname'];
    $_SESSION['lastname'] = $clientData['lastname'];
    $_SESSION['role'] = 'client';
}

function GetUserName(){
    return $_SESSION['username'];
}

function GetHumanName(){
    return $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
}

function GetRole(){
    return $_SESSION['role'];
}
?>