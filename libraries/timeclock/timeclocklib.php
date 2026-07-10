<?php
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';
include_once __ROOT__ . '/download.php';
include_once __ROOT__ . '/libraries/logging.php';


function GenerateTimeclockTable(){
    $tz = $_SESSION['timezone'] ?? 'America/Phoenix';
    $entryArray = GetTimeclockEntries();
    echo '<table id="ShiftList" class="display atc-tablecell" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>User</th><th>Time In</th><th>Time Out</th><th>Shift Length</th><th>Notes</th><th>Delete</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        $displayIn = PhoenixToLocal($entry['time_in'], $tz, 'F j, Y g:i A');
        $displayOut = PhoenixToLocal($entry['time_out'], $tz, 'F j, Y g:i A');
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['user']) . '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_in" data-mysql="' . htmlspecialchars($entry['time_in']) . '">';
        echo '  <span class="atc-display">' . htmlspecialchars($displayIn) . '</span>';
        echo '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_out" data-mysql="' . htmlspecialchars($entry['time_out'] ?? '') . '">';
        echo '  <span class="atc-display">' . htmlspecialchars($displayOut ?? '') . '</span>';
        echo '</td>';
        echo '<td>' . DetermineShiftLengthOrSummonButton($entry['time_in'], $entry['time_out'], $entry['shift_id']) . '</td>';
        echo '<td><textarea class="shift-comment" data-shift-id="' . $entry['shift_id'] . '" rows="2">' . htmlspecialchars($entry['shift_comments'] ?? '') . '</textarea></td>';
        echo '<td><a href="index.php?delete_shift_id=' . htmlspecialchars($entry['shift_id']) . '">❌</a></td>';

        echo '</tr>';
    }
    echo '</tbody></table>';
}



