<?php
//This lib contains functions related to project management including creating projects, and assigning people to projects.
include_once __DIR__ . '/session.php';
include_once __DIR__ . '/db.php';
include_once __ROOT__ . '/libraries/sharedlib.php';
include_once __ROOT__ . '/libraries/helpers.php';
include_once __ROOT__ . '/libraries/logging.php';

function GenerateProjectCards(){
    $projects = GetAllProjects();
    foreach($projects as $project){
        $pid = $project['pid'];
        $type = $project['type'];
        $currentLead = $project['leader'];
        $sizeBytes = $project['size_on_disk'] ?? 0;
        $sizeMB = $sizeBytes ? round($sizeBytes / 1048576, 2) . ' MB' : 'Calculating...';

        echo '<div class="module-card module-card--span-4" data-pid="' . htmlspecialchars($pid) . '">';
        // Row 1: Project Title
        echo '<div class="proj-card__title-row">';
        echo '<h2 class="proj-card__title">' . htmlspecialchars($project['project_name']) . '</h2>';
        echo '</div>';
        // Row 2: Data Columns
        echo '<div class="proj-card__data-row">';

        // Column 1: Project Type
        echo '<div class="proj-card__col">';
        echo '<span class="proj-card__col-label">Type</span>';
        echo '<span class="proj-card__col-value">' . htmlspecialchars(ucfirst($type)) . '</span>';
        echo '</div>';

        // Column 2: Project ID
        echo '<div class="proj-card__col">';
        echo '<span class="proj-card__col-label">Project ID</span>';
        echo '<span class="proj-card__col-value">' . htmlspecialchars($pid) . '</span>';
        echo '</div>';

        // Column 3: Active (text only, not clickable)
        echo '<div class="proj-card__col">';
        echo '<span class="proj-card__col-label">Active</span>';
        echo '<span class="proj-card__col-value">' . SummonActivityButton($project['active']) . '</span>';
        echo '</div>';

        // Column 4: Project Lead Dropdown
        echo '<div class="proj-card__col">';
        echo '<span class="proj-card__col-label">Project Lead</span>';
        echo GetProjectLeadDropdown($pid, $type, $currentLead);
        echo '</div>';

        // Column 5: Size On Disk
        echo '<div class="proj-card__col">';
        echo '<span class="proj-card__col-label">Size On Disk</span>';
        echo '<span class="proj-card__col-value proj-card__size" data-pid="' . htmlspecialchars($pid) . '">' . $sizeMB . '</span>';
        echo '</div>';

        echo '</div>'; // end data-row
        echo '</div>'; // end card
    }
}
function GetProjectLeadDropdown($pid, $type, $currentLead){
    if ($type === 'internal') {
        $people = ListAllActiveArtists();
    } else {
        $people = ListAllActiveClients();
    }

    $html = '<select class="proj-card__dropdown proj-lead-select" data-pid="' . htmlspecialchars($pid) . '">';
    $html .= '<option value="">— None —</option>';
    foreach ($people as $person) {
        $selected = ($person['username'] === $currentLead) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($person['username']) . '"' . $selected . '>';
        $html .= htmlspecialchars($person['firstname'] . ' ' . $person['lastname'] . ' (' . $person['username'] . ')');
        $html .= '</option>';
    }
    $html .= '</select>';
    return $html;
}
function UpdateProjectLead($pid, $newLead){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE projects SET leader = ? WHERE pid = ?");
    LogSimeckAction('Project lead updated', "Project with PID {$pid} has a new lead: " . ($newLead ?: 'None'), $pid);
    return $stmt->execute([$newLead ?: null, $pid]);

}
function GetProjectFolderSize($pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT active_path FROM projects WHERE pid = ?");
    $stmt->execute([$pid]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project || empty($project['active_path'])) {
        return 0;
    }

    $fullPath = __ROOT__ . $project['active_path'];
    if (!is_dir($fullPath)) {
        return 0;
    }

    $totalSize = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $totalSize += $file->getSize();
    }
    return $totalSize;
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
    $stmt = $pdo->prepare("SELECT pid, project_name FROM projects WHERE pid IN ($placeholders) AND active = 1 AND transitioning = 0");
    $stmt->execute(array_values($projectArr));
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($projects as $p) {
        echo '<option value="'.$p['pid'].'" >'.htmlspecialchars($p['project_name']).'</option>';
    }
}



