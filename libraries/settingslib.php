<?php
function ArtistSettingsErrorDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ArtistSettingsSuccessDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ClientSettingsErrorDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ClientSettingsSuccessDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function GetClientLockOverrideCount(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare('SELECT lock_overrides FROM clients WHERE username = ?');
    $stmt->execute([$_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['lock_overrides'] : 0;
}