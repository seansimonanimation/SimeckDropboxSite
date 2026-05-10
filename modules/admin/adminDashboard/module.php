<?php
//The module responsible for dashboard content on the admin portal. 
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





?>
<link rel="stylesheet" href=<?php echo __ROOT__ . '/modules/admin/adminDashboard/moduleStyle.css'?> />
<div class="dashboard-container">
    <h1>Admin Dashboard</h1>
    <div class="dashboard-content">
        <div class="dashboard-section">
            <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
            <p>This is your admin dashboard where you can manage artists, clients, and view analytics.</p>
        </div>
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <ul>
                <li><a href="#">Manage Artists</a></li>
                <li><a href="#">Manage Clients</a></li>
                <li><a href="#">View Analytics</a></li>
            </ul>
        </div>
    </div>