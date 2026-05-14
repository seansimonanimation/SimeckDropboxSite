<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module adminProjectManagement
 * @name ProjectManagement
 * @role admin
 * @nav-text Project Management
 * @nav-icon settings
 * @nav-order 70
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/projectlib.php';





?>
<script>
function archiveProject(pid, action) {
    fetch('/libraries/archive_project.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'pid=' + pid + '&action=' + action
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'started') {
            // Button already shows "Transitioning..." from DB update
            // Start polling for completion
            pollTransitionStatus(pid);
        }
    });
}

function pollTransitionStatus(pid) {
    const interval = setInterval(() => {
        fetch('/libraries/check_transition.php?pid=' + pid)
        .then(r => r.json())
        .then(data => {
            if (data.transitioning == 0) {
                clearInterval(interval);
                location.reload(); // Refresh the page to show updated state
            }
        });
    }, 3000); // Poll every 3 seconds
}
</script>


<link rel="stylesheet" href="/modules/admin/adminProjectManagement/moduleStyle.css" />
<div class="admin-apm">
    <div class="apm-header">
    </div>
    <div class="apm-grid">
        <div class="apm-card apm-card--span-4">
            <h1>Project Management</h1>
            <p>This module allows admins to manage projects.</p> </div>
        <div class="apm-card apm-card--span-1">Search for Project </div>
        <div class="apm-card apm-card--span-2"> Stats </div>
        <div class="apm-card apm-card--span-1"> Create new project </div>
        <?php GenerateProjectCards(); ?>
        <input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>