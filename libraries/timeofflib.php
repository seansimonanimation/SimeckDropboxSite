<?php
/**
 * timeofflib.php — Day-off submission and weekly availability rebuilding.
 */
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/session.php';
include_once __DIR__ . '/logging.php';

/**
 * Get the Monday of the current week relative to a timezone.
 * We define "current week" as the Monday-Sunday period containing today.
 * Returns 'Y-m-d' strings for Monday and Sunday.
 */
function GetCurrentWeekBounds($timezone = 'UTC') {
    $tz = new DateTimeZone($timezone);
    $now = new DateTime('now', $tz);
    $dayOfWeek = (int)$now->format('N'); // 1=Monday, 7=Sunday
    // Monday of this week
    $monday = clone $now;
    $monday->modify('-' . ($dayOfWeek - 1) . ' days');
    $monday->setTime(0, 0, 0);
    // Sunday of this week
    $sunday = clone $monday;
    $sunday->modify('+6 days');
    $sunday->setTime(23, 59, 59);
    return [
        'monday' => $monday->format('Y-m-d'),
        'sunday' => $sunday->format('Y-m-d'),
    ];
}

/**
 * Check if a date range overlaps the current week (Monday 00:00 through Sunday 23:59).
 * Returns true if any part of [dateStart, dateEnd] falls within the current week.
 */
function OverlapsCurrentWeek($dateStart, $dateEnd, $timezone = 'UTC') {
    $week = GetCurrentWeekBounds($timezone);
    // If the range starts after Sunday or ends before Monday → no overlap
    if ($dateStart > $week['sunday']) return false;
    if ($dateEnd !== null && $dateEnd < $week['monday']) return false;
    if ($dateEnd === null && $dateStart < $week['monday']) return false; // single day, before this week
    return true;
}

/**
 * Build a time-off bitmask for a single day.
 * Returns an integer with bits set for the blocked-off slots.
 *
 * @param string|null $startTime 'HH:MM' or null (all day)
 * @param string|null $endTime   'HH:MM' or null (all day)
 * @return int
 */
function BuildTimeOffMask($startTime, $endTime) {
    // All day → all 48 bits set
    if ($startTime === null || $endTime === null) {
        return (1 << 48) - 1; // 2^48 - 1 = 281474976710655
    }

    [$sh, $sm] = explode(':', $startTime);
    [$eh, $em] = explode(':', $endTime);
    $startSlot = (int)$sh * 2 + ((int)$sm >= 30 ? 1 : 0);
    $endSlot   = (int)$eh * 2 + ((int)$em >= 30 ? 1 : 0);

    // Clamp to valid range
    if ($startSlot < 0) $startSlot = 0;
    if ($endSlot > 48) $endSlot = 48;
    if ($endSlot <= $startSlot) return 0;

    // Build mask: bits from startSlot to endSlot-1
    return ((1 << $endSlot) - 1) & ~((1 << $startSlot) - 1);
}

/**
 * Adjust availability_this_week for a time-off that overlaps the current week.
 * Sets availability_this_week based on base availability, minus the time-off blocks.
 *
 * @param string $username
 * @param string $dateStart 'Y-m-d'
 * @param string|null $dateEnd 'Y-m-d' or null for single day
 * @param string|null $startTime 'HH:MM' or null for all-day
 * @param string|null $endTime 'HH:MM' or null for all-day
 * @param string $timezone
 * @return bool|string True on success, error string on failure.
 */
