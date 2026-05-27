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
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM artists WHERE username = ?");
    $stmt->execute([$_POST['artist_id']]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($artist) {
        UploadArtistDocument($artist['username'],$artist['firstname'],$artist['lastname'], $_FILES['uploaded_file']);
    }
}
?>
<script> //case fix. making a nonuseful comment.
// ==========================================================
// AJAX helper — sends a GET request with the XHR header
// so RefreshPortal() returns JSON instead of redirecting
// ==========================================================
async function ajaxGet(url) {
    return fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}

// ==========================================================
// Refreshes the #content area in-place (no redirect)
// ==========================================================
async function refreshContent() {
    const resp = await fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const html = await resp.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newContent = doc.querySelector('#content');
    if (newContent) {
        document.querySelector('#content').innerHTML = newContent.innerHTML;
    }
    // Re-bind event listeners since the DOM was replaced
    initPageListeners();
}

// ==========================================================
// Project assignment (already works — stays in place)
// ==========================================================
async function assignProject(username, pid) {
    if (!pid) return;
    await ajaxGet('?addArtistToProject=' + username + ',' + pid);
    await refreshContent();
}

async function removeProject(username, pid) {
    await ajaxGet('?removeArtistFromProject=' + username + ',' + pid);
    await refreshContent();
}

// ==========================================================
// Toggle artist active status
// ==========================================================
async function toggleArtistStatus(artistId, newStatus) {
    await ajaxGet('?artist_id=' + artistId + '&new_status=' + newStatus);
    await refreshContent();
}

// ==========================================================
// Reset artist password
// ==========================================================
async function resetPassword(artistId) {
    await ajaxGet('?reset_pw_for=' + artistId);
    await refreshContent();
}

// ==========================================================
// Delete an artist document
// ==========================================================
async function deleteDocument(docId) {
    await ajaxGet('?delete=' + docId);
    await refreshContent();
}

// ==========================================================
// Upload a document via AJAX with FormData
// ==========================================================
async function uploadDocument(artistId, file) {
    const formData = new FormData();
    formData.append('uploaded_file', file);
    formData.append('artist_id', artistId);

    await fetch(window.location.href, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    });
    await refreshContent();
}

// ==========================================================
// Binds all event listeners (called on load AND after refresh)
// ==========================================================
function initPageListeners() {
    // --- Toggle artist status ---
    document.querySelectorAll('.toggle-artist-status').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleArtistStatus(this.dataset.artistId, this.dataset.newStatus);
        });
    });

    // --- Reset password ---
    document.querySelectorAll('.reset-pw-button').forEach(btn => {
        btn.addEventListener('click', function() {
            resetPassword(this.dataset.artistId);
        });
    });

    // --- Delete document ---
    document.querySelectorAll('.delete-artist-document').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteDocument(this.dataset.docId);
        });
    });

    // --- Upload file button (opens file picker) ---
    document.querySelectorAll('.upload-file-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const artistId = this.dataset.artistId;
            const fileInput = document.getElementById('fileUploadInput');
            fileInput.dataset.artistId = artistId;
            fileInput.click();
        });
    });

    // --- File selected → upload via AJAX ---
    const fileInput = document.getElementById('fileUploadInput');
    if (fileInput) {
        // Remove old listener to avoid duplicates
        const newInput = fileInput.cloneNode(true);
        fileInput.parentNode.replaceChild(newInput, fileInput);
        newInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadDocument(this.dataset.artistId, this.files[0]);
            }
            // Reset so re-selecting the same file triggers change again
            this.value = '';
        });
    }

    // --- Create artist form ---
    const createForm = document.getElementById('createArtistForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = this.querySelector('[name="username"]').value;
            const firstname = this.querySelector('[name="firstname"]').value;
            const lastname = this.querySelector('[name="lastname"]').value;
            await ajaxGet('?CreateArtist=1&username=' + encodeURIComponent(username)
                + '&firstname=' + encodeURIComponent(firstname)
                + '&lastname=' + encodeURIComponent(lastname));
            await refreshContent();
        });
    }
}

// ==========================================================
// Initial bind on page load
// ==========================================================
document.addEventListener('DOMContentLoaded', function() {
    initPageListeners();
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
        <div class="module-card module-card--span-1">Search for Artist
            <form method="GET" class="artist-search-form">
                <input class="module-input" type="text" name="searchArtist" placeholder="Enter Artist Name" /><br />
                <button class="module-button" type="submit">Search</button>
            </form>
        </div>
        <div class="module-card module-card--span-2"> Stats </div>
        <div class="module-card module-card--span-1"> <h1>Create new Artist</h1>
            <!-- CHANGED: added id="createArtistForm" so JS can intercept submit -->
            <form method="GET" class="module-create-form" action="" id="createArtistForm">
                <input class="module-input" type="hidden" name="CreateArtist" placeholder="Enter Artist name" />
                <input class="module-input" type="text" name="username" placeholder="Username" required/><br />
                <input class="module-input" type="text" name="firstname" placeholder="First Name" required/><br />
                <input class="module-input" type="text" name="lastname" placeholder="Last Name" required/><br />
                <button class="module-button" type="submit">Create Artist</button>
            </form>
        </div>
        <?php GenerateArtistCards(); ?>
        <input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>