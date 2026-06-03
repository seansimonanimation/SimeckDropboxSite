<?php
include_once __DIR__ . '/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php'; 

function DisplayArtistAvailability($avString, $artistTimezone = 'UTC'){
    $parts = explode('|', $avString);
    if(count($parts) !== 7){ return '<span style="color:#999">Not Set</span>'; }
    if(array_sum($parts) == 0){ return '<span style="color:#999">Not Set</span>'; }

    $viewerTz = new DateTimeZone($_SESSION['timezone'] ?? 'UTC');
    $artistTz = new DateTimeZone($artistTimezone);

    // Convert artist's 7-day bitmask to viewer's local timezone
    $viewerMask = [0,0,0,0,0,0,0];
    $ref = new DateTime('2024-01-07', $artistTz); // Sunday reference

    for($d = 0; $d < 7; $d++){
        $mask = (int)$parts[$d];
        if($mask === 0) continue;

        for($b = 0; $b < 48; $b++){
            if(($mask >> $b) & 1){
                $dt = clone $ref;
                $dt->modify("+$d days");
                $dt->modify('+' . ($b * 30) . ' minutes');
                $dt->setTimezone($viewerTz);

                $vDay = (int)$dt->format('w');
                $vBit = (int)$dt->format('G') * 2 + (int)($dt->format('i') / 30);
                $viewerMask[$vDay] |= (1 << $vBit);
            }
        }
    }

    // Display from the converted viewer mask
    $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $lines = [];

    for($d = 0; $d < 7; $d++){
        $mask = $viewerMask[$d];
        if($mask === 0) continue;

        $ranges = [];
        $i = 0;
        while($i < 48){
            if(($mask >> $i) & 1){
                $start = $i;
                while($i < 48 && (($mask >> $i) & 1)){ $i++; }
                $end = $i;
                $startHour = floor($start / 2);
                $startMin = ($start % 2) * 30;
                $endHour = floor($end / 2);
                $endMin = ($end % 2) * 30;

                $startStr = ($startHour == 0 ? '12' : ($startHour > 12 ? $startHour - 12 : $startHour))
                    . ':' . str_pad($startMin, 2, '0', STR_PAD_LEFT)
                    . ($startHour < 12 ? 'a' : 'p');
                $endStr = ($endHour == 0 ? '12' : ($endHour > 12 ? $endHour - 12 : $endHour))
                    . ':' . str_pad($endMin, 2, '0', STR_PAD_LEFT)
                    . ($endHour < 12 ? 'a' : 'p');

                $ranges[] = $startStr . '-' . $endStr;
            } else {
                $i++;
            }
        }
        $lines[] = $dayNames[$d] . ': ' . implode(', ', $ranges);
    }

    return implode('<br>', $lines);
}





