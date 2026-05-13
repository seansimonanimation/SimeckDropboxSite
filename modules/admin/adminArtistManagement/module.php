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
include_once __ROOT__ . '/libraries/artistmanagement/artistmanagementlib.php';

if(isset($_GET['artist_id']) && isset($_GET['new_status'])){
    ToggleArtistStatus($_GET['artist_id'], $_GET['new_status']);
}


?>

<link rel="stylesheet" href="modules/admin/adminArtistManagement/moduleStyle.css" />
<div class="admin-artist-management">
    <div class="aam-header">
    </div>
    <div class="aam-grid">
        <div class="aam-card aam-card--span-4">
            <center><h1>Artist Management</h1>
            <p>This module allows admins to manage artists, including viewing artist details, editing information, and handling artist-related tasks.</p></center> </div>
        <div class="aam-card aam-card--span-1"> </div>
        <div class="aam-card aam-card--span-2"> Stats </div>
        <div class="aam-card aam-card--span-1"> Create new artist </div>
        <?php GenerateArtistCards(); ?>
    </div>
</div>