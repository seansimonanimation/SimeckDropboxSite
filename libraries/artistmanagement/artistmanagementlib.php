<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php';  
function GenerateArtistCards() {
    $artists = GetAllArtists();
    foreach ($artists as $artist) {
        echo '<div class="aam-card aam-card--span-4">';
        echo '<table id="oneArtistTable" class="display aam-tablecell" style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr><th>Username</th><th>Human Name</th><th>Active</th><th>Role</th><th>Project Assignments</th><th>Reset PW</th><th>Upload Document</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['firstname']) . ' ' . htmlspecialchars($artist['lastname']) . '</td>';
        echo '<td class="aam-tablecell">' . GenerateArtistStatusButton($artist['username'], $artist['active']) . '</td>';
        echo '<td class="aam-tablecell">' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td class="aam-tablecell">' . FetchArtistProjectAssignments($artist['username'], $artist['project_assignments']) . '</td>';
        echo '<td class="aam-tablecell"><button class="edit-artist-button" onclick="location.href=\'?reset_pw_for=' . $artist['username'] . '\'">Reset PW</button></td>';
        echo '<td class="aam-tablecell"><button class="upload-file-button" data-artist-id="' . $artist['username'] . '">Upload Document</button></td>';
        echo '</tr>';
        echo '</tbody></table>';
        echo $artist['firstname'] . '\'s files:<br />';
        echo DisplayArtistDocuments($artist['username']);
        echo '</div>';
    }
}

function GetAllArtists(){
    $SQLString = "SELECT username, firstname, lastname, userID, role , active, project_assignments FROM artists";
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
        return '<a href="?artist_id=' . $artistID . '&new_status=0" class="toggle-artist-status"><h1>✅</h1></a>';
    } else {
        return '<a href="?artist_id=' . $artistID . '&new_status=1" class="toggle-artist-status"><h1>❌</h1></a>';
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

function UploadArtistDocument($artistName, $firstname, $lastname, $file){
    $owner = $artistName;
    $folder_path = '/files/Corporate/ArtistDocuments/' . $lastname . ", " . $firstname . '/';
    $file_path = $folder_path . $file['name'];
    $systemFilePath = __ROOT__ . $file_path;
    $uploaded_by = $_SESSION['username'];
    $upload_time = date('Y-m-d H:i:s');
    // This function would handle the file upload logic, including moving the uploaded file to a secure location and updating the database with the file information.
    // For security reasons, you should implement proper validation and sanitization of the uploaded file, as well as error handling.
    
    //Ensure that the user directory exists, and if not, create it.
    $dir = dirname($folder_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $systemFilePath)) {
    // Failed to move file — disk full? permissions?
    return false;
    }
    //Insert the DB record now.
    $SQLString = "INSERT INTO artistdocuments (owner, filepath, uploaded_by, upload_time) VALUES (?, ?, ?, ?)";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$owner, $file_path, $uploaded_by, $upload_time]);
    RefreshPortal();
}

function SelectArtistDocuments($artistName){
    $SQLString = "SELECT uploadID, filepath, uploaded_by, upload_time FROM artistdocuments WHERE owner = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function DisplayArtistDocuments($artistName){
    $documents = SelectArtistDocuments($artistName);
    foreach($documents as $doc){
        echo '<div class="artist-document">';
        $b64 = Generateb64EncodedDownloadLink($artistName, $doc['uploadID']);
        echo '<a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a>';
        echo '<a href="?delete=' . $doc['uploadID'] . '">❌</a>';
        echo '</div>';
    }
}

function DeleteArtistDocument($docID){
    // First, we need to get the file path so we can delete the file from the server.
    $SQLString = "SELECT filepath FROM artistdocuments WHERE uploadID = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $filePath = __ROOT__ . $result['filepath'];
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file from the server
        }
    }
    // Now we can delete the record from the database.
    $SQLString = "DELETE FROM artistdocuments WHERE uploadID = ?";
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$docID]);
    RefreshPortal();
}

function FetchArtistProjectAssignments($username, $projectAssignmentStr){
    if(empty($projectAssignmentStr)){
        return "No projects assigned";
    }
    $projectArr = explode(",", $projectAssignmentStr);

    // ------ CHANGED: select both pid AND project_name ------
    $SQLString = "SELECT pid, project_name FROM projects WHERE pid IN ("
        . implode(",", array_fill(0, count($projectArr), "?")) . ")";

    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute($projectArr);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------ CHANGED: build display string with pid_projectName + delete link ------
    $output = [];
    foreach ($results as $row) {
        $output[] = $row['pid'] . '_' . $row['project_name']
            . ' <a href="?removeArtistFromProject=' . $username . ',' . $row['pid'] . '">❌</a>';
    }
    return implode("<br>", $output);
}

function RemoveArtistFromProject($username, $pid){
    // First we need to get the current project assignment string for the artist
    $SQLString = "SELECT project_assignments FROM artists WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $projectAssignmentStr = $result['project_assignments'];
        $projectArr = explode(",", $projectAssignmentStr);
        // Remove the pid from the array
        if (($key = array_search($pid, $projectArr)) !== false) {
            unset($projectArr[$key]);
        }
        // Update the database with the new project assignment string
        $newProjectAssignmentStr = implode(",", $projectArr);
        $updateSQL = "UPDATE artists SET project_assignments = ? WHERE username = ?";
        $updateStmt = $pdo->prepare($updateSQL);
        $updateStmt->execute([$newProjectAssignmentStr, $username]);
    }
    RefreshPortal();
}

function RetrieveAllClientProjectsForDropdown(){
    $SQLString = "SELECT pid, project_name FROM projects WHERE pid LIKE 'c%'";
    return GetDataFromDB($SQLString);
}
//
?>