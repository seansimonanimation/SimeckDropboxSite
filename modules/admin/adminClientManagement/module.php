<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module adminClientManagement
 * @name ClientManagement
 * @role admin
 * @nav-text Client Management
 * @nav-icon users
 * @nav-order 5
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/clientManagement/adminClientManagementLib.php';
?>




<link rel="stylesheet" href="/modules/admin/adminClientManagement/moduleStyle.css" />
<div class="admin-client-management">
    <div class="acm-header">
    </div>
    <div class="acm-grid">
        <div class="acm-card acm-card--span-4">
            <h1>Client Management</h1>
            <p>This module allows admins to manage clients, including viewing client details, editing information, and handling client-related tasks.</p> </div>
        <div class="acm-card acm-card--span-1">Search for Client </div>
        <div class="acm-card acm-card--span-2"> Stats </div>
        <div class="acm-card acm-card--span-1"> Create new client </div>
        <?php GenerateClientCards(); ?>
        <input type="file" id="fileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>