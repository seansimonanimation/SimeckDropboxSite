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
    $tz = $_SESSION['timezone'] ?? 'UTC';
    $entryArray = GetTimeclockEntries(null, null, $artistID);
    echo '<table id="ShiftList" class="display" style="width:100%; border-collapse: collapse;">';
    echo '<thead><tr><th>Shift ID</th><th>Time In</th><th>Time Out</th><th>Shift Length</th></tr></thead>';
    echo '<tbody>';
    foreach($entryArray as $entry){
        $localIn = UTCToLocal($entry['time_in'], $tz);
        $localOut = UTCToLocal($entry['time_out'], $tz);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['shift_id']) . '</td>';
        echo '<td>' . htmlspecialchars($localIn) . '</td>';
        echo '<td>' . htmlspecialchars($localOut ?? '') . '</td>';
        echo '<td>' . DetermineArtistShiftLength($entry['time_in'], $entry['time_out']) . '</td>';
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
function GetArtistShiftDurationSeconds($timeIn, $timeOut){
    $start = new DateTime($timeIn, new DateTimeZone('UTC'));
    $end = $timeOut ? new DateTime($timeOut, new DateTimeZone('UTC')) : new DateTime('now', new DateTimeZone('UTC'));
    $interval = $start->diff($end);
    return $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
}


function GetArtistStats($artistID){
    $tz = $_SESSION['timezone'] ?? 'UTC';
    $userTz = new DateTimeZone($tz);
    $utc = new DateTimeZone('UTC');
    
    $now = new DateTime('now', $userTz);
    
    // Today: 00:00:00 to 23:59:59
    $todayStart = (clone $now)->setTime(0, 0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
    $todayEnd = (clone $now)->setTime(23, 59, 59)->setTimezone($utc)->format('Y-m-d H:i:s');
    
    // This week (Monday start)
    $dayOfWeek = (int)$now->format('N'); // 1=Monday, 7=Sunday
    $monday = (clone $now)->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0);
    $weekStart = (clone $monday)->setTimezone($utc)->format('Y-m-d H:i:s');
    $weekEnd = (clone $monday)->modify('+6 days')->setTime(23, 59, 59)->setTimezone($utc)->format('Y-m-d H:i:s');
    
    // Last 2 weeks
    $twoWeeksStart = (clone $now)->modify('-14 days')->setTime(0, 0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
    $twoWeeksEnd = $weekEnd;
    
    // This month
    $monthStart = (clone $now)->modify('first day of this month')->setTime(0, 0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
    $monthEnd = (clone $now)->modify('last day of this month')->setTime(23, 59, 59)->setTimezone($utc)->format('Y-m-d H:i:s');
    
    // This year
    $yearStart = (clone $now)->modify('first day of January')->setTime(0, 0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
    $yearEnd = (clone $now)->modify('last day of December')->setTime(23, 59, 59)->setTimezone($utc)->format('Y-m-d H:i:s');
    
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
        
        // Check which periods this shift falls into
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
    echo '<p><strong>Today:</strong> ' . FormatDurationHours($stats['today']) . '</p>';
    echo '<p><strong>This Week:</strong> ' . FormatDurationHours($stats['week']) . '</p>';
    echo '<p><strong>Last 2 Weeks:</strong> ' . FormatDurationHours($stats['twoWeeks']) . '</p>';
    echo '<p><strong>This Month:</strong> ' . FormatDurationHours($stats['month']) . '</p>';
    echo '<p><strong>This Year:</strong> ' . FormatDurationHours($stats['year']) . '</p>';
    echo '<p><strong>Lifetime:</strong> ' . FormatDurationHours($stats['lifetime']) . '</p>';
    echo '</div>';
}
