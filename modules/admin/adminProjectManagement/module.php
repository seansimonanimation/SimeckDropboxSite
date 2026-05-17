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


// Handle project creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $name = trim($_POST['project_name'] ?? '');
    $type = $_POST['project_type'] ?? 'client';
    $description = trim($_POST['project_description'] ?? '');

    if ($name !== '') {
        CreateNewProject($name, $description, $type);
        // Redirect to prevent form re-submission on refresh
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}



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

// Two-sided toggle interaction
document.addEventListener('click', function(e) {
    const toggleOption = e.target.closest('.module-toggle__option');
    if (!toggleOption) return;

    const toggle = toggleOption.closest('.module-toggle');
    // Deactivate all options
    toggle.querySelectorAll('.module-toggle__option').forEach(opt => {
        opt.classList.remove('module-toggle__option--active');
    });
    // Activate clicked option
    toggleOption.classList.add('module-toggle__option--active');

    // Sync the hidden radio buttons
    const value = toggleOption.dataset.value;
    const radio = toggle.querySelector('input[value="' + value + '"]');
    if (radio) radio.checked = true;
});

</script>


<link rel="stylesheet" href="/css/moduleStyle.css" />
<div class="module">
    <div class="module-header">
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-4">
            <h1>Project Management</h1>
            <p>This module allows admins to manage projects.</p> </div>
        <div class="module-card module-card--span-1">Search for Project </div>
        <div class="module-card module-card--span-2"> Stats </div>
        <div class="module-card module-card--span-1">
            
<div class="module-card module-card--span-1">
    <form method="post" class="module-create-form">
        <h1 class="module-form-title"><center>Create New Project</center></h1>

        <!-- Two-sided toggle -->
        <div class="module-toggle" id="projectTypeToggle">
            <input type="radio" name="project_type" id="typeClient" value="client" checked hidden>
            <input type="radio" name="project_type" id="typeInternal" value="internal" hidden>
            <label for="typeClient" class="module-toggle__option module-toggle__option--active" data-value="client">Client Project</label>
            <label for="typeInternal" class="module-toggle__option" data-value="internal">Internal Project</label>
            <div class="module-toggle__slider"></div>
        </div>

        <div class="module-form-group">
            <label for="projectName">Project Name</label>
            <input type="text" name="project_name" id="projectName" required>
        </div>

        <div class="module-form-group">
            <label for="projectDescription">Description</label>
            <textarea name="project_description" id="projectDescription" rows="3"></textarea>
        </div>

        <button type="submit" name="create_project" class="module-btn">Create Project</button>
    </form>
</div>
</div>
        <?php GenerateProjectCards(); ?>
        <input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>