function GenerateArtistTimeclockTable($artistID){
    $tz = $_SESSION['timezone'] ?? 'America/Phoenix';
    $entryArray = GetTimeclockEntries(null, null, $artistID);
    echo '<table id="ShiftList" class="display" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>Time In</th><th>Time Out</th><th>Shift Length</th><th>Notes</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        $displayIn = PhoenixToLocal($entry['time_in'], $tz, 'F j, Y g:i A');
        $displayOut = PhoenixToLocal($entry['time_out'], $tz, 'F j, Y g:i A');
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_in" data-mysql="' . htmlspecialchars($entry['time_in']) . '">';
        echo '  <span class="atc-display">' . htmlspecialchars($displayIn) . '</span>';
        echo '</td>';
        echo '<td class="atc-editable" data-shift-id="' . $entry['shift_id'] . '" data-field="time_out" data-mysql="' . htmlspecialchars($entry['time_out'] ?? '') . '">';
        echo '  <span class="atc-display">' . htmlspecialchars($displayOut ?? '') . '</span>';
        echo '</td>';
        echo '<td>' . DetermineArtistShiftLength($entry['time_in'], $entry['time_out']) . '</td>';
        echo '<td><textarea class="shift-comment" data-shift-id="' . $entry['shift_id'] . '" rows="2">' . htmlspecialchars($entry['shift_comments'] ?? '') . '</textarea></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}




function UTCToLocal($utcDatetime, $targetTimezone){
    if($utcDatetime === null || $utcDatetime === '') return null;
    $dt = new DateTime($utcDatetime, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone($targetTimezone));
    return $dt->format('Y-m-d H:i:s');
}
function PhoenixToLocal($phxDatetime, $targetTimezone, $format = 'Y-m-d H:i:s'){
    if($phxDatetime === null || $phxDatetime === '') return null;
    $dt = new DateTime($phxDatetime, new DateTimeZone('America/Phoenix'));
    $dt->setTimezone(new DateTimeZone($targetTimezone));
    return $dt->format($format);
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
    $phx = new DateTimeZone('America/Phoenix');
    $start = new DateTime($timeIn, $phx);
    $end = new DateTime($timeOut, $phx);
    if ($start > $end) return '<span class="temporal-error">TEMPORAL ERROR</span>';
    // In both DetermineShiftLengthOrSummonButton() and DetermineArtistShiftLength()
    $interval = $start->diff($end);
    $parts = [];
    if ($interval->d > 0) $parts[] = $interval->d . ' days';
    $parts[] = $interval->h . ' hours ' . $interval->i . ' minutes';
    return implode(', ', $parts);

}

function DetermineArtistShiftLength($timeIn, $timeOut){
    if($timeOut == ''){
        return 'Currently working...';
    }
    $phx = new DateTimeZone('America/Phoenix');
    $start = new DateTime($timeIn, $phx);
    $end = new DateTime($timeOut, $phx);
    if ($start > $end) return '<span class="temporal-error">TEMPORAL ERROR</span>';

    // In both DetermineShiftLengthOrSummonButton() and DetermineArtistShiftLength()
    $interval = $start->diff($end);
    $parts = [];
    if ($interval->d > 0) $parts[] = $interval->d . ' days';
    $parts[] = $interval->h . ' hours ' . $interval->i . ' minutes';
    return implode(', ', $parts);
}
function ClockEveryoneOut(){
    $SQLString = 'UPDATE timeclockshifts SET time_out = CONVERT_TZ(UTC_TIMESTAMP(), "+00:00", "America/Phoenix") WHERE time_out IS NULL';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    LogSimeckAction('Mass clock-out', 'All artists were clocked out by the system.', 'System');
}

function DeleteShift($shiftID){
    $SQLString = 'DELETE FROM timeclockshifts WHERE shift_id = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$shiftID]);
    LogSimeckAction('Shift deleted', 'A timeclock shift was deleted by an admin.', 'System');
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
    $SQLString = 'INSERT INTO timeclockshifts (user, time_in) VALUES (?, CONVERT_TZ(UTC_TIMESTAMP(), "+00:00", "America/Phoenix"))';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    LogSimeckAction('Clocked in', 'Artist clocked in.', 'System');
    header("Location: index.php");
    exit;
}

function ArtistClockOut($artistID){
    $SQLString = 'UPDATE timeclockshifts SET time_out = CONVERT_TZ(UTC_TIMESTAMP(), "+00:00", "America/Phoenix") WHERE user = ? AND time_out IS NULL';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    LogSimeckAction('Clocked out', 'Artist clocked out.', 'System');
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
function GetArtistShiftDurationSeconds($timeIn, $timeOut){
    $dbTz = new DateTimeZone('America/Phoenix');
    $start = new DateTime($timeIn, $dbTz);
    $end = $timeOut ? new DateTime($timeOut, $dbTz) : new DateTime('now', new DateTimeZone('America/Phoenix'));
    $interval = $start->diff($end);
    return $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
}




function GetArtistStats($artistID){
    $phx = new DateTimeZone('America/Phoenix');
    
    $now = new DateTime('now', $phx);
    
    // Today: 00:00:00 to 23:59:59 in Phoenix
    $todayStart = (clone $now)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    $todayEnd = (clone $now)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    
    // This week (Monday start) in Phoenix
    $dayOfWeek = (int)$now->format('N');
    $monday = (clone $now)->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0);
    $weekStart = $monday->format('Y-m-d H:i:s');
    $weekEnd = (clone $monday)->modify('+6 days')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    
    // Last 2 weeks in Phoenix
    $twoWeeksStart = (clone $now)->modify('-14 days')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    $twoWeeksEnd = $weekEnd;
    
    // This month in Phoenix
    $monthStart = (clone $now)->modify('first day of this month')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    $monthEnd = (clone $now)->modify('last day of this month')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    
    // This year in Phoenix
    $yearStart = (clone $now)->modify('first day of January')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    $yearEnd = (clone $now)->modify('last day of December')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    
    // Get all shifts for the artist
    $SQLString = 'SELECT time_in, time_out FROM timeclockshifts WHERE user = ?';
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'today' => 0,
        'week' => 0,
        'twoWeeks' => 0,
        'month' => 0,
        'year' => 0,
        'lifetime' => 0,
    ];
    
    foreach($shifts as $shift){
        $timeIn = $shift['time_in'];
        $timeOut = $shift['time_out'];
        
        $duration = GetArtistShiftDurationSeconds($timeIn, $timeOut);
        $stats['lifetime'] += $duration;
        
        // Compare Phoenix-stored times against Phoenix boundaries
        if($timeIn >= $todayStart && $timeIn <= $todayEnd) $stats['today'] += $duration;
        if($timeIn >= $weekStart && $timeIn <= $weekEnd) $stats['week'] += $duration;
        if($timeIn >= $twoWeeksStart && $timeIn <= $twoWeeksEnd) $stats['twoWeeks'] += $duration;
        if($timeIn >= $monthStart && $timeIn <= $monthEnd) $stats['month'] += $duration;
        if($timeIn >= $yearStart && $timeIn <= $yearEnd) $stats['year'] += $duration;
    }
    
    return $stats;
}


function FormatDurationHours($totalSeconds){
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    return $hours . 'h ' . $minutes . 'm';
}

function DisplayArtistStats($artistID){
    $stats = GetArtistStats($artistID);
    echo '<div class="stats-grid">';
    echo '<p><strong>Timezone</strong>: ' . ($_SESSION['timezone'] ?? 'UTC') . '</p>';
    echo '<p><strong>Today:</strong> ' . FormatDurationHours($stats['today']) . '</p>';
    echo '<p><strong>This Week:</strong> ' . FormatDurationHours($stats['week']) . '</p>';
    echo '<p><strong>Last 2 Weeks:</strong> ' . FormatDurationHours($stats['twoWeeks']) . '</p>';
    echo '<p><strong>This Month:</strong> ' . FormatDurationHours($stats['month']) . '</p>';
    echo '<p><strong>This Year:</strong> ' . FormatDurationHours($stats['year']) . '</p>';
    echo '<p><strong>Lifetime:</strong> ' . FormatDurationHours($stats['lifetime']) . '</p>';
    echo '</div>';
}
