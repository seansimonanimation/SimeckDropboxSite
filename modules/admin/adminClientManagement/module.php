<?php
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

//Includes for both PHP AND JavaScript (AJAX handlers)
include_once __ROOT__ . '/modules/admin/adminClientManagement/issets.php';
?>
<script src="modules/admin/adminClientManagement/ajaxHandlers.js"></script>

<link rel="stylesheet" href="/css/moduleStyle.css">
<div class="module">
    <div class="module-header"></div>
    <div class="module-grid">
        <div class="module-card module-card--span-4">
            <h1>Client Management</h1>
            <p>This module allows admins to manage clients, including viewing client details, editing information, and handling client-related tasks.</p>
        </div>
        <div class="module-card module-card--span-1">
            Search for Client
            <form method="GET" class="client-search-form">
                <input class="module-input" type="text" name="searchClient" placeholder="Enter Client Name" /><br />
                <button class="module-button" type="submit">Search</button>
            </form>
        </div>
        <div class="module-card module-card--span-2">Stats</div>
        <div class="module-card module-card--span-1">
            Create new client<br />
            <form method="GET" class="module-create-form" action="" id="createClientForm">
                <input class="module-input" type="hidden" name="CreateClient" value="1" />
                <input class="module-input" type="text" name="email" placeholder="Email" required/><br />
                <input class="module-input" type="text" name="firstname" placeholder="First Name" required/><br />
                <input class="module-input" type="text" name="lastname" placeholder="Last Name" required/><br />
                <select class="module-input" name="pid">
                    <option value="">Select Initial project assignment</option>
                    <?php
                    $projs = GetAllClientProjectList();
                    foreach ($projs as $proj){
                        echo '<option value="' . $proj['pid'] . '">' . $proj['pid'] . ' ' . $proj['project_name'] . '</option>';
                    }
                    ?>
                </select><br />
                <select class="module-input" name="point_of_contact">
                    <option value="">Select Point Of Contact</option>
                    <?php
                        $pocs = GetAllArtists();
                        foreach($pocs as $poc){
                            echo '<option value="'.$poc['username'].'">'.$poc['firstname'].' '.$poc['lastname'].'</option>';
                        }
                    ?>
                </select><br />
                <button class="module-button" type="submit">Create Client</button>
            </form>
        </div>
        <?php GenerateClientCards(); ?>
        <input type="file" id="clientFileUploadInput" name="uploaded_file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" />
    </div>
</div>
