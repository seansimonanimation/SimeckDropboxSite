<?php 
//Contains all functions related to the dashboard modules, which are used in the admin, client, and artist dashboards.


include_once (__DIR__ . '/session.php');
include_once (__DIR__ . '/db.php');


function DisplayChangelog(){
    //Simply reads the changelog.txt file and returns it as a string to be displayed on the dashboard.
    return file_get_contents(__ROOT__ .'/changelog.txt');
}

function GetClientCount(bool $includeInactive = false){

    $SQLString = 'SELECT COUNT(*) as client_count FROM clients';
    if(!$includeInactive){
        $SQLString .= ' WHERE active = 1';
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['client_count'];
}

function GetArtistCount(bool $includeInactive = false){
    $SQLString = 'SELECT COUNT(*) as artist_count FROM artists';
    if(!$includeInactive){
        $SQLString .= ' WHERE active = 1';
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['artist_count'];
}

function GetTotalCommentCount(){
    $SQLString = 'SELECT COUNT(*) as comment_count FROM filecomments';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];
}
function GetNASUsage(){
    return 'Not implemented';
}