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


if (isset($_GET['clockout_all'])) {
    ClockEveryoneOut();
}
if(isset($_GET['delete_shift_id'])){
    DeleteShift($_GET['delete_shift_id']);
}
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
function clockArtistOut(shiftId) {
    fetch('libraries/timeclock/clockout.php?shift_id=' + shiftId)
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
}

$(document).ready(function () {
    var table = $('#ShiftList').DataTable({
        "paging": false,
        "info": false,
        "searching": true,
        "order": [[0, "asc"]]
    });

    // Link the custom "Filter by artist" input to the User column
    $('#artistFilter').on('keyup', function () {
        table.search(this.value).draw();
    });
});




</script>



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
                <a href="?clockout_all=1"><img src="globalSiteAssets/big-red-button.png"></a>
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
        <input type="text" id="artistFilter" placeholder="Type artist name..." style="width:90%; padding:8px; border-radius:6px; border:1px solid var(--color-border-bright); background:var(--color-bg-raised); color:var(--color-text); margin-top:8px;">
    </center>
</div>
        <div class="tcm-card tcm-card--span-4">
            <center>
                <h1>Timeclock entries</h1>
                <?php echo GenerateTimeclockTable(); ?>
            </center>
    </div>
</div>