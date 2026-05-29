<?php
//Obfuscates URL for downloading personnel files. Only allows access if the user is logged in and has the appropriate permissions.
include_once __DIR__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
// At the top of download.php, after the session include:
if (isset($_GET['download'])) {
    InitiateDownload($_GET['download']);
}



function Generateb64EncodedDownloadLink($username, $docID){
    $data = $username . '|' . $docID;
    return base64_encode($data);
}

function InitiateDownload($encodedData){
    if(DownloadPermissionCheck($encodedData)){
        ServeFileForDownload(...explode('|', base64_decode($encodedData)));
    }
}

function DownloadPermissionCheck($encodedData){
    $decodedData = base64_decode($encodedData);
    list($username, $filename) = explode('|', $decodedData);
    // Check if the user is logged in and has permission to access this file
    if(isset($_SESSION['username']) && UserHasPermissionForArtistFile($_SESSION['username'], $username)){
        // Serve the file for download
        return true;
    } else {
        return false;
    }
}

function UserHasPermissionForArtistFile($username, $artistID){
    // Implement your logic to check if the user has permission to access the file for the given artistID
    // This might involve checking the user's role, their association with the artist, etc.

    if($_SESSION['role'] == 'admin'){
        return true; // Admins have access to all files
    }
    if($_SESSION['username'] == $username){
        return true; // Users can access their own files
    }
    return false; // deny access for everyone that makes it to this line.
}

function ServeFileForDownload($username, $docID){
    $pdo = DBConnect();

    // Try artistdocuments first
    $stmt = $pdo->prepare("SELECT filepath FROM artistdocuments WHERE owner = ? AND uploadID = ?");
    $stmt->execute([$username, $docID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If not found, try clientdocuments
    if (!$result) {
        $stmt = $pdo->prepare("SELECT filepath FROM clientdocuments WHERE owner = ? AND uploadID = ?");
        $stmt->execute([$username, $docID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$result) {
        echo "File not found.";
        return;
    }
    
    $filePath = __ROOT__ . $result['filepath'];
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
}




?>