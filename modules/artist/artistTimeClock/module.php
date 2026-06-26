<?php 
/**
 * @module artistTimeClock
 * @name TimeClock
 * @role artist
 * @nav-text Time Clock
 * @nav-icon clock
 * @nav-order 3
 */
include_once __DIR__ . '/../../../libraries/session.php';
include_once __ROOT__ . '/libraries/timeclock/timeclocklib.php';
include_once __ROOT__ . '/libraries/timeclock/timeclock_issets.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php';

RunArtistTimeclockIssets();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="/css/moduleStyle.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="/libraries/timeclock/timeclocklib.js"></script>
<script src="/libraries/timeclock/timeclockAjaxHandlers.js"></script>


<div class="module">
    <div class="module-header">
        <h1>Time Clock</h1>
        <p>Use the button below to clock in or out. If you forget to clock out, an admin can adjust your times. Just remember to tell us the shift ID!</p>
    </div>
    <div class="module-grid">
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-2">
            <h3>Your Stats</h3>
            <?php DisplayArtistStats($_SESSION['username']); ?>
        </div>
        <div class="module-card module-card--span-1">
            <?php echo DisplayArtistClockInOutButton($_SESSION['username']); ?>
        </div>
        <div class="module-card module-card--span-3">
            <center>
                <h1>Your Timeclock Entries</h1>
                <?php GenerateArtistTimeclockTable($_SESSION['username']); ?>
            </center>
        </div>
        <div class="module-card module-card--span-1">
            <center>
                <h1>Your important documents</h1>
                <p><?php ShowArtistFilesForTimeclock(); ?></p>
            </center>
        </div>
    </div>
</div>
