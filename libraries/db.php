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

$db_instance = null;
function DBConnect(){
    global $db_instance;
    if($db_instance != null){
        return $db_instance;
    }

    $dbconfig = include __DIR__ . '/dbconfig.php';
    $db = $dbconfig['simeckdb']; // default connection
    $dsnData = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
    $db_instance = new PDO($dsnData, $db['user'], $db['pass']);
    $db_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db_instance;
}


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
function GetClientCount(bool $includeInactive = false){

    $SQLString = 'SELECT COUNT(*) as client_count FROM clients';
    if($includeInactive){
        $SQLString = 'SELECT COUNT(*) as client_count FROM clients WHERE active = 1';
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['client_count'];
}

function GetArtistCount(bool $includeInactive = false){
    $SQLString = 'SELECT COUNT(*) as artist_count FROM artists';
    if($includeInactive){
        $SQLString = 'SELECT COUNT(*) as artist_count FROM artists WHERE active = 1';
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['artist_count'];
}

function GetTimeclockEntries($startDate = null, $endDate = null, $artist = null){
    $SQLString = 'SELECT * FROM timeclockshifts WHERE 1=1';
    $params = [];
    if($startDate){
        $SQLString .= ' AND time_in >= ?';
        $params[] = $startDate;
    }
    if($endDate){
        $SQLString .= ' AND time_out <= ?';
        $params[] = $endDate;
    }
    if($artist){
        $SQLString .= ' AND user = ?';
        $params[] = $artist;
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function CloseTimeclockShift($shiftID){
    $SQLString = 'UPDATE timeclockshifts SET time_out = NOW() WHERE shift_id = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$shiftID]);
}
?>