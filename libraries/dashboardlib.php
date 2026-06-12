<?php 
//Contains all functions related to the dashboard modules, which are used in the admin, client, and artist dashboards.

if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}
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
function FormatBytes($bytes, $decimals = 2){
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $factor = floor((strlen((string)$bytes) - 1) / 3);
    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
}

function GetNASUsage(){
    $path = __ROOT__ . '/files';

    $total = @disk_total_space($path);
    $free  = @disk_free_space($path);

    if ($total === false || $free === false) {
        return 'Unable to determine drive usage';
    }

    $used      = $total - $free;
    $percent   = ($total > 0) ? round(($used / $total) * 100) : 0;

    $totalFormatted = FormatBytes($total);
    $usedFormatted   = FormatBytes($used);
    $freeFormatted   = FormatBytes($free);

    return "{$usedFormatted} / {$totalFormatted} ({$percent}% used, {$freeFormatted} free)";
}
