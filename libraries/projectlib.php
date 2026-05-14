<?php
//This lib contains functions related to project management including creating projects, and assigning people to projects.
include_once __DIR__ . '/session.php';
include_once __DIR__ . '/db.php';

function GenerateProjectCards(){
    $projects = GetAllProjects();
    foreach($projects as $project){
        echo '<div class="apm-card apm-card--span-1">';
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

function SummonActivityButton($isActive){
    if($isActive){
        return '<span style="color:green; font-weight:bold">✅</span>';
    } else {
        return '<span style="color:red; font-weight:bold">❌</span>';
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

?>