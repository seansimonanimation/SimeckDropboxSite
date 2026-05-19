<?php
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';
include_once __ROOT__ . '/download.php';



function GenerateTimeclockTable(){
    $entryArray = GetTimeclockEntries();
    echo '<table id="ShiftList" class="display atc-tablecell" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>User</th><th>Time In</th><th>Time Out</th><th>Shift Length</th><th>Delete</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['user']) . '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_in">';
        echo '  <span class="atc-display">' . htmlspecialchars($entry['time_in']) . '</span>';
        echo '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_out">';
        echo '  <span class="atc-display">' . htmlspecialchars($entry['time_out'] ?? '') . '</span>';
        echo '</td>';
        echo '<td>' . DetermineShiftLengthOrSummonButton($entry['time_in'], $entry['time_out'], $entry['shift_id']) . '</td>';
        echo '<td><a href="index.php?delete_shift_id=' . htmlspecialchars($entry['shift_id']) . '">❌</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function GenerateArtistTimeclockTable($artistID){
    $entryArray = GetTimeclockEntries(null, null, $artistID);
    echo '<table id="ShiftList" class="display" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>Time In</th><th>Time Out</th><th>Shift Length</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['time_in']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['time_out'] == NULL ? '' : $entry['time_out']) . '</td>';
        echo '<td>' . DetermineArtistShiftLength($entry['time_in'], $entry['time_out']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}


function GetClockedInArtistCount(){
    $SQLString = 'SELECT COUNT(*) as clocked_in_count FROM timeclockshifts WHERE time_out IS NULL';
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

function DetermineArtistShiftLength($timeIn, $timeOut){
    if($timeOut == ''){
        return 'Currently working...';
    }
    $start = new DateTime($timeIn);
    $end = new DateTime($timeOut); // Current time
    $interval = $start->diff($end);
    return $interval->format('%h hours %i minutes');
}
function ClockEveryoneOut(){
    $SQLString = 'UPDATE timeclockshifts SET time_out = NOW() WHERE time_out IS NULL';
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

function DisplayArtistClockInOutButton($artistID){
    $SQLString = 'SELECT * FROM timeclockshifts WHERE user = ? AND time_out IS NULL ORDER BY time_in DESC LIMIT 1';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    $currentShift = $stmt->fetch(PDO::FETCH_ASSOC);

    if($currentShift){
        return '<center><h1>Clock Out</h1><a href="?clock_out=1" id="clockButton"><img src="/globalSiteAssets/clockOut_button.png" alt="Clock Out" /></a></center>';
    } else {
        return '<center><h1>Clock In</h1><a href="?clock_in=1" id="clockButton"><img src="/globalSiteAssets/clockIn_button.png" alt="Clock In" /></a></center>';
    }
}

function ArtistClockIn($artistID){
    $SQLString = 'INSERT INTO timeclockshifts (user, time_in) VALUES (?, NOW())';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    header("Location: index.php");
    exit;
}

function ArtistClockOut($artistID){
    $SQLString = 'UPDATE timeclockshifts SET time_out = NOW() WHERE user = ? AND time_out IS NULL';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    header("Location: index.php");
    exit;
}

function ShowArtistFilesForTimeclock(){
    //This is a place where artist can view things like important tax documents, contracts, and other files related to their work at Simeck Entertainment. This is a future feature that we will be adding, but for now it just returns a placeholder message.
    $SQLString = 'SELECT * from artistdocuments where owner = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$_SESSION['username']]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach($files as $file){
        $b64 = Generateb64EncodedDownloadLink($_SESSION['username'], $file['uploadID']);
        echo '<p><a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($file['filepath'])) . '</a></p>';
    }
}