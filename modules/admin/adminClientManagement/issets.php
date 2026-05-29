<?php
// ── AJAX Handlers ──

// Toggle active status
if(isset($_GET['toggleActive'])){
    ToggleClientActive($_GET['toggleActive']);
}

// Update a single field
if(isset($_GET['field']) && isset($_GET['email']) && isset($_GET['value'])){
    UpdateClientField($_GET['email'], $_GET['field'], $_GET['value']);
}

// Add client to project
if(isset($_GET['addClientToProject'])){
    $params = explode(",", $_GET['addClientToProject']);
    AddClientToProject($params[0], $params[1]);
}

// Remove client from project
if(isset($_GET['removeClientFromProject'])){
    $params = explode(",", $_GET['removeClientFromProject']);
    RemoveClientFromProject($params[0], $params[1]);
}

// Delete a client document
if(isset($_GET['deleteDoc'])){
    DeleteClientDocument($_GET['deleteDoc']);
}

// Create new client
if(isset($_GET['CreateClient'])){
    CreateNewClient($_GET['email'], $_GET['firstname'], $_GET['lastname'], $_GET['point_of_contact'], $_GET['pid']);
}

// Upload document (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM clients WHERE username = ?");
    $stmt->execute([$_POST['client_id']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        UploadClientDocument($client['username'], $client['firstname'], $client['lastname'], $_FILES['uploaded_file']);
    }
}