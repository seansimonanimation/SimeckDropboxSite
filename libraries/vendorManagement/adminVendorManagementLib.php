<?php
include_once __DIR__ .'/../session.php';
include_once __ROOT__ . '/libraries/sharedlib.php';
include_once __ROOT__ . '/download.php';

function GenerateVendorCards(){
    if(isset($_GET['searchVendor'])){
        $vendors = GetSearchedVendor($_GET['searchVendor']);
    } else {
        $vendors = GetAllVendors();
    }
    foreach($vendors as $vendor){
        echo '<div class="module-card module-card--span-2">';
        echo '<table class="vendor-edit-table">';
        echo '<tbody>';

        // Row: Username
        echo '<tr>';
        echo '<td class="vendor-label">Username</td>';
        echo '<td><input class="vendor-editable" type="text" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-field="username" value="' . htmlspecialchars($vendor['username']) . '" /></td>';
        echo '</tr>';

        // Row: Company Name
        echo '<tr>';
        echo '<td class="vendor-label">Company Name</td>';
        echo '<td><input class="vendor-editable" type="text" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-field="company_name" value="' . htmlspecialchars($vendor['company_name']) . '" /></td>';
        echo '</tr>';

        // Row: POC First Name
        echo '<tr>';
        echo '<td class="vendor-label">POC First Name</td>';
        echo '<td><input class="vendor-editable" type="text" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-field="vendor_poc_firstname" value="' . htmlspecialchars($vendor['vendor_poc_firstname']) . '" /></td>';
        echo '</tr>';

        // Row: POC Last Name
        echo '<tr>';
        echo '<td class="vendor-label">POC Last Name</td>';
        echo '<td><input class="vendor-editable" type="text" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-field="vendor_poc_lastname" value="' . htmlspecialchars($vendor['vendor_poc_lastname']) . '" /></td>';
        echo '</tr>';

        // Row: Point Of Contact
        echo '<tr>';
        echo '<td class="vendor-label">Point Of Contact</td>';
        echo '<td><select class="vendor-editable" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-field="point_of_contact">';
        $allArtists = GetAllArtistsForVendor();
        foreach($allArtists as $artist){
            $selected = ($artist['username'] === $vendor['point_of_contact']) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($artist['username']) . '"' . $selected . '>' . htmlspecialchars(GetArtistNicknameAndLegalName($artist)) . '</option>';
        }
        echo '</select></td>';
        echo '</tr>';

        // Row: Active (toggle)
        echo '<tr>';
        echo '<td class="vendor-label">Active</td>';
        echo '<td><a href="#" class="toggle-vendor-status" data-vendor="' . htmlspecialchars($vendor['username']) . '" data-active="' . (int)$vendor['active'] . '"><h1>' . ($vendor['active'] ? '✅' : '❌') . '</h1></a></td>';
        echo '</tr>';

        // Row: Projects
        echo '<tr>';
        echo '<td class="vendor-label" style="vertical-align:top;">Projects</td>';
        echo '<td>' . FetchVendorProjectAssignments($vendor['username'], $vendor['project_assignments']) . '</td>';
        echo '</tr>';

        // Row: Documents
        echo '<tr>';
        echo '<td class="vendor-label" style="vertical-align:top;">Documents</td>';
        echo '<td>' . DisplayVendorDocuments($vendor['username']) . '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}

function GetAllVendors(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM vendors");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetSearchedVendor($searchterm){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM vendors WHERE username LIKE ? OR company_name LIKE ? OR vendor_poc_firstname LIKE ? OR vendor_poc_lastname LIKE ?");
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetAllClientProjectListForVendor(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_name, pid FROM projects WHERE pid LIKE 'c%' OR pid LIKE 'p%'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetAllArtistsForVendor(){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname, nickname FROM artists");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function CreateNewVendor($username, $company_name, $pocFirstname, $pocLastname, $PoC, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO vendors (username, company_name, vendor_poc_firstname, vendor_poc_lastname, point_of_contact, project_assignments) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $company_name, $pocFirstname, $pocLastname, $PoC, $pid]);
    LogSimeckAction('Vendor created', "Vendor '{$username}' ({$company_name}) was created.", 'System');
    RefreshPortal();
}

function UpdateVendorField($username, $field, $value){
    $allowedFields = ['username', 'company_name', 'vendor_poc_firstname', 'vendor_poc_lastname', 'point_of_contact'];
    if(!in_array($field, $allowedFields)){
        return;
    }
    $pdo = DBConnect();
    $sql = "UPDATE vendors SET `$field` = ? WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$value, $username]);
    LogSimeckAction('Vendor field updated', "Vendor '{$username}' field '{$field}' updated.", 'System');
    RefreshPortal();
}

function ToggleVendorActive($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT active FROM vendors WHERE username = ?");
    $stmt->execute([$username]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
    if($vendor){
        $newActive = $vendor['active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE vendors SET active = ? WHERE username = ?");
        $stmt->execute([$newActive, $username]);
    }
    RefreshPortal();
}

// ── Document Management ──

function SelectVendorDocuments($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT uploadID, filepath, uploaded_by, upload_time FROM vendordocuments WHERE owner = ?");
    $stmt->execute([$username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function DisplayVendorDocuments($username){
    $documents = SelectVendorDocuments($username);
    $html = '<div class="vendor-documents-list">';
    foreach($documents as $doc){
        $html .= '<div class="vendor-document-item">';
        $b64 = Generateb64EncodedDownloadLink($username, $doc['uploadID']);
        $html .= '<a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a>';
        $html .= ' <a href="#" class="delete-vendor-document" data-doc-id="' . $doc['uploadID'] . '">❌</a>';
        $html .= '</div>';
    }
    $html .= '<button class="upload-file-button" data-vendor-id="' . htmlspecialchars($username) . '">+ Upload Document</button>';
    $html .= '</div>';
    return $html;
}

function UploadVendorDocument($vendorUsername, $companyName, $pocFirstname, $pocLastname, $file){
    $owner = $vendorUsername;
    $sanitizedCompany = preg_replace('/[^a-zA-Z0-9\s]/', '', $companyName);
    $folder_path = '/files/Corporate/VendorDocuments/' . trim($sanitizedCompany) . '/';
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
    $stmt = $pdo->prepare("INSERT INTO vendordocuments (owner, filepath, uploaded_by, upload_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$owner, $file_path, $uploaded_by, $upload_time]);
    LogSimeckAction('Vendor document uploaded', "Document uploaded for vendor '{$vendorUsername}'.", 'System');
    RefreshPortal();
}

function DeleteVendorDocument($docID){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT filepath FROM vendordocuments WHERE uploadID = ?");
    $stmt->execute([$docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $filePath = __ROOT__ . $result['filepath'];
        if(file_exists($filePath)){
            unlink($filePath);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM vendordocuments WHERE uploadID = ?");
    $stmt->execute([$docID]);
    RefreshPortal();
}

// ── Project Assignment Management ──

function FetchVendorProjectAssignments($username, $projectAssignmentStr){
    $projectArr = empty($projectAssignmentStr) ? [] : explode(",", $projectAssignmentStr);
    $allProjects = GetAllClientProjectListForVendor();
    $output = [];

    $dropdown = '<select onchange="addVendorProject(\'' . $username . '\', this.value)">';
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

    if (!empty($projectAssignmentStr)) {
        $pdo = DBConnect();
        $placeholders = implode(",", array_fill(0, count($projectArr), "?"));
        $stmt = $pdo->prepare("SELECT pid, project_name FROM projects WHERE pid IN ($placeholders)");
        $stmt->execute($projectArr);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $output[] = $row['pid'] . '_' . $row['project_name']
                . ' <a href="#" data-vendor="' . $username . '" data-pid="' . $row['pid'] . '" onclick="removeVendorProject(\'' . $username . '\', \'' . $row['pid'] . '\'); return false;">❌</a>';
        }
    } else {
        $output[] = 'No projects assigned';
    }

    return implode("<br>", $output);
}

function AddVendorToProject($username, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_assignments FROM vendors WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $projectArr = empty($result['project_assignments']) ? [] : explode(",", $result['project_assignments']);
        if(!in_array($pid, $projectArr)){
            $projectArr[] = $pid;
        }
        $newStr = implode(",", $projectArr);
        $stmt = $pdo->prepare("UPDATE vendors SET project_assignments = ? WHERE username = ?");
        $stmt->execute([$newStr, $username]);
    }
    RefreshPortal();
}

function RemoveVendorFromProject($username, $pid){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT project_assignments FROM vendors WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $projectArr = explode(",", $result['project_assignments']);
        if(($key = array_search($pid, $projectArr)) !== false){
            unset($projectArr[$key]);
        }
        $newStr = implode(",", $projectArr);
        $stmt = $pdo->prepare("UPDATE vendors SET project_assignments = ? WHERE username = ?");
        $stmt->execute([$newStr, $username]);
    }
    RefreshPortal();
}
