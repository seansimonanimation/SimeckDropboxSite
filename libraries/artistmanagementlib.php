<?php
include_once __DIR__ . '/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/download.php';
include_once __ROOT__ . '/libraries/timeofflib.php';


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

    return  'Timezone: ' . $artistTimezone . '<br />' . implode('<br>', $lines);
}

function SearchArtistsByName($query){
    $SQLString = "SELECT username, firstname, lastname, nickname, secondary_roles, availability, availability_this_week, timezone FROM artists WHERE active = 1 AND (username LIKE ? OR firstname LIKE ? OR lastname LIKE ?)";
    $pdo = DBConnect();
    $likeTerm = '%' . $query . '%';
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        echo '<thead><tr><th>Username</th><th>Legal Name</th><th>Active</th><th>Role</th><th>Secondary Roles</th><th>Availability</th><th>Project Assignments</th><th>Reset PW</th><th>Upload Document</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td class="module-tablecell">' . htmlspecialchars($artist['username']) . '</td>';
        echo '<td class="module-tablecell">' . htmlspecialchars(GetArtistNicknameAndLegalName($artist)) . '</td>';

        echo '<td class="module-tablecell">' . GenerateArtistStatusButton($artist['username'], $artist['active']) . '</td>';
        echo '<td class="module-tablecell">' . htmlspecialchars($artist['role']) . '</td>';
        echo '<td class="module-tablecell">' . FetchArtistSecondaryRoles($artist['username'], $artist['secondary_roles'] ?? '') . '</td>';
        echo '<td class="module-tablecell">' . DisplayArtistAvailability($artist['availability'] ?? '0|0|0|0|0|0|0', $artist['timezone'] ?? 'UTC') . '</td>';
        echo '<td class="module-tablecell">' . FetchArtistProjectAssignments($artist['username'], $artist['project_assignments']) . '</td>';
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
    $SQLString = "SELECT username, firstname, lastname, nickname, userID, role, active, secondary_roles, project_assignments, availability, availability_this_week, timezone FROM artists";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function GetAllActiveArtists(){
    $SQLString = "SELECT username, firstname, lastname, nickname, secondary_roles, availability, availability_this_week, timezone FROM artists WHERE active = 1";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function IsArtistAvailableNow($avString, $artistTimezone){
    $parts = explode('|', $avString);
    if(count($parts) !== 7) return 'No';

    $artistTz = new DateTimeZone($artistTimezone);
    $now = new DateTime('now', $artistTz);
    $day = (int)$now->format('w'); // 0=Sun..6=Sat
    $bit = (int)$now->format('G') * 2 + (int)((int)$now->format('i') / 30);

    $mask = (int)$parts[$day];
    return ($mask >> $bit) & 1 ? 'Yes' : 'No';
}


function GenerateArtistStatusButton($artistUsername, $isActive){
    if($artistUsername == $_SESSION["username"]){
        return "This is you!";
    }
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
    $SQLString = "SELECT username, firstname, lastname, nickname, userID, role, active, secondary_roles, project_assignments, availability, availability_this_week, timezone FROM artists WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function ResetArtistPassword($username){
    $defaultPW = '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy'; // This is the hash for "SimeckArtist01".
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
    
    $dir = dirname($systemFilePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $systemFilePath)) {
    return false;
    }
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
        echo '<a href="#" class="delete-artist-document" data-doc-id="' . $doc['uploadID'] . '">❌</a>';
        echo '</div>';
    }
}

function DeleteArtistDocument($docID){
    $SQLString = "SELECT filepath FROM artistdocuments WHERE uploadID = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $filePath = __ROOT__ . $result['filepath'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    $SQLString = "DELETE FROM artistdocuments WHERE uploadID = ?";
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$docID]);
    RefreshPortal();
}

// ════════════════════════════════════════════════════════════
//  SECONDARY ROLE ASSIGNMENT FUNCTIONS
// ════════════════════════════════════════════════════════════

