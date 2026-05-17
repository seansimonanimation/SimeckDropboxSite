<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistDashboard
 * @name Dashboard
 * @role artist
 * @nav-text Artist Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/dashboardlib.php';






?>
<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Welcome to the Simeck Artist Portal!</h1>
        <br />
    </div>
    <div class="module-grid">
        <!-- Row 1: 4 cards, each 1 column (no span class needed) -->
        <div class="module-card"><center><h3> Number of active clients</h3>
        <p><?php echo GetClientCount(false); ?></p></center>
    </div>
        <div class="module-card"><center><h3> Number of active artists</h3>
        <p><?php echo GetArtistCount(false); ?></p></center>
    </div>
        <div class="module-card">Card 3</div>
        <div class="module-card">Card 4</div>
        
        <!-- Row 2: 2 cards, each spanning 2 columns -->
        <div class="module-card module-card--span-3">Card 5</div>
        <div class="module-card module-card--span-1">Card 6</div>
        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="module-card module-card--full"><h1>Changelog</h1>
    <p><?php echo DisplayChangelog(); ?></p></div>
    </div>
</div>