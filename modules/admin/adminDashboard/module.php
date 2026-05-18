<?php
//The module responsible for Dashboard content on the admin portal. 
// yep

/**
 * @module adminDashboard
 * @name Dashboard
 * @role admin
 * @nav-text Admin Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/dashboardlib.php';





?>

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Welcome to the Simeck Admin Portal!</h1>
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
        <div class="module-card"><h1> Drive Status</h1>
    <h2>Drive Name:</h2>Larson<br />
    <h2> Drive Usage:</h2>
    <?php echo GetNASUsage(); ?>
    </div>
        
        <!-- Row 2: 2 cards, each spanning 2 columns -->
        <div class="module-card module-card--span-2"><center><h1>Total number of comments</h1>
        <h1><?php echo GetTotalCommentCount(); ?> </h1>
        </div>
        <div class="module-card module-card--span-2">Card 6</div>
        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="module-card module-card--span-4"><h1>Changelog</h1>
    <p><?php echo file_get_contents(__ROOT__ .'/changelog.txt'); ?></p></div>
    </div>
</div>