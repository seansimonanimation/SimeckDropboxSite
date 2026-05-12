<?php 
/**
 * @module adminTimeclockManagement
 * @name TimeclockManagement
 * @role admin
 * @nav-text Timeclock Management
 * @nav-icon clock
 * @nav-order 10
 */


include_once __DIR__ . '/../../../libraries/session.php';
include_once __ROOT__ . '/libraries/timeclock/timeclockLib.php';
include_once __ROOT__ . '/libraries/db.php';

?>
<script>
function clockArtistOut(shiftId) {
    fetch('libraries/timeclock/clockout.php?shift_id=' + shiftId)
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
}</script>



<link rel="stylesheet" href="/modules/admin/adminTimeclockManagement/moduleStyle.css" />
<div class="admin-timeclock-management">
    <div class="timeclock-header">
        <h1>Timeclock Management</h1>
        <p>This module allows admins to manage artist timeclocks, including viewing clock-in/out times, editing entries, and generating reports.</p>
    </div>
    <div class="tcm-grid">
        <div class="tcm-card tcm-card--span-1">
            <center>
                <h3>Number of artists currently clocked in</h3>
                <p><h1><?php echo GetClockedInArtistCount(); ?></h1></p>
            </center>
        </div>
        <div class="tcm-card tcm-card--span-1">
            <center>
                <h3>Clock everyone out</h3>
                <a href=<?php ClockEveryoneOut(); ?>><img src="globalSiteAssets/big-red-button.png"></a>
            </center>
        </div>
            <div class="tcm-card tcm-card--span-1">
            <center>
                <h3>Limit view to date range</h3>
            </center>
        </div>
                    <div class="tcm-card tcm-card--span-1">
            <center>
                <h3>Filter by artist</h3>
            </center>
        </div>
        <div class="tcm-card tcm-card--span-4">
            <center>
                <h1>Timeclock entries</h1>
                <?php echo GenerateTimeclockTable(); ?>
            </center>
    </div>
</div>