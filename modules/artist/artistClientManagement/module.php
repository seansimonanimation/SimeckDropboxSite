<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistClientManagement
 * @name ClientManagement
 * @role artist
 * @nav-text Client Management
 * @nav-icon users
 * @nav-order 30
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/clientManagement/artistClientManagement.php';
include_once __ROOT__ . '/libraries/logging.php';
?>


<link rel="stylesheet" href="/css/moduleStyle.css">

<div class="module">
    <div class="module-header">
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-3">
            <center><h1> Client Management</h1>
            <p> This module allows artist to manage their clients.</p></center>
        </div>
        <div class="module-card module-card--span-1">Search for Client 
            <form method="GET" class="client-search-form">
                <input class="module-input" type="text" name="searchClient" placeholder="Enter Client Name" /><br />
                <button class="module-button" type="submit">Search</button></form>
        </div>
        <?php GenerateArtistClientCards($_SESSION['username']); ?>
    </div>
</div>