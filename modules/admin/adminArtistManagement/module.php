<?php
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
include_once __ROOT__ . '/modules/admin/adminArtistManagement/issets.php';
?>

<script src="modules/admin/adminArtistManagement/ajaxHandlers.js"></script>
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
