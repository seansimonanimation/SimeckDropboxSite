<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
function GenerateArtistCards() {
    $artists = GetAllArtists();
    foreach ($artists as $artist) {
        echo '<div class="aam-card aam-card--span-4">';
        echo '<table id="oneArtistTable" class="display" style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr><th>Username</th><th>Human Name</th><th>Active</th><th>Role</th><th>PW Reset</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td>' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td>' . htmlspecialchars($artist['firstname']) . ' ' . htmlspecialchars($artist['lastname']) . '</td>';
        echo '<td>' . GenerateArtistStatusButton($artist['userID'], $artist['active']) . '</td>';
        echo '<td>' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td><button class="edit-artist-button" data-artist-id="' . $artist['userID'] . '">Reset PW</button></td>';
        echo '</tr>';
        echo '</tbody></table>';
        echo '</div>';
    }
}

function GetAllArtists(){
    $SQLString = "SELECT username, firstname, lastname, userID, active, role FROM artists";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function GenerateArtistStatusButton($artistID, $isActive){
    if($isActive){
        return '<button class="toggle-artist-status" data-artist-id="' . $artistID . '" data-new-status="0">✅</button>';
    } else {
        return '<button class="toggle-artist-status" data-artist-id="' . $artistID . '" data-new-status="1">❌</button>';
    }

}
?>