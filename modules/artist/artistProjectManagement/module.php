<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistProjectManagement
 * @name ProjectManagement
 * @role artist
 * @nav-text Project Management
 * @nav-icon settings
 * @nav-order 4
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';
include_once __ROOT__ . '/libraries/projectlib.php';


if(isset($_POST['See_Project'])){
    $CurrentProjectData = GetAllDataForProject($_POST['See_Project']);
} else {
    $CurrentProjectData = array(
        'project' => array( 'leader' => null),
        'comments' => array(),
        'artists' => array(),
        'clients' => array()
    );
}



?>



<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Project Management</h1>
        <br />
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Project Selector</h3>
            </div>
            <div class="module-card__content">
                <form method="post" action="index.php">
                    <label class="module-form-group" style="margin-bottom:12px;">
                        <span style="margin-bottom:4px;">Select Project</span>
                        <select name="See_Project" id="project-select" class="module-input" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                                <option value="" disabled selected>Select a project</option>
                                <?php GetAssignedProjectOptionList(); ?>
                        </select>
                    </label>
                </form>
            </div>
        </div>
            <div class="module-card module-card--placeholder"></div>
            <div class="module-card module-card--span-1">
                <div class="module-card__header">
                    <h3 class="module-card__title">Team Members</h3>
                    <?php echo DisplayProjectTeamMembers($CurrentProjectData['artists'],$CurrentProjectData['project']['leader']); ?>
                </div>
                <div class="module-card__content"></div>
            </div>
                        <div class="module-card module-card--span-1">
                <div class="module-card__header">
                    <h3 class="module-card__title">Project Clients</h3>
                    <?php echo DisplayProjectClients($CurrentProjectData['clients']); ?>
                </div>
                <div class="module-card__content"></div>
            </div>
            <div class="module-card module-card--placeholder"></div>
            <div class="module-card module-card--placeholder"></div>
            <div class="module-card module-card--span-2">
                <h1>Project Comments</h1>
                <?php echo DisplayProjectComments($CurrentProjectData['comments']); ?>
            </div>
        </div>
    </div>
</div>