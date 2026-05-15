<?php





function GenerateClientCards(){
    $clients = GetAllClients();
    foreach($clients as $client){
        echo '<div class="acm-card acm-card--span-1">';
        echo '<table>';
        echo '<tr><td>Email</td><td>'.$client['email'].'</td></tr>';
        echo '<tr><td>Name</td><td>'.$client['firstname'].' '.$client['lastname'].'</td></tr>';
        echo '<tr><td>Outstanding Balance</td><td>$'.$client['outstandingBalance'].'</td></tr>';
        echo '<tr><td>Active</td><td>'.SummonActivityButton($client['active']).'</td></tr>';
        echo '<tr><td>Toggle Active</td><td>'.GetToggleButtonText($client['email'],$client['active']).'</td></tr>';
        echo '<tr><td>PW Reset</td><td><button onclick="resetClientPassword(\''.$client['email'].'\')">Reset Password</button></td></tr>';
        echo '<tr><td>Assign Project</td><td>[button goes here]</td></tr>';
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

function SummonActivityButton($isActive){
    if($isActive){
        return '<span style="color:green; font-weight:bold">✅</span>';
    } else {
        return '<span style="color:red; font-weight:bold">❌</span>';
    }
}

function GetToggleButtonText($email, $activestatus){
    $action = $activestatus ? 'deactivate' : 'activate';
    $buttonText = $activestatus ? 'Deactivate' : 'Activate';
    return '<td><button onclick="toggleClientStatus(\''.$email.'\', \''.$action.'\')">' . $buttonText . '</button></td>';
}

function GetAllClientProjectList(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name FROM projects WHERE pid LIKE 'c%'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function CreateNewClient($email, $firstname, $lastname){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO clients (email, firstname, lastname) VALUES (?, ?, ?)");
    $stmt->execute([$email, $firstname, $lastname]);
    RefreshPortal();
}
?>