<?php

//
//libraries/db.php - PDO connection factory.
//
// usage: $pdo = db(); // default connection (simeck DB)
//        $dbacct = connection account.
// Connections are lazy singletons: the first call for a given key opens the
// connection; subsequent calls in the same request return the cached instance.
//

// $DBConfigLoc = 'C:\Users\rsimon_ptaa\Documents\dropbox.simeck.com\dbconfig.php'; //Iwerks only
// $DBConfigLoc = 'C:\Users\randy\Documents\dropbox.simeck.com\dbconfig.php'; //Fabio only
$artistAdminSQL = "Select * from artists where username = ? AND active = 1";
$clientSQL = "Select * from clients where username = ? AND active = 1";

$db_instance = null;
function DBConnect(){
    global $db_instance;
    if($db_instance != null){
        return $db_instance;
    }

    $possibleConfigPaths = [
        '/var/www/dbconfig.php', // typical Linux server location
        'C:/Users/rsimon_ptaa/Documents/dropbox.simeck.com/libraries/dbconfig.php', //school location for Iwerks
        'C:/Users/randy/Documents/dbconfig.php', //home location for Fabio
        __DIR__ . '/dbconfig.php', // default location

    ];

    $dbconfig = null;
    foreach ($possibleConfigPaths as $path) {
        if (file_exists($path)) {
            $dbconfig = include $path;
            break;
        }
    }
    if ($dbconfig === null) {
        throw new Exception('Database configuration file not found.');
    }
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
    $SQLString = 'UPDATE timeclockshifts SET time_out = CONVERT_TZ(UTC_TIMESTAMP(), "+00:00", "America/Phoenix") WHERE shift_id = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    if(IsImpersonating()){ return false;}
    $stmt->execute([$shiftID]);
    LogSimeckAction('Closed timeclock shift', "Shift #$shiftID was closed.", 'System');
}

function UpdateTimeclockShiftField($shiftId, $field, $value){
    $allowedFields = ['time_in', 'time_out', 'shift_comments'];
    if(!in_array($field, $allowedFields)){
        return false;
    }
    $SQLString = "UPDATE timeclockshifts SET $field = ? WHERE shift_id = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    if(IsImpersonating()){ return false;}
    $result = $stmt->execute([$value, $shiftId]);
    if($result){
        LogSimeckAction('Updated timeclock shift', "Shift #$shiftId had its $field updated to $value.", 'System');
    }
    return $result;
}


function GetDataFromDB($SQLString, $params = []){
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function ListAllActiveArtists(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname, nickname FROM artists WHERE active = 1 ORDER BY username");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function ListAllActiveClients(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM clients WHERE active = 1 ORDER BY lastname");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

