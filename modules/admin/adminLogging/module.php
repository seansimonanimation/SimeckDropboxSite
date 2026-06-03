<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module adminLogging
 * @name Logging
 * @role admin
 * @nav-text System Logs
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
            <h1>System Logs</h1>
            <div class="logging-content">
                <?php echo ShowAdminLogPageData(); ?>
            </div>
        </div>
    </div>
</div>