function FetchArtistSecondaryRoles($username, $secondaryRolesStr){
    $roleArr = empty($secondaryRolesStr) ? [] : explode(",", $secondaryRolesStr);

    $allRoles = GetAllDefinedSecondaryRoles();
    $output = [];

    // --- Dropdown to add a secondary role ---
    $dropdown = '<select onchange="assignSecondaryRole(\'' . $username . '\', this.value)">';
    $dropdown .= '<option value="">-- Add secondary role --</option>';
    $availableCount = 0;
    foreach ($allRoles as $role) {
        if (!in_array($role['role_name'], $roleArr)) {
            $dropdown .= '<option value="' . htmlspecialchars($role['role_name']) . '">' . htmlspecialchars($role['display_name']) . '</option>';
            $availableCount++;
        }
    }
    $dropdown .= '</select>';

    if ($availableCount > 0) {
        $output[] = $dropdown;
    } else {
        $output[] = 'All roles assigned';
    }

    // --- Existing secondary roles below ---
    if (!empty($secondaryRolesStr)) {
        foreach ($roleArr as $roleName) {
            $roleName = trim($roleName);
            if ($roleName === '') continue;
            // Look up display name
            $display = $roleName;
            foreach ($allRoles as $r) {
                if ($r['role_name'] === $roleName) {
                    $display = $r['display_name'];
                    break;
                }
            }
            $output[] = htmlspecialchars($display)
                . ' <a href="#" data-username="' . $username . '" data-rolename="' . htmlspecialchars($roleName) . '" onclick="removeSecondaryRole(\'' . $username . '\', \'' . htmlspecialchars(addslashes($roleName)) . '\'); return false;">❌</a>';
        }
    } else {
        $output[] = 'No secondary roles assigned';
    }

    return implode("<br>", $output);
}

function AddSecondaryRoleToArtist($username, $roleName){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT secondary_roles FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $currentStr = $result['secondary_roles'];
        $roleArr = empty($currentStr) ? [] : explode(",", $currentStr);
        if (!in_array($roleName, $roleArr)) {
            $roleArr[] = $roleName;
        }
        $newStr = implode(",", $roleArr);
        $updateStmt = $pdo->prepare("UPDATE artists SET secondary_roles = ? WHERE username = ?");
        $updateStmt->execute([$newStr, $username]);
    }
    RefreshPortal();
}

function RemoveSecondaryRoleFromArtist($username, $roleName){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT secondary_roles FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $currentStr = $result['secondary_roles'];
        $roleArr = empty($currentStr) ? [] : explode(",", $currentStr);
        if (($key = array_search($roleName, $roleArr)) !== false) {
            unset($roleArr[$key]);
        }
        $newStr = implode(",", $roleArr);
        $updateStmt = $pdo->prepare("UPDATE artists SET secondary_roles = ? WHERE username = ?");
        $updateStmt->execute([$newStr, $username]);
    }
    RefreshPortal();
}

// ════════════════════════════════════════════════════════════
//  DEFINED SECONDARY ROLES — MASTER LIST (for Admin Settings)
// ════════════════════════════════════════════════════════════

function GetAllDefinedSecondaryRoles(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT id, role_name, display_name FROM secondary_roles ORDER BY display_name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function AddDefinedSecondaryRole($roleName, $displayName){
    $pdo = DBConnect();
    // Check if already exists
    $stmt = $pdo->prepare("SELECT id FROM secondary_roles WHERE role_name = ?");
    $stmt->execute([$roleName]);
    if ($stmt->fetch()) {
        return false; // Already exists
    }
    $insert = $pdo->prepare("INSERT INTO secondary_roles (role_name, display_name) VALUES (?, ?)");
    $insert->execute([$roleName, $displayName]);
    return true;
}

function RemoveDefinedSecondaryRole($id){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("DELETE FROM secondary_roles WHERE id = ?");
    $stmt->execute([$id]);
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
    $SQLString = "SELECT project_assignments FROM artists WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $projectAssignmentStr = $result['project_assignments'];
        $projectArr = explode(",", $projectAssignmentStr);
        if (($key = array_search($pid, $projectArr)) !== false) {
            unset($projectArr[$key]);
        }
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
    $SQLString = "SELECT project_assignments FROM artists WHERE username = ?";
    $pdo = DBConnect();
    $stmt = $pdo->prepare($SQLString);
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $projectAssignmentStr = $result['project_assignments'];
        $projectArr = explode(",", $projectAssignmentStr);
        if (!in_array($pid, $projectArr)) {
            $projectArr[] = $pid;
        }
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
