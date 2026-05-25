<?php
//This lib contains functions related to project management including creating projects, and assigning people to projects.
include_once __DIR__ . '/session.php';
include_once __DIR__ . '/db.php';
include_once __ROOT__ . '/libraries/sharedlib.php';

function GenerateProjectCards(){
    $projects = GetAllProjects();
    foreach($projects as $project){
        echo '<div class="module-card module-card--span-1">';
        echo '<table><th> Project ID </th><th> Project Name </th><th> Active </th><th>Toggle Active</th>';
        echo '<tr>';
        echo '<td>'.$project['pid'].'</td>';
        echo '<td>'.$project['project_name'].'</td>';
        echo '<td>'.SummonActivityButton($project['active']).'</td>';
        echo GetToggleButtonText($project['pid'],$project['active'], $project['transitioning']);
        echo '</tr>';
        echo '</table>';
        echo '</div>';
    }
}
function GetAllProjects(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM projects");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetAssignedProjectOptionList(){
    $pdo = DBConnect();
    
    // Get the project_assignments string for the current user
    $stmt = $pdo->prepare("SELECT project_assignments FROM artists WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || empty($result['project_assignments'])) {
        return; // No projects assigned
    }
    
    $projectArr = array_filter(explode(",", $result['project_assignments']));
    if (empty($projectArr)) {
        return;
    }
    
    // Get project names for the assigned PIDs
    $placeholders = implode(",", array_fill(0, count($projectArr), "?"));
    $stmt = $pdo->prepare("SELECT pid, project_name FROM projects WHERE pid IN ($placeholders)");
    $stmt->execute(array_values($projectArr));
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($projects as $p) {
        echo '<option value="'.$p['pid'].'" >'.htmlspecialchars($p['project_name']).'</option>';
    }
}



function ToggleProjectActivation($pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT active FROM projects WHERE pid = ?");
    $stmt->execute([$pid]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if($project){
        $newStatus = $project['active'] ? 0 : 1;
        $updateStmt = $pdo->prepare("UPDATE projects SET active = ?, transitioning = 1  WHERE pid = ?");
        $updateStmt->execute([$newStatus, $pid]);
    }
    RefreshPortal();
}

function GetToggleButtonText($pid, $activestatus, $transitioning){
    $disabledText = $transitioning ? 'disabled' : '';
    $action = $activestatus ? 'archive' : 'unarchive';
    if($transitioning){
        return '<td><button onclick="archiveProject(\''.$pid.'\', \''.$action.'\')" ' . $disabledText . '>' . 'Transitioning...</button></td>';
    } else {
        $buttonText = $activestatus ? 'Archive' : 'Unarchive';
        return '<td><button onclick="archiveProject(\''.$pid.'\', \''.$action.'\')" ' . $disabledText . '>' . $buttonText . '</button></td>';
    }
}
function CreateNewProject($name, $description, $type) {
    // Sanitize: remove all whitespace for folder paths
    $fsName = preg_replace('/\s+/', '', $name);

    $pdo = DBConnect();

    // Determine prefix based on type
    $prefix = ($type === 'internal') ? 'P' : 'C';

    // Find the highest existing number for this prefix
    $stmt = $pdo->prepare("SELECT pid FROM projects WHERE pid LIKE ? ORDER BY pid DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Extract the numeric part and increment
        $num = (int)substr($row['pid'], 1) + 1;
    } else {
        $num = 1;
    }

    // Pad to at least 2 digits (01, 02, ... 10, 11)
    $newPid = $prefix . str_pad($num, 2, '0', STR_PAD_LEFT);

    // Build folder path
    if ($type === 'internal') {
        $activePath = "/files/Projects/internal/{$newPid}_{$name}/";
        $inactiveZipPath = "/files/Projects/internal/archive/{$newPid}_{$fsName}.zip";
    } else {
        $activePath = "/files/Projects/clientProjects/{$newPid}_{$name}/";
        $inactiveZipPath = "/files/Projects/clientProjects/archive/{$newPid}_{$fsName}.zip";
    }

    // Create the folder on disk
    $fullPath = __ROOT__ . $activePath;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0777, true);
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO projects (pid, project_name, active, active_path, inactive_zip_path, transitioning, type, description)
                           VALUES (?, ?, 1, ?, ?, 0, ?, ?)");
    $stmt->execute([$newPid, $name, $activePath, $inactiveZipPath, $type, $description]);

    return $newPid;
}

function GetAllDataForProject($pid){
    $projData = array();
    $commentData = array();
    $artistData = array();
    $clientData = array();
    $pdo = DBConnect();
    $ProjectDataString = "SELECT * FROM projects WHERE pid = ? AND active = 1 AND transitioning = 0";
    $ProjectCommentString = "SELECT * FROM filecomments WHERE parent_file_url LIKE ? ORDER BY created_at DESC";
    $ProjectArtistListString = "SELECT username, first_name, last_name FROM artists WHERE project_assignments LIKE ?";
    $ProjectClientListString = "SELECT email, first_name, last_name FROM clients WHERE project_assignments LIKE ?";

    $projstmt = $pdo->prepare($ProjectDataString);
    $projstmt->execute([$pid]);
    $projData = $projstmt->fetch(PDO::FETCH_ASSOC);

    $commentstmt = $pdo->prepare($ProjectCommentString);
    $commentstmt->execute(['%'.$pid.'%']);
    $commentData = $commentstmt->fetch(PDO::FETCH_ASSOC);

    $artiststmt = $pdo->prepare($ProjectArtistListString);
    $artiststmt->execute(['%'.$pid.'%']);
    $artistData = $artiststmt->fetch(PDO::FETCH_ASSOC);

    $clientstmt = $pdo->prepare($ProjectClientListString);
    $clientstmt->execute(['%'.$pid.'%']);
    $clientData = $clientstmt->fetch(PDO::FETCH_ASSOC);

    return array(
        'project' => $projData,
        'comments' => $commentData,
        'artists' => $artistData,
        'clients' => $clientData
    );
}
function DisplayProjectTeamMembers($artists, $lead){
    if($artists === array()){
        return 'This is where you will see who is on your team.';
    }
    if($artists === ''){
        return 'This project has no assigned artists.';
    }

    foreach($artists as $artist){

        echo '<p>';
        if($lead == $_SESSION['username']){
            echo '⭐';
        }
        echo htmlspecialchars($artist['first_name'] . ' ' . $artist['last_name'] . ' (' . $artist['username'] . ')');
        if($lead == $_SESSION['username'] && $artist['username'] != $_SESSION['username']){
            echo ' <button onclick="removeTeamMember(\''.$artist['username'].'\')">❌</button>';

        }
        echo '</p>';
    }
}

function DisplayProjectClients($clients){

}

function DisplayProjectComments($comments){

}


function DisplayArtistProject($pid){
    //Project info to display:
    //Row 1
    // Left: Project Selector
    // Middle: Assigned team - 1 span - Lead can add and remove team members - 1 span
    // Right: Assigned Clients - 1 span

    //Row 2
    //Half width: All project comments
    //Half width: Project activity feed (automated and manual entries)





    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE pid = ?");
    $stmt->execute([$pid]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if($project){
        echo '<h2>'.htmlspecialchars($project['project_name']).'</h2>';
        echo '<p>'.htmlspecialchars($project['description']).'</p>';
        echo '<p><strong>Status:</strong> ' . ($project['active'] ? 'Active' : 'Inactive') . '</p>';
        echo '<p><strong>Type:</strong> ' . htmlspecialchars($project['type']) . '</p>';
        // Add more project details as needed
    }
}