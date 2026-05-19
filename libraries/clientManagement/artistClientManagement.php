<?php
//This library contains functions related to an artist managing their clients.

include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/sharedlib.php';



function GenerateArtistClientCards($artist){
    if(isset($_GET['searchClient'])){
        $clients = GetSearchedArtistClient($_GET['searchClient']);
    } else {
        $clients = GetAllArtistClients($artist);
    }
    foreach($clients as $client){

    echo '<div class="module-card module-card--span-1">';
        echo '<table>';
        echo '<tr><td>Email</td><td>'.$client['email'].'</td></tr>';
        echo '<tr><td>Name</td><td>'.$client['firstname'].' '.$client['lastname'].'</td></tr>';
        echo '<tr><td>PW Reset</td><td><button onclick="resetClientPassword(\''.$client['email'].'\')">Reset Password</button></td></tr>';
        echo '<tr><td>Current projects: <br />' . GetClientProjects($client['email'], $client['project_assignments']) . '</td></tr>';
        echo '</table>';
    echo '</div>';
    }
}
function GetAllArtistClients($artist){
    $SQLString = "SELECT * FROM clients WHERE point_of_contact = ? AND active = 1";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artist]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetSearchedArtistClient($searchterm){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email LIKE ? OR firstname LIKE ? OR lastname LIKE ? AND active = 1 AND point_of_contact = ?");
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm, $_SESSION['username']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetClientProjects($clientEmail, $clientProjects){
    $clientProjArr = explode(',', $clientProjects);
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name, pid from projects");
    $stmt->execute();
    $allProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $projectString = "";
    foreach($clientProjArr as $project){
        foreach($allProjects as $proj){
            if($proj['pid'] == $project){
                $projectString .= $proj['project_name'] . '<br />';
            }
        }
    }
    return $projectString;
}