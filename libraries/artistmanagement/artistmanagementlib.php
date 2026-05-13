<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
function GenerateArtistCards() {
    $artists = GetAllArtists();
    foreach ($artists as $artist) {
        echo '<div class="aam-card aam-card--span-4">';
        echo '<table id="oneArtistTable" class="display aam-tablecell" style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr><th>Username</th><th>Human Name</th><th>Active</th><th>Role</th><th>Set PW</th><th>Upload Document</th><th>Save Changes</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['firstname']) . ' ' . htmlspecialchars($artist['lastname']) . '</td>';
        echo '<td class="aam-tablecell">' . GenerateArtistStatusButton($artist['userID'], $artist['active']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td class="aam-tablecell"><button class="edit-artist-button" data-artist-id="' . $artist['userID'] . '">Reset PW</button></td>';
        echo '<td class="aam-tablecell"><button class="upload-file-button" data-artist-id="' . $artist['userID'] . '">Upload Document</button></td>';
        echo '<td class="aam-tablecell"><button class="save-artist-button" data-artist-id="' . $artist['userID'] . '">Save Changes</button></td>';
        echo '</tr>';
        echo '</tbody></table>';
        echo $artist['firstname'] . '\'s files:<br />';
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
    if($artistID == $_SESSION["userID"]){
        return "This is you!";
    }
    if($isActive){
        return '<a href="?artist_id=' . $artistID . '&new_status=0" class="toggle-artist-status">✅</a>';
    } else {
        return '<a href="?artist_id=' . $artistID . '&new_status=1" class="toggle-artist-status">❌</a>';
    }

}


function ToggleArtistStatus($artistID, $isActive){
    if($isActive == "1"){
        $SQLString = "UPDATE artists SET active = 1 WHERE userID = ?";
    } else {
        $SQLString = "UPDATE artists SET active = 0 WHERE userID = ?";
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistID]);
    RefreshPortal();
}
//
?>