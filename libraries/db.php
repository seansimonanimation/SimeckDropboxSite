<?php

//
//libraries/db.php - PDO connection factory.
//
// usage: $pdo = db(); // default connection (simeck DB)
//        $dbacct = connection account.
// Connections are lazy singletons: the first call for a given key opens the
// connection; subsequent calls in the same request return the cached instance.
//

$DBConfigLoc = 'C:\Users\rsimon_ptaa\Documents\dropbox.simeck.com\dbconfig.php'; //Iwerks only
// $DBConfigLoc = 'C:\Users\randy\Documents\dropbox.simeck.com\dbconfig.php'; //Fabio only

$artistAdminSQL = "Select * from artists where username = ? AND active = 1";
$clientSQL = "Select * from clients where email = ? AND active = 1";

function pull_artistAdmin_data($username){
    global $artistAdminSQL;
    $pdo = DBConnect();
    $stmt = $pdo->prepare($artistAdminSQL);
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function pull_client_data($email){
    global $clientSQL;
    $pdo = DBConnect();
    $stmt = $pdo->prepare($clientSQL);
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function SetArtistPassword($currentPass){
    //TODO: implement this function. It should take the current password, verify it, and if correct, prompt the user for a new password and update the database with the new password hash.
}

?>