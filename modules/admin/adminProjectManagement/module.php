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
    Helpers.post('/libraries/archive_project.php', {
        pid: pid,
        action: action
    }).then(function(data) {
        if (data.status === 'started') {
            pollTransitionStatus(pid);
        }
    });
}

function pollTransitionStatus(pid) {
    var interval = setInterval(function() {
        Helpers.get('/libraries/check_transition.php', { pid: pid })
        .then(function(data) {
            if (data.transitioning == 0) {
                clearInterval(interval);
                location.reload();
            }
        });
    }, 3000);
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
// ── Project Lead Dropdown: Immediate Save ──
document.addEventListener('change', function(e) {
    const dropdown = e.target.closest('.proj-lead-select');
    if (!dropdown) return;

    const pid = dropdown.dataset.pid;
    const newLead = dropdown.value;

    Helpers.post('/modules/admin/adminProjectManagement/endpoint.php', {
        action: 'update_lead',
        pid: pid,
        lead: newLead
    }).then(function(data) {
        if (data.success) {
            dropdown.style.borderColor = '#4caf50';
            setTimeout(function() { dropdown.style.borderColor = ''; }, 1500);
        } else {
            console.error('Failed to update project lead:', data.error);
        }
    }).catch(function(err) {
        console.error('Error updating project lead:', err);
    });
});

// ── Size On Disk: Async Recalculation ──
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.proj-card__size').forEach(function(el) {
        var pid = el.dataset.pid;
        if (!pid) return;
        Helpers.get('/libraries/update_project_size.php', { pid: pid })
        .then(function(data) {
            if (data.size_mb !== undefined) {
                el.textContent = data.size_mb + ' MB';
            }
        }).catch(function(err) {
            console.error('Error fetching project size:', err);
        });
    });
});

</script>


<link rel="stylesheet" href="/css/moduleStyle.css" />
<div class="module">
    <div class="module-header">
    </div>
    <div class="module-grid">
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-1">
            <center><h1>Project Management</h1>
            <p>This module allows admins to manage projects.</p></center> 
        </div>
        <div class="module-card module-card--placeholder"></div>
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

    <?php GenerateProjectCards(); ?>
</div>
<input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />

