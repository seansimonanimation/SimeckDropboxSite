<?php
include_once __DIR__ .'/../session.php';
include_once __ROOT__ . '/libraries/sharedlib.php';




function GenerateClientCards(){
    if(isset($_GET['searchClient'])){
        $clients = GetSearchedClient($_GET['searchClient']);
    } else {
        $clients = GetAllClients();
    }
    foreach($clients as $client){
        echo '<div class="module-card module-card--span-2">';
        echo '<table>';
        echo '<tr><td>Email</td><td>'.$client['username'].'</td></tr>';
        echo '<tr><td>Name</td><td>'.$client['firstname'].' '.$client['lastname'].'</td></tr>';
        echo '<tr><td>Point Of Contact</td><td>'.GetPoCName($client['point_of_contact']).'</td></tr>';
        echo '<tr><td>Outstanding Balance</td><td>$'.$client['outstandingBalance'].'</td></tr>';
        echo '<tr><td>Active</td><td>'.SummonActivityButton($client['active']).'</td></tr>';
        echo '<tr><td>Toggle Active</td><td>'.GetToggleButtonText($client['username'],$client['active']).'</td></tr>';
        echo '<tr><td>PW Reset</td><td><button onclick="resetClientPassword(\''.$client['username'].'\')">Reset Password</button></td></tr>';
        echo '<tr><td>Assign Project</td><td>Dropdown</td></tr>';
        echo '<tr><td>Current projects</td><td>Dropdown goes here</td></tr>';
        echo '<tr><td>Upload Document</td><td><button class="upload-file-button" data-client-id="'.$client['username'].'">Upload</button></td></tr>';
        echo '<tr><td>Uploaded Documents</td><td>Document list goes here</td></tr>';
        echo '</table>';
        echo '</div>';
    }
}

function GetAllClients(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM clients");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetSearchedClient($searchterm){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ?");
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetToggleButtonText($email, $activestatus){
    $action = $activestatus ? 'deactivate' : 'activate';
    $buttonText = $activestatus ? 'Deactivate' : 'Activate';
    return '<td><button onclick="toggleClientStatus(\''.$email.'\', \''.$action.'\')">' . $buttonText . '</button></td>';
}

function GetAllClientProjectList(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name, pid FROM projects WHERE pid LIKE 'c%'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function CreateNewClient($username, $firstname, $lastname, $PoC, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO clients (username, firstname, lastname, point_of_contact, project_assignments) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $firstname, $lastname, $PoC, $pid]);
    RefreshPortal();
}

function GetPoCName($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if($artist){
        return $artist['firstname'] . ' ' . $artist['lastname'];
    }
    return 'Unknown';
}
function GetAllArtists(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM artists");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}