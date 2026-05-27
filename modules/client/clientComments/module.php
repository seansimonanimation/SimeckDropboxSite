<?php
//The module responsible for dashboard content on the client portal. 
// yep

/**
 * @module clientComments
 * @name Comments
 * @role client
 * @nav-text Comments
 * @nav-icon comment
 * @nav-order 2
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/projectlib.php';


// Handle comment submission BEFORE loading project data
if(!IsReadOnly()){
    if(isset($_POST['submit_dir_comment']) && !empty($_POST['dir_comment_content'])){
        $pdo = DBConnect();
        $orderStmt = $pdo->prepare("SELECT COALESCE(MAX(comment_order), 0) + 1 FROM filecomments WHERE parent_file_url = ?");
        $orderStmt->execute([$_POST['dir_comment_path']]);
        $nextOrder = $orderStmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO filecomments (owner, comment_time, parent_file_url, comment_order, comment_content)
                               VALUES (?, NOW(), ?, ?, ?)");
        $stmt->execute([$_SESSION['username'], $_POST['dir_comment_path'], $nextOrder, $_POST['dir_comment_content']]);
    }
}


if(isset($_POST['See_Project'])){
    $CurrentProjectData = GetAllDataForProject($_POST['See_Project']);
} else {
    $CurrentProjectData = GetAllDataForProject(GetAssignedClientProjectOptionList()[0]['pid']);
}
?>



<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Project Management</h1>
        <br />
    </div>
    <div class="module-grid">
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-2"><Center><h1>Current Project</h1><h3> <?php echo $CurrentProjectData['project']['project_name']; ?></h3> </Center></div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Project Selector</h3>
            </div>
            <div class="module-card__content">
                <form method="post" action="index.php">
                    <label class="module-form-group" style="margin-bottom:12px;">
                        <select name="See_Project" id="project-select" class="module-input" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                                <option value="" disabled selected>Select a project</option>
                                <?php GetAssignedClientProjectOptionListHTML(); ?>
                        </select>
                    </label>
                </form>
            </div>
        </div>
            <div class="module-card module-card--span-1">
                <div class="module-card__header">
                    <h3 class="module-card__title">My Point of Contact</h3>
                </div>
                <div class="module-card__content">
                        <?php echo GetClientPoC($_SESSION['point_of_contact']) ?>
                </div>

            </div>
            <div class="module-card module-card--span-1">
                <div class="module-card__header">
                    <h3 class="module-card__title">Team Members</h3>
                </div>
                <div class="module-card__content">
                    <?php if(empty($CurrentProjectData['project'])): ?>
                        <p>This is where you will be able to see who the rest of your team is!</p>
                    <?php else: ?>
                        <?php echo DisplayProjectTeamMembers($CurrentProjectData['artists'],$CurrentProjectData['project']['leader']); ?>
                    <?php endif; ?>
                </div>

            </div>
                <div class="module-card module-card--span-1">
                <div class="module-card__header">
                    <h3 class="module-card__title">Project Clients</h3>
                </div>
                <div class="module-card__content">
                    <?php if(empty($CurrentProjectData['project'])): ?>
                        <p>This is where you will be able to see the clients assigned to a project!</p>
                    <?php else: ?>
                        <?php echo DisplayProjectClients($CurrentProjectData['clients'], $CurrentProjectData['project']['leader']); ?>
                    <?php endif; ?>
                </div>

            </div>
            <div class="elfinder module-card module-card--span-2">
                <h1> Project Comments</h1>
                <?php if(empty($CurrentProjectData['project'])): ?>
                    <p>This is where you will be able to see comments on the project directory!</p>
                <?php else: ?>
                    <?php echo DisplayProjectDirComments($CurrentProjectData['projectDirComments'], $CurrentProjectData['projectDirLoc']['active_path']); ?>
                <?php endif; ?>
                <?php if(!empty($CurrentProjectData['project'])): ?>
                    <hr style="margin:16px 0;border-color:var(--color-border);">
                    <form method="post" action="index.php">
                        <input type="hidden" name="See_Project" value="<?= htmlspecialchars($_POST['See_Project'] ?? '') ?>">
                        <input type="hidden" name="dir_comment_path" value="<?= htmlspecialchars($CurrentProjectData['projectDirLoc']['active_path']) ?>">
                        <label class="module-form-group">
                            <span style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">Add a comment</span>
                            <textarea name="dir_comment_content" rows="3" class="module-input" placeholder="Type your comment here..." required></textarea>
                        </label>
                        <button type="submit" name="submit_dir_comment" class="module-input" style="margin-top:8px;cursor:pointer;width:auto;padding:6px 18px;">Post Comment</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="module-card module-card--span-2">
                <h1>New Comments</h1>
                <?php if(empty($CurrentProjectData['project'])): ?>
                    <p>This is where you will be able to see the most recent comments in the project.</p>
                <?php else: ?>
                    <?php echo DisplayProjectFileComments($CurrentProjectData['projectFileComments']); ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>




?>
