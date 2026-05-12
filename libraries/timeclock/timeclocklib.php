<?php
include_once __DIR__ . '/../../libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';



function GenerateTimeclockTable(){
    $entryArray = GetTimeclockEntries();
    echo '<table id="ShiftList" class="display" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>User</th><th>Time In</th><th>Time Out</th><th>Shift Length</th><th>Delete</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['user']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['time_in']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['time_out']) . '</td>';
        echo '<td>' . DetermineShiftLengthOrSummonButton($entry['time_in'], $entry['time_out'], $entry['shift_id']) . '</td>';
        echo '<td><a href="index.php?delete_shift_id=' . htmlspecialchars($entry['shift_id']) . '">❌</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
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

function DeleteShift($shiftID){
    $SQLString = 'DELETE FROM timeclockshifts WHERE shift_id = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$shiftID]);
}
?>