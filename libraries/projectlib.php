<?php
//This lib contains functions related to project management including creating projects, and assigning people to projects.

function GenerateProjectCards(){
    $projects = GetAllProjects();
    foreach($projects as $project){
        echo '<div class="apm-card apm-card--span-1">';
        echo '<h3>'.$project['project_name'].'</h3>';
        echo '<p>Project ID: '.$project['project_id'].'</p>';
        echo '</div>';
    }
}
function GetAllProjects(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM projects");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>