function AdjustAvailabilityThisWeek($username, $dateStart, $dateEnd, $startTime, $endTime, $timezone = 'UTC') {
    $pdo = DBConnect();

    // 1. Get base availability
    $stmt = $pdo->prepare("SELECT availability FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $baseAv = $stmt->fetchColumn();
    if ($baseAv === false || $baseAv === null) {
        return 'Artist not found.';
    }

    // Check if base availability is all zeros
    $parts = explode('|', $baseAv);
    if (count($parts) !== 7) {
        return 'Invalid base availability format.';
    }
    $allZero = true;
    foreach ($parts as $p) {
        if ((int)$p !== 0) { $allZero = false; break; }
    }
    if ($allZero) {
        return 'Your base availability is not set. Please set your weekly availability first before requesting time off.';
    }

    // 2. Clone the base availability as a starting point
    $newMask = [];
    for ($d = 0; $d < 7; $d++) {
        $newMask[$d] = (int)$parts[$d];
    }

    // 3. Get current week bounds
    $week = GetCurrentWeekBounds($timezone);
    $weekStart = new DateTime($week['monday']);
    $weekEnd   = new DateTime($week['sunday']);

    // 4. Determine the range of days to process (clamped to this week)
    $rangeStart = new DateTime($dateStart);
    $rangeEnd   = ($dateEnd !== null) ? new DateTime($dateEnd) : clone $rangeStart;

    // Clamp to current week
    if ($rangeStart < $weekStart) $rangeStart = clone $weekStart;
    if ($rangeEnd > $weekEnd)     $rangeEnd   = clone $weekEnd;

    // Build the time-off mask (same for each day in the range)
    $offMask = BuildTimeOffMask($startTime, $endTime);

    // 5. For each day in the clamped range, subtract the time-off
    $current = clone $rangeStart;
    while ($current <= $rangeEnd) {
        $dayOfWeek = (int)$current->format('w'); // 0=Sunday..6=Saturday
        // Remap to our 0=Sunday array index
        $idx = $dayOfWeek;

        // Clear the time-off bits from this day's mask
        $newMask[$idx] = $newMask[$idx] & ~$offMask;

        $current->modify('+1 day');
    }

    // 6. Save to availability_this_week
    $newAvStr = implode('|', $newMask);
    $stmt = $pdo->prepare("UPDATE artists SET availability_this_week = ? WHERE username = ?");
    $stmt->execute([$newAvStr, $username]);
    return true;
}

/**
 * Submit a day-off request.
 *
 * @param string $username
 * @param string $dateStart 'Y-m-d'
 * @param string|null $dateEnd 'Y-m-d' or null for single day
 * @param string|null $startTime 'HH:MM' or null for all-day
 * @param string|null $endTime 'HH:MM' or null for all-day
 * @return bool|string True on success, error string on failure.
 */
function SubmitDayOff($username, $dateStart, $dateEnd, $startTime, $endTime) {
    $pdo = DBConnect();

    // Validate date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart)) {
        return 'Invalid start date format.';
    }
    if ($dateEnd !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
        return 'Invalid end date format.';
    }

    // Validate time formats if provided
    if ($startTime !== null && !preg_match('/^\d{2}:\d{2}$/', $startTime)) {
        return 'Invalid start time format.';
    }
    if ($endTime !== null && !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
        return 'Invalid end time format.';
    }

    // Insert the day-off record
    $stmt = $pdo->prepare(
        "INSERT INTO daysoff (username, date_off_start, date_off_end, start_time, end_time)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$username, $dateStart, $dateEnd, $startTime, $endTime]);

    // Determine if this overlaps the current week
    $artistTz = $_SESSION['timezone'] ?? 'UTC';
    if (OverlapsCurrentWeek($dateStart, $dateEnd, $artistTz)) {
        $result = AdjustAvailabilityThisWeek($username, $dateStart, $dateEnd, $startTime, $endTime, $artistTz);
        if ($result !== true) {
            return $result; // Error message
        }
    }
    // If it's entirely future, the Sunday cronjob will handle it

    LogSimeckAction('Time off requested', "Artist '{$username}' requested time off from {$dateStart}" . ($dateEnd ? " to {$dateEnd}" : '') . ".", 'System');
    return true;
}

/**
 * Rebuild availability_this_week for all active artists based on daysoff records.
 * Called by the Sunday evening cronjob.
 *
 * @param string $timezone Server timezone for week boundary calculation.
 * @return array Summary of results.
 */
function RebuildAvailabilityThisWeek($timezone = 'UTC') {
    $pdo = DBConnect();
    $week = GetCurrentWeekBounds($timezone);
    $results = ['total' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    // Get all active artists
    $artists = $pdo->query("SELECT username, availability FROM artists WHERE active = 1")->fetchAll(PDO::FETCH_ASSOC);
    $results['total'] = count($artists);

    foreach ($artists as $artist) {
        $username = $artist['username'];
        $baseAv = $artist['availability'];

        // Skip if base availability is all zeros (not set)
        $parts = explode('|', $baseAv);
        $allZero = true;
        foreach ($parts as $p) {
            if ((int)$p !== 0) { $allZero = false; break; }
        }
        if ($allZero || count($parts) !== 7) {
            $results['skipped']++;
            LogSimeckAction('Weekly availability rebuild skipped', "Skipped '{$username}': base availability not set.", 'System');
            continue;
        }

        // Start with base availability
        $newMask = [];
        for ($d = 0; $d < 7; $d++) {
            $newMask[$d] = (int)$parts[$d];
        }

        // Query daysoff entries that overlap the current week
        $stmt = $pdo->prepare(
            "SELECT date_off_start, date_off_end, start_time, end_time
             FROM daysoff
             WHERE username = ?
               AND date_off_start <= ?
               AND (date_off_end IS NULL OR date_off_end >= ?)"
        );
        $stmt->execute([$username, $week['sunday'], $week['monday']]);
        $daysoffEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($daysoffEntries as $entry) {
            $rangeStart = new DateTime($entry['date_off_start']);
            $rangeEnd   = ($entry['date_off_end'] !== null) ? new DateTime($entry['date_off_end']) : clone $rangeStart;

            // Clamp to this week
            $weekStart = new DateTime($week['monday']);
            $weekEnd   = new DateTime($week['sunday']);
            if ($rangeStart < $weekStart) $rangeStart = clone $weekStart;
            if ($rangeEnd > $weekEnd)     $rangeEnd   = clone $weekEnd;

            $offMask = BuildTimeOffMask($entry['start_time'], $entry['end_time']);

            $current = clone $rangeStart;
            while ($current <= $rangeEnd) {
                $dayOfWeek = (int)$current->format('w'); // 0=Sunday
                $newMask[$dayOfWeek] = $newMask[$dayOfWeek] & ~$offMask;
                $current->modify('+1 day');
            }
        }

        // Save
        $newAvStr = implode('|', $newMask);
        $stmt = $pdo->prepare("UPDATE artists SET availability_this_week = ? WHERE username = ?");
        $stmt->execute([$newAvStr, $username]);
        $results['updated']++;
    }

    LogSimeckAction('Weekly availability rebuild', "Rebuilt availability for {$results['updated']} artists ({$results['skipped']} skipped).", 'System');
    return $results;
}
/**
 * Returns availability_this_week if it has any non-zero values,
 * otherwise falls back to base availability.
 * A fully-zero availability_this_week means "use base schedule".
 *
 * @param string $baseAv      Base availability string (7 pipe-separated integers)
 * @param string $thisWeekAv  availability_this_week string (7 pipe-separated integers)
 * @return string
 */
function GetEffectiveAvailability($baseAv, $thisWeekAv) {
    $parts = explode('|', $thisWeekAv);
    foreach ($parts as $p) {
        if ((int)$p !== 0) return $thisWeekAv;
    }
    return $baseAv;
}
