<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
function GenerateArtistCards() {
    $artists = GetAllArtists();
    foreach ($artists as $artist) {
        echo '<div class="aam-card aam-card--span-4">';
        echo '<table id="oneArtistTable" class="display aam-tablecell" style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr><th>Username</th><th>Human Name</th><th>Active</th><th>Role</th><th>Reset PW</th><th>Upload Document</th><th>Save Changes</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['firstname']) . ' ' . htmlspecialchars($artist['lastname']) . '</td>';
        echo '<td class="aam-tablecell">' . GenerateArtistStatusButton($artist['userID'], $artist['active']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td class="aam-tablecell"><button class="edit-artist-button" onclick="location.href=\'?reset_pw_for=' . $artist['username'] . '\'">Reset PW</button></td>';
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

function ResetArtistPassword($username){
    $defaultPW = '$2y$10$zMKhZyXxiuVI4MhnboAkNeMCCDZU29.FsvF23zFInKalm5eTn5jZS'; // This is the hash for "SimeckArtist01".
    $SQLString = "UPDATE artists SET password = ? WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$defaultPW,$username]);
    RefreshPortal();
}

function UploadArtistDocument($artistName, $file){
    $owner = $artistName;
    $folder_path = __ROOT__ . '/files/Corporate/ArtistDocuments/' . $artistName . '/';
    $file_path = $folder_path . $file['name'];
    $uploaded_by = $_SESSION['username'];
    $upload_time = date('Y-m-d H:i:s');
    // This function would handle the file upload logic, including moving the uploaded file to a secure location and updating the database with the file information.
    // For security reasons, you should implement proper validation and sanitization of the uploaded file, as well as error handling.
    
    //Ensure that the user directory exists, and if not, create it.
    $dir = dirname($folder_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    // Failed to move file — disk full? permissions?
    return false;
    }
    //Insert the DB record now.
    $SQLString = "INSERT INTO artistdocuments (owner, filepath, uploaded_by, upload_time) VALUES (?, ?, ?, ?)";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$owner, $file_path, $uploaded_by, $upload_time]);
    return true;
    RefreshPortal();

}
//
?>