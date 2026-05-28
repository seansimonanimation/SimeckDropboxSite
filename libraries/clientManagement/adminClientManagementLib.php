<?php
include_once __DIR__ .'/../session.php';
include_once __ROOT__ . '/libraries/sharedlib.php';
include_once __ROOT__ . '/download.php';

function GenerateClientCards(){
    if(isset($_GET['searchClient'])){
        $clients = GetSearchedClient($_GET['searchClient']);
    } else {
        $clients = GetAllClients();
    }
    foreach($clients as $client){
        echo '<div class="module-card module-card--span-2">';
        echo '<table class="client-edit-table">';
        echo '<tbody>';

        // Row: Email
        echo '<tr>';
        echo '<td class="client-label">Email</td>';
        echo '<td><input class="client-editable" type="email" data-email="' . htmlspecialchars($client['username']) . '" data-field="username" value="' . htmlspecialchars($client['username']) . '" /></td>';
        echo '</tr>';

        // Row: First Name
        echo '<tr>';
        echo '<td class="client-label">First Name</td>';
        echo '<td><input class="client-editable" type="text" data-email="' . htmlspecialchars($client['username']) . '" data-field="firstname" value="' . htmlspecialchars($client['firstname']) . '" /></td>';
        echo '</tr>';

        // Row: Last Name
        echo '<tr>';
        echo '<td class="client-label">Last Name</td>';
        echo '<td><input class="client-editable" type="text" data-email="' . htmlspecialchars($client['username']) . '" data-field="lastname" value="' . htmlspecialchars($client['lastname']) . '" /></td>';
        echo '</tr>';

        // Row: Point Of Contact
        echo '<tr>';
        echo '<td class="client-label">Point Of Contact</td>';
        echo '<td><select class="client-editable" data-email="' . htmlspecialchars($client['username']) . '" data-field="point_of_contact">';
        $allArtists = GetAllArtists();
        foreach($allArtists as $artist){
            $selected = ($artist['username'] === $client['point_of_contact']) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($artist['username']) . '"' . $selected . '>' . htmlspecialchars($artist['firstname'] . ' ' . $artist['lastname']) . '</option>';
        }
        echo '</select></td>';
        echo '</tr>';

        // Row: Outstanding Balance
        echo '<tr>';
        echo '<td class="client-label">Outstanding Balance</td>';
        echo '<td><input class="client-editable" type="number" step="0.01" data-email="' . htmlspecialchars($client['username']) . '" data-field="outstandingBalance" value="' . htmlspecialchars($client['outstandingBalance']) . '" /></td>';
        echo '</tr>';

        // Row: Lock Override Tokens (NEW)
        echo '<tr>';
        echo '<td class="client-label">Lock Override Tokens</td>';
        echo '<td><input class="client-editable" type="number" data-email="' . htmlspecialchars($client['username']) . '" data-field="lock_overrides" value="' . (int)($client['lock_overrides'] ?? 0) . '" /></td>';
        echo '</tr>';

        // Row: Active (toggle)
        echo '<tr>';
        echo '<td class="client-label">Active</td>';
        echo '<td><a href="#" class="toggle-client-status" data-email="' . htmlspecialchars($client['username']) . '" data-active="' . (int)$client['active'] . '"><h1>' . ($client['active'] ? '✅' : '❌') . '</h1></a></td>';
        echo '</tr>';

        // Row: Projects
        echo '<tr>';
        echo '<td class="client-label" style="vertical-align:top;">Projects</td>';
        echo '<td>' . FetchClientProjectAssignments($client['username'], $client['project_assignments']) . '</td>';
        echo '</tr>';

        // Row: Documents
        echo '<tr>';
        echo '<td class="client-label" style="vertical-align:top;">Documents</td>';
        echo '<td>' . DisplayClientDocuments($client['username']) . '</td>';
        echo '</tr>';

        echo '</tbody>';
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

function GetAllClientProjectList(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name, pid FROM projects WHERE pid LIKE 'c%'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetAllProjectsForDropdown(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name, pid FROM projects");
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

/**
 * Universal field updater for client records.
 * Called when an admin edits any field in the client card.
 */
function UpdateClientField($email, $field, $value){
    $allowedFields = ['username', 'firstname', 'lastname', 'point_of_contact', 'outstandingBalance', 'lock_overrides'];
    if(!in_array($field, $allowedFields)){
        return;
    }
    $pdo = DBConnect();
    // Sanitize field name (whitelist already checked, but use backticks for safety)
    $sql = "UPDATE clients SET `$field` = ? WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$value, $email]);
    RefreshPortal();
}

function ToggleClientActive($email){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT active FROM clients WHERE username = ?");
    $stmt->execute([$email]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if($client){
        $newActive = $client['active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE clients SET active = ? WHERE username = ?");
        $stmt->execute([$newActive, $email]);
    }
    RefreshPortal();
}

// ── Document Management ──

function SelectClientDocuments($email){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT uploadID, filepath, uploaded_by, upload_time FROM clientdocuments WHERE owner = ?");
    $stmt->execute([$email]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function DisplayClientDocuments($email){
    $documents = SelectClientDocuments($email);
    $html = '<div class="client-documents-list">';
    foreach($documents as $doc){
        $html .= '<div class="client-document-item">';
        $b64 = Generateb64EncodedDownloadLink($email, $doc['uploadID']);
        $html .= '<a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a>';
        $html .= ' <a href="#" class="delete-client-document" data-doc-id="' . $doc['uploadID'] . '">❌</a>';
        $html .= '</div>';
    }
    // Upload button
    $html .= '<button class="upload-file-button" data-client-id="' . htmlspecialchars($email) . '">+ Upload Document</button>';
    $html .= '</div>';
    return $html;
}

function UploadClientDocument($clientEmail, $firstname, $lastname, $file){
    $owner = $clientEmail;
    $folder_path = '/files/Corporate/ClientDocuments/' . $lastname . ', ' . $firstname . '/';
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
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO clientdocuments (owner, filepath, uploaded_by, upload_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$owner, $file_path, $uploaded_by, $upload_time]);
    RefreshPortal();
}

function DeleteClientDocument($docID){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT filepath FROM clientdocuments WHERE uploadID = ?");
    $stmt->execute([$docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $filePath = __ROOT__ . $result['filepath'];
        if(file_exists($filePath)){
            unlink($filePath);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM clientdocuments WHERE uploadID = ?");
    $stmt->execute([$docID]);
    RefreshPortal();
}

// ── Project Assignment Management ──

function FetchClientProjectAssignments($email, $projectAssignmentStr){
    $projectArr = empty($projectAssignmentStr) ? [] : explode(",", $projectAssignmentStr);

    // Get all projects
    $allProjects = GetAllClientProjectList();
    $output = [];

    // Dropdown for adding projects
    $dropdown = '<select onchange="addClientProject(\'' . $email . '\', this.value)">';
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

    // Existing assignments
    if (!empty($projectAssignmentStr)) {
        $pdo = DBConnect();
        $placeholders = implode(",", array_fill(0, count($projectArr), "?"));
        $stmt = $pdo->prepare("SELECT pid, project_name FROM projects WHERE pid IN ($placeholders)");
        $stmt->execute($projectArr);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $output[] = $row['pid'] . '_' . $row['project_name']
                . ' <a href="#" data-email="' . $email . '" data-pid="' . $row['pid'] . '" onclick="removeClientProject(\'' . $email . '\', \'' . $row['pid'] . '\'); return false;">❌</a>';
        }
    } else {
        $output[] = 'No projects assigned';
    }

    return implode("<br>", $output);
}

function AddClientToProject($email, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_assignments FROM clients WHERE username = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $projectArr = empty($result['project_assignments']) ? [] : explode(",", $result['project_assignments']);
        if(!in_array($pid, $projectArr)){
            $projectArr[] = $pid;
        }
        $newStr = implode(",", $projectArr);
        $stmt = $pdo->prepare("UPDATE clients SET project_assignments = ? WHERE username = ?");
        $stmt->execute([$newStr, $email]);
    }
    RefreshPortal();
}

function RemoveClientFromProject($email, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_assignments FROM clients WHERE username = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $projectArr = explode(",", $result['project_assignments']);
        if(($key = array_search($pid, $projectArr)) !== false){
            unset($projectArr[$key]);
        }
        $newStr = implode(",", $projectArr);
        $stmt = $pdo->prepare("UPDATE clients SET project_assignments = ? WHERE username = ?");
        $stmt->execute([$newStr, $email]);
    }
    RefreshPortal();
}
