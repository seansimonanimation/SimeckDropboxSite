<?php
include_once __DIR__ . '/../../libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';



function GenerateTimeclockTable(){

$entryArray = GetTimeclockEntries();
echo "<div class='tcm-card'>";
echo "<table width='100%' border='0' style='border-collapse: collapse;table-layout:fixed;'><tr><td>Shift ID</td><td>User</td><td>Time In</td><td>Time Out</td><td>Shift Length</td></tr>";
echo "</table></div>";
foreach($entryArray as $entry){
    echo "<div class='tcm-card'><table width='100%' border='0' style='border-collapse: collapse;table-layout:fixed;'>";
    echo "<tr>";
    echo "<td>" . $entry['shift_id'] . "</td>";
    echo "<td>" . $entry['user'] . "</td>";
    echo "<td>" . $entry['time_in'] . "</td>";
    echo "<td>" . $entry['time_out'] . "</td>";
    echo "<td>" . DetermineShiftLengthOrSummonButton($entry['time_in'],$entry['time_out'], $entry['shift_id']) . "</td>";
    echo "</tr>";
    echo "</table></div>";
}

}

function GetClockedInArtistCount(){
    $SQLString = 'SELECT COUNT(*) as clocked_in_count FROM timeclockshifts WHERE time_out IS NULL OR time_out = NULL';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['clocked_in_count'];
}

function DetermineShiftLengthOrSummonButton($timeIn, $timeOut, $shiftID){
    if($timeOut == ''){
        return '<button onclick="clockArtistOut(' . $shiftID . ')">Currently Working.<br /> Click to Clock Out</button>';
    }
    $start = new DateTime($timeIn);
    $end = new DateTime($timeOut);
    $interval = $start->diff($end);
    return $interval->format('%h hours %i minutes');
}

function ClockEveryoneOut(){
    $SQLString = 'UPDATE timeclockshifts SET time_out = NOW() WHERE time_out IS NULL OR time_out = NULL';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
}
?>