function GetAssignedClientProjectOptionList(){
    $pdo = DBConnect();
    // Get the project_assignments string for the current user
    $stmt = $pdo->prepare("SELECT project_assignments FROM clients WHERE username = ?");
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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}
function GetAssignedArtistProjectOptionList(){
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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}


function GetAssignedArtistProjectOptionListHTML(){
    $projects = GetAssignedArtistProjectOptionList();
    foreach ($projects as $p) {
        echo '<option value="'.$p['pid'].'" >'.htmlspecialchars($p['project_name']).'</option>';
    }
}
function GetAssignedClientProjectOptionListHTML(){
    $projects = GetAssignedClientProjectOptionList();
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
    LogSimeckAction('Project activation toggled', "Project with PID {$pid} was " . ($newStatus ? 'activated' : 'deactivated') . " and is now transitioning.", $pid);
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
    $fsName = preg_replace('/\s+/', '_', $name);

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
        $activePath = "/files/Projects/internal/{$newPid}_{$fsName}";
        $inactiveZipPath = "/files/Projects/internal/archive/{$newPid}_{$fsName}.zip";
    } else {
        $activePath = "/files/Projects/clientProjects/{$newPid}_{$fsName}";
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
    LogSimeckAction('Project created', "Project '{$name}' with PID {$newPid} was created.", 'System');
    return $newPid;
    
}

function GetAllDataForProject($pid){
    $projData = array();
    $commentData = array();
    $artistData = array();
    $clientData = array();
    $projectDirData = array();
    $pdo = DBConnect();
    $ProjectDataString = "SELECT * FROM projects WHERE pid = ? AND active = 1 AND transitioning = 0";
    $ProjectFileCommentsString = "SELECT * FROM filecomments WHERE parent_file_url LIKE ? AND parent_file_url != ? ORDER BY comment_time DESC";
    $ProjectArtistListString = "SELECT username, firstname, lastname FROM artists WHERE CONCAT(',', project_assignments, ',') LIKE CONCAT('%,', ?, ',')";
    $ProjectClientListString = "SELECT username, firstname, lastname FROM clients WHERE CONCAT(',', project_assignments, ',') LIKE CONCAT('%,', ?, ',')";
    $projectDirLocString = "SELECT active_path FROM projects WHERE pid = ? AND active = 1 AND transitioning = 0";
    $ProjectDirCommentString = "SELECT * FROM filecomments WHERE parent_file_url = ? ORDER BY comment_time ASC";

    $projectDirLocStmt = $pdo->prepare($projectDirLocString);
    $projectDirLocStmt->execute([$pid]);
    $projectDirLocData = $projectDirLocStmt->fetch(PDO::FETCH_ASSOC);

    $projstmt = $pdo->prepare($ProjectDataString);
    $projstmt->execute([$pid]);
    $projData = $projstmt->fetch(PDO::FETCH_ASSOC);

    $artiststmt = $pdo->prepare($ProjectArtistListString);
    $artiststmt->execute([$pid]);
    $artistData = $artiststmt->fetchAll(PDO::FETCH_ASSOC);

    $clientstmt = $pdo->prepare($ProjectClientListString);
    $clientstmt->execute([$pid]);
    $clientData = $clientstmt->fetchAll(PDO::FETCH_ASSOC);

    // Move this check BEFORE accessing $projectDirLocData['active_path']
    if ($projectDirLocData === false || $projData === false) {
        return array(
            'project' => array(),
            'projectFileComments' => array(),
            'artists' => $artistData,
            'clients' => $clientData,
            'projectDirLoc' => array('active_path' => ''),
            'projectDirComments' => array()
        );
    }

    // These lines now execute safely because we know $projectDirLocData is not false
    $projectFileCommentstmt = $pdo->prepare($ProjectFileCommentsString);
    $projectFileCommentstmt->execute([$projectDirLocData['active_path'] . '/%', $projectDirLocData['active_path']]);
    $projectFileCommentData = $projectFileCommentstmt->fetchAll(PDO::FETCH_ASSOC);

    $ProjectDirCommentstmt = $pdo->prepare($ProjectDirCommentString);
    $ProjectDirCommentstmt->execute([$projectDirLocData['active_path']]);
    $projectDirComments = $ProjectDirCommentstmt->fetchAll(PDO::FETCH_ASSOC);

    return array(
        'project' => $projData,
        'projectFileComments' => $projectFileCommentData,
        'artists' => $artistData,
        'clients' => $clientData,
        'projectDirLoc' => $projectDirLocData,
        'projectDirComments' => $projectDirComments
    );
}

