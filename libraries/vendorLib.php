<?php
include_once __ROOT__ . '/libraries/session.php';
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
        echo '<td>' . DisplayVendorDocumentsAdmin($vendor['username']) . '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}

function GetAllVendors(){
    return PullDBValues("*", "vendors", 1, 1);
}


function GetSearchedVendor($searchterm){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT * FROM vendors WHERE username LIKE ? OR company_name LIKE ? OR vendor_poc_firstname LIKE ? OR vendor_poc_lastname LIKE ?");
    $likeTerm = '%' . $searchterm . '%';
    $stmt->execute([$likeTerm, $likeTerm, $likeTerm, $likeTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetAllClientProjectListForVendor(){
    return PullDBValues("project_name, pid", "projects", 1, 1, "AND (pid LIKE 'c%' OR pid LIKE 'p%')");
}


function GetAllArtistsForVendor(){
    return PullDBValues("username, firstname, lastname, nickname", "artists", 1, 1);
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
    
    $vendor = PullDBValues("active", "vendors", "username", $username)[0] ?? null;
    if($vendor){
        $pdo = DBConnect();
        $newActive = $vendor['active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE vendors SET active = ? WHERE username = ?");
        $stmt->execute([$newActive, $username]);
    }
    RefreshPortal();
}

// ── Document Type Helpers ──

function GetDocumentTypeOptions($selected = ''){
    $types = ['invoice' => 'Invoice', 'contract' => 'Contract', 'receipt' => 'Receipt'];
    $html = '';
    foreach($types as $val => $label){
        $sel = ($val === $selected) ? ' selected' : '';
        $html .= '<option value="' . $val . '"' . $sel . '>' . $label . '</option>';
    }
    return $html;
}

function GetDocumentTypeLabel($type){
    $labels = ['invoice' => 'Invoice', 'contract' => 'Contract', 'receipt' => 'Receipt'];
    return $labels[$type] ?? ucfirst($type);
}

function DisplayDocumentTypeBadge($type){
    $type = $type ?: 'invoice';
    $label = GetDocumentTypeLabel($type);
    return '<span class="doc-badge doc-badge--' . htmlspecialchars($type) . '">' . htmlspecialchars($label) . '</span>';
}

// ── Document Management ──

function SelectVendorDocuments($username){
    return PullDBValues("uploadID, upload_type, filepath, uploaded_by, upload_time", "vendordocuments", "owner", $username);
}

function SelectVendorDocumentsChronological($username){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT uploadID, upload_type, filepath, uploaded_by, upload_time FROM vendordocuments WHERE owner = ? ORDER BY upload_time DESC");
    $stmt->execute([$username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Admin-facing document list (inside vendor card)
 */
function DisplayVendorDocumentsAdmin($username){
    $documents = SelectVendorDocuments($username);
    $html = '<div class="vendor-documents-list">';
    foreach($documents as $doc){
        $html .= '<div class="vendor-document-item">';
        $b64 = Generateb64EncodedDownloadLink($username, $doc['uploadID']);
        $html .= DisplayDocumentTypeBadge($doc['upload_type'] ?? '') . ' ';
        $html .= '<a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a>';
        $html .= ' <a href="#" class="delete-vendor-document" data-doc-id="' . $doc['uploadID'] . '">❌</a>';
        $html .= '</div>';
    }
    $html .= '<button class="upload-file-button" data-vendor-id="' . htmlspecialchars($username) . '" data-vendor-type="admin">+ Upload Document</button>';
    $html .= '</div>';
    return $html;
}

/**
 * Vendor-facing document list (full page)
 */
function DisplayVendorDocumentsVendor($username){
    $documents = SelectVendorDocumentsChronological($username);
    if(empty($documents)){
        return '<p>No documents uploaded yet.</p>';
    }
    $html = '<table class="module-table" style="width:100%; border-collapse: collapse;">';
    $html .= '<thead><tr>';
    $html .= '<th>Document</th>';
    $html .= '<th>Type</th>';
    $html .= '<th>Uploaded By</th>';
    $html .= '<th>Date</th>';
    $html .= '</tr></thead><tbody>';
    foreach($documents as $doc){
        $b64 = Generateb64EncodedDownloadLink($username, $doc['uploadID']);
        $html .= '<tr>';
        $html .= '<td><a href="download.php?download=' . urlencode($b64) . '">' . htmlspecialchars(basename($doc['filepath'])) . '</a></td>';
        $html .= '<td>' . DisplayDocumentTypeBadge($doc['upload_type'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($doc['uploaded_by'] ?? '—') . '</td>';
        $html .= '<td>' . htmlspecialchars(date('M j, Y g:i A', strtotime($doc['upload_time']))) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function UploadVendorDocument($vendorUsername, $companyName, $pocFirstname, $pocLastname, $file, $uploadType = ''){
    $owner = $vendorUsername;
    $sanitizedCompany = preg_replace('/[^a-zA-Z0-9\s]/', '', $companyName);
    $sanitizedCompany = trim($sanitizedCompany);
    $folder_path = '/files/Corporate/VendorDocuments/' . $sanitizedCompany . '/';
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
    $stmt = $pdo->prepare("INSERT INTO vendordocuments (owner, upload_type, filepath, uploaded_by, upload_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$owner, $uploadType, $file_path, $uploaded_by, $upload_time]);
    LogSimeckAction('Vendor document uploaded', "Document ({$uploadType}) uploaded for vendor '{$vendorUsername}'.", 'System');
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
    
    $result = PullDBValues("project_assignments", "vendors", "username", $username)[0] ?? null;
    if($result){
        $projectArr = empty($result['project_assignments']) ? [] : explode(",", $result['project_assignments']);
        if(!in_array($pid, $projectArr)){
            $projectArr[] = $pid;
        }
        $newStr = implode(",", $projectArr);
        $pdo = DBConnect();
        $stmt = $pdo->prepare("UPDATE vendors SET project_assignments = ? WHERE username = ?");
        $stmt->execute([$newStr, $username]);
    }
    RefreshPortal();
}

function RemoveVendorFromProject($username, $pid){
    $result = PullDBValues("project_assignments", "vendors", "username", $username)[0] ?? null;
    if($result){
        $pdo = DBConnect();
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
// ── Floating Island: Vendor Document Upload (Admin) ──

function LoadVendorUploadIsland($vendorUsername){
    $uid = 'fi-vendor-upload-' . md5(uniqid('', true));
    $safeVendor = htmlspecialchars($vendorUsername, ENT_QUOTES, 'UTF-8');

    $bodyHtml = <<<HTML
<form id="{$uid}-form" enctype="multipart/form-data">
    <input type="hidden" name="vendor_id" value="{$safeVendor}">

    <div style="margin-bottom:14px;">
        <label style="display:block;margin-bottom:4px;font-weight:600;color:var(--color-heading);">
            Document Type
        </label>
        <select name="upload_type" required
            style="width:100%;padding:8px 10px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;">
            <option value="">— Select Type —</option>
            <option value="invoice">Invoice</option>
            <option value="contract">Contract</option>
            <option value="receipt">Receipt</option>
        </select>
    </div>

    <div style="margin-bottom:14px;">
        <label style="display:block;margin-bottom:4px;font-weight:600;color:var(--color-heading);">
            File
        </label>
        <input type="file" name="uploaded_file" required
            style="width:100%;padding:6px;border:1px solid var(--color-border-bright);border-radius:var(--radius-sm);background:var(--color-bg-raised);color:var(--color-text);font-family:var(--font-sans);font-size:0.88rem;">
    </div>

    <button type="submit" id="{$uid}-submit"
        style="padding:8px 20px;font-weight:600;">Upload Document</button>
    <div id="{$uid}-status" style="margin-top:10px;"></div>
</form>
HTML;

    $js = <<<JS
<script>
(function() {
    var form = document.getElementById('{$uid}-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var statusDiv = document.getElementById('{$uid}-status');
        var submitBtn = document.getElementById('{$uid}-submit');
        statusDiv.innerHTML = '<p style="color:var(--color-text-muted);">Uploading…</p>';
        submitBtn.disabled = true;

        var formData = new FormData(form);

        fetch('libraries/endpoints/vendorUploadIslandEndpoint.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                statusDiv.innerHTML = '<p style="color:var(--color-success);">✅ Document uploaded!</p>';
                setTimeout(function() {
                    var island = form.closest('.floating-island');
                    if (island) island.remove();
                    // Refresh the page content to show the new document
                    if (typeof refreshContent === 'function') {
                        refreshContent();
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                statusDiv.innerHTML = '<p style="color:var(--color-danger);">Error: ' + (data.error || 'unknown') + '</p>';
            }
        })
        .catch(function(err) {
            statusDiv.innerHTML = '<p style="color:var(--color-danger);">Request failed: ' + err.message + '</p>';
        })
        .finally(function() {
            submitBtn.disabled = false;
        });
    });
})();
</script>
JS;

    return SpawnFloatingIsland($bodyHtml . $js, 'Upload Document');
}