function GenerateArtistCards() {
    if(isset($_GET['searchArtist'])){
        $artists = GetSearchedArtist($_GET['searchArtist']);
    } else {
        $artists = GetAllArtists();
    }
    foreach ($artists as $artist) {
        echo '<div class="module-card module-card--span-4">';
        echo '<table id="oneArtistTable" class="display module-tablecell" style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr><th>Username</th><th>Human Name</th><th>Active</th><th>Role</th><th>Availability</th><th>Project Assignments</th><th>Reset PW</th><th>Upload Document</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td class="module-tablecell">' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td class="module-tablecell">' . htmlspecialchars($artist['firstname']) . ' ' . htmlspecialchars($artist['lastname']) . '</td>';
        echo '<td class="module-tablecell">' . GenerateArtistStatusButton($artist['username'], $artist['active']) . '</td>';
        echo '<td class="module-tablecell">' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td class="module-tablecell">' . DisplayArtistAvailability($artist['availability'] ?? '0|0|0|0|0|0|0', $artist['timezone'] ?? 'UTC') . '</td>';
        echo '<td class="module-tablecell">' . FetchArtistProjectAssignments($artist['username'], $artist['project_assignments']) . '</td>';
        // CHANGED: added class="reset-pw-button" and data attribute instead of location.href
        echo '<td class="module-tablecell"><button class="edit-artist-button reset-pw-button" data-artist-id="' . $artist['username'] . '">Reset PW</button></td>';
        echo '<td class="module-tablecell"><button class="upload-file-button" data-artist-id="' . $artist['username'] . '">Upload Document</button></td>';
        echo '</tr>';
        echo '</tbody></table>';
        echo $artist['firstname'] . '\'s files:<br />';
        echo DisplayArtistDocuments($artist['username']);
        echo '</div>';
    }
}
function GetAllArtists(){
    $SQLString = "SELECT username, firstname, lastname, userID, role , active, project_assignments, availability, timezone FROM artists";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function GenerateArtistStatusButton($artistUsername, $isActive){
    if($artistUsername == $_SESSION["username"]){
        return "This is you!";
    }
    // CHANGED: Use data attributes + href="#" instead of direct GET links
    $newStatus = $isActive ? 0 : 1;
    $icon = $isActive ? '✅' : '❌';
    return '<a href="#" class="toggle-artist-status" data-artist-id="' . $artistUsername . '" data-new-status="' . $newStatus . '"><h1>' . $icon . '</h1></a>';
}


function ToggleArtistStatus($artistUsername, $isActive){
    if($isActive == "1"){
        $SQLString = "UPDATE artists SET active = 1 WHERE username = ?";
    } else {
        $SQLString = "UPDATE artists SET active = 0 WHERE username = ?";
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$artistUsername]);
    RefreshPortal();
}
function GetSearchedArtist($searchterm){
    $SQLString = "SELECT username, firstname, lastname, userID, role , active, project_assignments, availability, timezone FROM artists WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $dir = dirname($systemFilePath);
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
        echo '<div class="admin-Artist-management-artist-document">';
        $b64 = Generateb64EncodedDownloadLink($artistName, $doc['uploadID']);
        echo '<a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a>';
        // CHANGED: use data attribute + href="#" instead of direct GET link
        echo '<a href="#" class="delete-artist-document" data-doc-id="' . $doc['uploadID'] . '">❌</a>';
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
    $projectArr = empty($projectAssignmentStr) ? [] : explode(",", $projectAssignmentStr);

    // --- Dropdown at top ---
    $allProjects = RetrieveAllActiveProjects();
    $output = [];
    $dropdown = '<select onchange="assignProject(\'' . $username . '\', this.value)">';
    $dropdown .= '<option value="">-- Add project --</option>';
    $availableCount = 0;
    foreach ($allProjects as $proj) {
        if (!in_array($proj['pid'], $projectArr)) {
            $dropdown .= '<option value="' . $proj['pid'] . '">' . $proj['pid'] . '_' . $proj['project_name'] . '</option>';
            $availableCount++;
        }
    }
    $dropdown .= '</select>';

    if ($availableCount > 0) {
        $output[] = $dropdown;
    } else {
        $output[] = 'All projects assigned';
    }

    // --- Then existing assignments below ---
    if (!empty($projectAssignmentStr)) {
        $SQLString = "SELECT pid, project_name FROM projects WHERE pid IN ("
            . implode(",", array_fill(0, count($projectArr), "?")) . ")";
        $pdo = DBConnect();
        $stmt = $pdo->prepare($SQLString);
        $stmt->execute($projectArr);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $output[] = $row['pid'] . '_' . $row['project_name']
    . ' <a href="#" data-username="' . $username . '" data-pid="' . $row['pid'] . '" onclick="removeProject(\'' . $username . '\', \'' . $row['pid'] . '\'); return false;">❌</a>';
        }
    } else {
        $output[] = 'No projects assigned';
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

function RetrieveAllActiveProjects(){
    $SQLString = "SELECT pid, project_name FROM projects WHERE active = 1";
    return GetDataFromDB($SQLString);
}

function AddArtistToProject($username, $pid){
    // First we need to get the current project assignment string for the artist
    $SQLString = "SELECT project_assignments FROM artists WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $projectAssignmentStr = $result['project_assignments'];
        $projectArr = explode(",", $projectAssignmentStr);
        // Add the new pid to the array if it's not already there
        if (!in_array($pid, $projectArr)) {
            $projectArr[] = $pid;
        }
        // Update the database with the new project assignment string
        $newProjectAssignmentStr = implode(",", $projectArr);
        $updateSQL = "UPDATE artists SET project_assignments = ? WHERE username = ?";
        $updateStmt = $pdo->prepare($updateSQL);
        $updateStmt->execute([$newProjectAssignmentStr, $username]);
    }
    RefreshPortal();
}

function CreateNewArtist($username, $firstname, $lastname){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO artists (username, firstname, lastname) VALUES (?, ?, ?)");
    $stmt->execute([$username, $firstname, $lastname]);
    RefreshPortal();
}