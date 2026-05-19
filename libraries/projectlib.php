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