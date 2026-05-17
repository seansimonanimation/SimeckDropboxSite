<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module adminArtistManagement
 * @name ArtistManagement
 * @role admin
 * @nav-text Artist Management
 * @nav-icon settings
 * @nav-order 80
 */
include_once __DIR__ . '/../../../libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/artistmanagementlib.php';

if(isset($_GET['CreateArtist'])){
    CreateNewArtist($_GET['username'], $_GET['firstname'], $_GET['lastname']);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    // Resolve artist_id → artist username
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM artists WHERE userID = ?");
    $stmt->execute([$_POST['artist_id']]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($artist) {
        UploadArtistDocument($artist['username'],$artist['firstname'],$artist['lastname'], $_FILES['uploaded_file']);
    }
}

?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.upload-file-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const artistId = this.dataset.artistId;
            const fileInput = document.getElementById('fileUploadInput');
            fileInput.dataset.artistId = artistId;
            fileInput.click();
        });
    });
    document.getElementById('fileUploadInput').addEventListener('change', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.enctype = 'multipart/form-data';
        form.appendChild(this);
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'artist_id';
        hiddenInput.value = this.dataset.artistId;
        form.appendChild(hiddenInput);

        document.body.appendChild(form);
        form.submit();
    });
});
</script>

<link rel="stylesheet" href="/css/moduleStyle.css">
<div class="module">
    <div class="module-header">
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-4">
            <h1>Artist Management</h1>
            <p>This module allows admins to manage artists, including viewing artist details, editing information, and handling artist-related tasks.</p> </div>
        <div class="module-card module-card--span-1">Search for Artist </div>
        <div class="module-card module-card--span-2"> Stats </div>
        <div class="module-card module-card--span-1"> <h1>Create new Artist</h1>
            <form method="GET" class="module-create-form" action="">
            <input class="module-input" type="hidden" name="CreateArtist" placeholder="Enter Artist name" />
            <input class="module-input" type="text" name="username" placeholder="Username" required/><br />
            <input class="module-input" type="text" name="firstname" placeholder="First Name" required/><br />
            <input class="module-input" type="text" name="lastname" placeholder="Last Name" required/><br />
            <button class="module-button" type="submit">Create Artist</button>
</form></div>
        <?php GenerateArtistCards(); ?>
        <input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>