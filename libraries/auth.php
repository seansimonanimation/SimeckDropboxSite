<?php
//
//libraries/auth.php - Authentication library for Simeck Entertainment's Dropbox as well as session helpers.
//

include_once __DIR__ . '/db.php';
include_once __DIR__ . '/session.php';

function attempt_login($username, $password){
//This function attemps to log an artist user in first. If that fails, it attempts to log a client user in. If both fail, it returns false.
    $artistAdminData = pull_artistAdmin_data($username);
    if($artistAdminData && password_verify($password, $artistAdminData['password'])){ 
        PutArtistDataInSession($artistAdminData);
        return true;
    }
    $clientData = pull_client_data($username);
    if($clientData && password_verify($password, $clientData['password'])){
        PutClientDataInSession($clientData);
        return true;
    }
    return false;
}
    function logout(){
        session_unset();
        session_destroy();
}






?>