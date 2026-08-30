<?php

//Issets for the admin Artist Management page.


if(isset($_GET['CreateArtist'])){
    CreateNewArtist($_GET['username'], $_GET['firstname'], $_GET['lastname']);
}

// ── Inline field update handler ──
if(isset($_GET['action']) && $_GET['action'] === 'update_artist_field'){
    UpdateArtistField($_GET['user_id'], $_GET['field'], $_GET['value']);
}

if(isset($_GET['addArtistToProject'])){
    $params = explode(",", $_GET['addArtistToProject']);
    AddArtistToProject($params[0], $params[1]);
}

if(isset($_GET['artist_id']) && isset($_GET['new_status'])){
    ToggleArtistStatus($_GET['artist_id'], $_GET['new_status']);
}
if(isset($_GET['reset_pw_for'])){
    ResetArtistPassword($_GET['reset_pw_for']);
}
if(isset($_GET['delete'])){
    DeleteArtistDocument($_GET['delete']);
}
if(isset($_GET['removeArtistFromProject'])){
    $params = explode(",", $_GET['removeArtistFromProject']);
    RemoveArtistFromProject($params[0], $params[1]);
}

// ════════════════════════════════════════════════════════════
//  SECONDARY ROLE HANDLERS
// ════════════════════════════════════════════════════════════
if(isset($_GET['addSecondaryRoleToArtist'])){
    $params = explode(",", $_GET['addSecondaryRoleToArtist']);
    AddSecondaryRoleToArtist($params[0], $params[1]);
}

if(isset($_GET['removeSecondaryRoleFromArtist'])){
    $params = explode(",", $_GET['removeSecondaryRoleFromArtist']);
    RemoveSecondaryRoleFromArtist($params[0], $params[1]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM artists WHERE username = ?");
    $stmt->execute([$_POST['artist_id']]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($artist) {
        UploadArtistDocument($artist['username'],$artist['firstname'],$artist['lastname'], $_FILES['uploaded_file']);
    }
}
?>