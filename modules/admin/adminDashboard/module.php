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
<link rel="stylesheet" href="/modules/admin/adminDashboard/moduleStyle.css" />

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Welcome to the Simeck Admin Portal!</h1>
        <br />
    </div>
    <div class="dashboard-grid">
        <!-- Row 1: 4 cards, each 1 column (no span class needed) -->
        <div class="dashboard-card">Card 1</div>
        <div class="dashboard-card">Card 2</div>
        <div class="dashboard-card">Card 3</div>
        <div class="dashboard-card">Card 4</div>
        
        <!-- Row 2: 2 cards, each spanning 2 columns -->
        <div class="dashboard-card dashboard-card--span-2">Card 5</div>
        <div class="dashboard-card dashboard-card--span-2">Card 6</div>
        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="dashboard-card dashboard-card--full">Card 7</div>
    </div>
</div>