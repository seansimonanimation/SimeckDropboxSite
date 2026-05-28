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

function SetArtistPassword($username, $newpass){
    $hashpass = password_hash($newpass, PASSWORD_BCRYPT);
    $SQLString = "UPDATE artists SET password = ? WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$hashpass,$username]);
    return true;
}

function SetClientPassword($username, $newpass){
    $hashpass = password_hash($newpass, PASSWORD_BCRYPT);
    $SQLString = "UPDATE clients SET password = ? WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$hashpass,$username]);
    return true;
}

function SetUserTheme($username, $theme, $role){
    $table = ($role === 'client') ? 'clients' : 'artists';
    $SQLString = "UPDATE $table SET theme = ? WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $result = $stmt->execute([$theme, $username]);
    if($result){
        $_SESSION['theme'] = $theme;
    }
    return $result;
}
function SetUserTimezone($username, $timezone, $role){
    $table = ($role === 'client') ? 'clients' : 'artists';
    $SQLString = "UPDATE $table SET timezone = ? WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $result = $stmt->execute([$timezone, $username]);
    if($result){
        $_SESSION['timezone'] = $timezone;
    }
    return $result;
}
