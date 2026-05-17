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
include_once __ROOT__ . '/libraries/timeclock/timeclockLib.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php';

if(isset($_GET['clock_in'])){
    ArtistClockIn($_SESSION['username']);
}
if(isset($_GET['clock_out'])){
    ArtistClockOut($_SESSION['username']);
}
if(isset($_GET['download_file'])){
    InitiateDownload($_GET['download']);
}
?>

<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1>Time Clock</h1>
        <p>Use the button below to clock in or out. If you forget to clock out, an admin can adjust your times. Just remember to tell us the shift ID!</p>
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-1">
            <h3>Your most recent activity was</h3>
        </div>
        <div class="module-card module-card--span-2">
            <h3> Your Stats</h3>
        </div>
        <div class="module-card module-card--span-1">
            <?php echo DisplayArtistClockInOutButton($_SESSION['username']); ?>
        </div>
        <div class="module-card module-card--span-2">
            <center>
                <h1>Your Timeclock Entries</h1>
                <?php GenerateArtistTimeclockTable($_SESSION['username']); ?>
            </center>
        </div>
        <div class="module-card module-card--span-2">
            <center>
                <h1>Your important documents</h1>
                <p><?php ShowArtistFilesForTimeclock(); ?></p>
            </center>
    </div>
    </div>
</div>