<?php 
//Contains all functions related to the dashboard modules, which are used in the admin, client, and artist dashboards.

if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}
include_once (__DIR__ . '/session.php');
include_once (__DIR__ . '/db.php');
include_once (__DIR__ . '/artistmanagementlib.php');


function DisplayChangelog(){
    //Simply reads the changelog.txt file and returns it as a string to be displayed on the dashboard.
    return file_get_contents(__ROOT__ .'/changelog.txt');
}

function GetClientCount(bool $includeInactive = false){
    $extra = $includeInactive ? '' : "WHERE active = 1";
    $rows = PullDBValues("COUNT(*) as client_count", "clients", 1, 1, $extra);
    return $rows[0]['client_count'] ?? 0;
}


function GetArtistCount(bool $includeInactive = false){
    $extra = $includeInactive ? '' : "WHERE active = 1";
    $rows = PullDBValues("COUNT(*) as artist_count", "artists", 1, 1, $extra);
    return $rows[0]['artist_count'] ?? 0;
}


function GetTotalCommentCount(){
    $rows = PullDBValues("COUNT(*) as comment_count", "filecomments", 1, 1);
    return $rows[0]['comment_count'] ?? 0;
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
/**
 * Returns ['total' => int, 'active' => int] for the projects assigned to a client.
 * $projectAssignments is a comma-separated list of pids (as stored on the client row/session).
 */
function GetClientProjectStats($projectAssignments = null){
    if ($projectAssignments === null) {
        $projectAssignments = $_SESSION['project_assignments'] ?? '';
    }
    $pids = array_filter(array_map('trim', explode(',', $projectAssignments)));
    if (empty($pids)) {
        return ['total' => 0, 'active' => 0];
    }
    $pdo = DBConnect();
    $placeholders = implode(',', array_fill(0, count($pids), '?'));
    $sql = "SELECT COUNT(*) as total, SUM(active = 1) as active FROM projects WHERE pid IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($pids));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'total'  => (int)($row['total'] ?? 0),
        'active' => (int)($row['active'] ?? 0),
    ];
}

/**
 * Gathers point-of-contact display data for the client dashboard PoC card.
 * Returns ['name', 'timezone', 'available_now', 'availability_html'] or null.
 */
function GetClientPointOfContactCardData($pocUsername){
    if ($pocUsername === '' || $pocUsername === null) {
        return null;
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare(
        "SELECT firstname, lastname, availability, availability_this_week, timezone
         FROM artists WHERE username = ? AND active = 1"
    );
    $stmt->execute([$pocUsername]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$artist) {
        return null;
    }
    $name = trim((string)($artist['firstname'] ?? '') . ' ' . (string)($artist['lastname'] ?? ''));
    if ($name === '') {
        $name = $pocUsername;
    }
    $tz = $artist['timezone'] ?? 'UTC';
    $effectiveAv = GetEffectiveAvailability(
        $artist['availability'] ?? '0|0|0|0|0|0|0',
        $artist['availability_this_week'] ?? '0|0|0|0|0|0|0'
    );
    return [
        'name'             => $name,
        'timezone'         => $tz,
        'available_now'    => IsArtistAvailableNow($effectiveAv, $tz),
        'availability_html'=> DisplayArtistAvailability($effectiveAv, $tz),
    ];
}