function DisplayProjectTeamMembers($artists, $lead){
    if(empty($artists)){
        return 'This project has no assigned artists.';
    }

    foreach($artists as $artist){
        echo '<p>';

        if($lead === $artist['username']){
            echo '⭐';
        }
        $isAdmin = ($_SESSION['role'] === 'admin');
        echo htmlspecialchars($artist['firstname'] . ' ' . $artist['lastname'] . ' (' . $artist['username'] . ')');
        if(($isAdmin || $lead === $_SESSION['username']) && $artist['username'] !== $_SESSION['username']){
            echo ' <button onclick="removeTeamMember(\''.$artist['username'].'\')">❌</button>';
        }
        echo '</p>';
    }
}

function GetClientPoC($poc){
    if (empty($poc)) {
        return 'No client point of contact assigned.';
    }
    $SQLstring = "SELECT firstname, lastname FROM artists WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLstring);
    $stmt->execute([$poc]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return htmlspecialchars($result['firstname'] . ' ' . $result['lastname']);
}


function DisplayProjectDirComments($comments, $projectPath){
    if(empty($comments)){
        echo '<p>No comments on the project directory yet.</p>';
        return;
    }
    
    echo '<table class="module-tablecell" style="width:100%;border-collapse:collapse;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Author</th>';
    echo '<th>Date / Time</th>';
    echo '<th>Comment</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($comments as $comment){
        echo '<tr>';
        echo '<td>'.htmlspecialchars(GetUserDisplayName($comment['owner'])).'</td>';
        echo '<td style="white-space:nowrap;">'.htmlspecialchars($comment['comment_time']).'</td>';
        echo '<td>'.htmlspecialchars($comment['comment_content']).'</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

function GetUserDisplayName($username) {
    $pdo = DBConnect();
    // Check artists first
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['firstname'] . ' ' . $result['lastname'];
    }
    // Check clients
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM clients WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['firstname'] . ' ' . $result['lastname'];
    }
    // Fallback to username
    return $username;
}

function DisplayProjectFileComments($comments){
    if(empty($comments)){
        return '<p>This project has no file comments yet.</p>';
    }
    echo '<table class="module-tablecell" style="width:100%;border-collapse:collapse;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Author</th>';
    echo '<th>Date / Time</th>';
    echo '<th>File</th>';
    echo '<th>Comment</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($comments as $comment){
        // Extract just the filename from the full path
        $filename = basename($comment['parent_file_url']);
        echo '<tr>';
        echo '<td>'.htmlspecialchars(GetUserDisplayName($comment['owner'])).'</td>';
        echo '<td style="white-space:nowrap;">'.htmlspecialchars($comment['comment_time']).'</td>';
        echo '<td>'.htmlspecialchars($filename).'</td>';
        echo '<td>'.htmlspecialchars($comment['comment_content']).'</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}


function DisplayProjectClients($clients, $lead){
    if(empty($clients)){
        return 'This project has no assigned clients.';
    }
    $isAdmin = ($_SESSION['role'] === 'admin');
    $isclient = ($_SESSION['role'] === 'client');
    $isLead = ($lead === $_SESSION['username']); // Check if the current user is the lead
    
    foreach($clients as $client){
        echo '<p>';
        echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname'] . ' (' . $client['username'] . ')');
        if($isAdmin || $isLead && !$isclient){
            echo ' <button onclick="removeClient(\''.$client['username'].'\')">❌</button>';
        }
        echo '</p>';
    }
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