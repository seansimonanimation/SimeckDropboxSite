<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistLogging
 * @name Logging
 * @role artist
 * @nav-text Activity Log
 * @nav-icon logging
 * @nav-order 90
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/logging.php';






?>
<div class="module">
    <div class="module-header">
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-4">
        <div class="logging-container">
            <div class="logging-content">
                <?php echo ShowArtistLogPageData(); ?>
            </div>
        </div>
    </div>
</div>