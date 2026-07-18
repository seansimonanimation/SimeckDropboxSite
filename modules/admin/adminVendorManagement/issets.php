<?php
// Toggle active status
if(isset($_GET['toggleActive'])){
    ToggleVendorActive($_GET['toggleActive']);
}

// Update a single field
if(isset($_GET['field']) && isset($_GET['vendor']) && isset($_GET['value'])){
    UpdateVendorField($_GET['vendor'], $_GET['field'], $_GET['value']);
}

// Add vendor to project
if(isset($_GET['addVendorToProject'])){
    $params = explode(",", $_GET['addVendorToProject']);
    AddVendorToProject($params[0], $params[1]);
}

// Remove vendor from project
if(isset($_GET['removeVendorFromProject'])){
    $params = explode(",", $_GET['removeVendorFromProject']);
    RemoveVendorFromProject($params[0], $params[1]);
}

// Delete a vendor document
if(isset($_GET['deleteDoc'])){
    DeleteVendorDocument($_GET['deleteDoc']);
}

// Create new vendor
if(isset($_GET['CreateVendor'])){
    CreateNewVendor($_GET['username'], $_GET['company_name'], $_GET['poc_firstname'], $_GET['poc_lastname'], $_GET['point_of_contact'], $_GET['pid']);
}

// Upload document (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, company_name, vendor_poc_firstname, vendor_poc_lastname FROM vendors WHERE username = ?");
    $stmt->execute([$_POST['vendor_id']]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vendor) {
        UploadVendorDocument($vendor['username'], $vendor['company_name'], $vendor['vendor_poc_firstname'], $vendor['vendor_poc_lastname'], $_FILES['uploaded_file']);
    }
}
