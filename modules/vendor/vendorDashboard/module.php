<?php
/**
 * @module vendorDashboard
 * @name Dashboard
 * @role vendor
 * @nav-text Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
?>
<div class="module">
    <div class="module-header">
        <h1 class="module-title">Vendor Dashboard</h1>
    </div>
    <div class="module-grid">
        <div class="module-card module-card--span-2">
            <h2>Welcome, <?php echo htmlspecialchars(GetHumanName('greeting')); ?>!</h2>
            <p>You are logged in as a vendor.</p>
        </div>
    </div>
</div>
