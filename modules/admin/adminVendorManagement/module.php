<?php
/**
 * @module adminVendorManagement
 * @name VendorManagement
 * @role admin
 * @nav-text Vendor Management
 * @nav-icon users
 * @nav-order 6
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/vendorManagement/adminVendorManagementLib.php';

include_once __ROOT__ . '/modules/admin/adminVendorManagement/issets.php';
?>
<script src="modules/admin/adminVendorManagement/ajaxHandlers.js"></script>

<link rel="stylesheet" href="/css/moduleStyle.css">
<div class="module">
    <div class="module-header"></div>
    <div class="module-grid">
        <div class="module-card module-card--span-4">
            <h1>Vendor Management</h1>
            <p>This module allows admins to manage vendors, including viewing vendor details, editing information, and handling vendor-related tasks.</p>
        </div>
        <div class="module-card module-card--span-1">
            Search for Vendor
            <form method="GET" class="vendor-search-form">
                <input class="module-input" type="text" name="searchVendor" placeholder="Enter Vendor Name" /><br />
                <button class="module-button" type="submit">Search</button>
            </form>
        </div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-1">
            Create new vendor<br />
            <form method="GET" class="module-create-form" action="" id="createVendorForm">
                <input class="module-input" type="hidden" name="CreateVendor" value="1" />
                <input class="module-input" type="text" name="username" placeholder="Username" required/><br />
                <input class="module-input" type="text" name="company_name" placeholder="Company Name" required/><br />
                <input class="module-input" type="text" name="poc_firstname" placeholder="POC First Name" required/><br />
                <input class="module-input" type="text" name="poc_lastname" placeholder="POC Last Name" required/><br />
                <select class="module-input" name="pid">
                    <option value="">Select Initial project assignment</option>
                    <?php
                    $projs = GetAllClientProjectListForVendor();
                    foreach ($projs as $proj){
                        echo '<option value="' . $proj['pid'] . '">' . $proj['pid'] . ' ' . $proj['project_name'] . '</option>';
                    }
                    ?>
                </select><br />
                <select class="module-input" name="point_of_contact">
                    <option value="">Select Point Of Contact</option>
                    <?php
                        $pocs = GetAllArtistsForVendor();
                        foreach($pocs as $poc){
                            echo '<option value="'.$poc['username'].'">'.htmlspecialchars(GetArtistNicknameAndLegalName($poc)).'</option>';
                        }
                    ?>
                </select><br />
                <button class="module-button" type="submit">Create Vendor</button>
            </form>
        </div>
        <?php GenerateVendorCards(); ?>
        <input type="file" id="vendorFileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>
