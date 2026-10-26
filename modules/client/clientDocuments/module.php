<?php
/**
 * @module clientDocuments
 * @name Documents
 * @role client
 * @nav-text Documents
 * @nav-icon file-text
 * @nav-order 3
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/clientManagement/adminClientManagementLib.php';
include_once __ROOT__ . '/libraries/logging.php';

$username = $_SESSION['username'];
$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';

// ── Handle upload ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['client_doc_file'])) {
    UploadClientDocument($username, $firstname, $lastname, $_FILES['client_doc_file']);
    // UploadClientDocument calls RefreshPortal() which handles AJAX vs redirect
}
?>
<link rel="stylesheet" href="/css/moduleStyle.css">

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Documents</h1>
    </div>
    <div class="module-grid">
        <!-- ── Upload Form Card ── -->
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--placeholder"></div>
        <div class="module-card module-card--span-1">
            <div class="module-card__header">
                <h3 class="module-card__title">Upload a Document</h3>
            </div>
            <div class="module-card__content">
                <form method="POST" enctype="multipart/form-data" action="">
                    <div class="module-form-group" style="margin-bottom:12px;">
                        <span style="display:block;margin-bottom:4px;">File</span>
                        <input class="module-input" type="file" name="client_doc_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" required />
                    </div>
                    <button class="module-button" type="submit">Upload Document</button>
                </form>
            </div>
        </div>


        <!-- ── Document List Card ── -->
        <div class="module-card module-card--span-4">
            <div class="module-card__header">
                <h3 class="module-card__title">Your Documents</h3>
            </div>
            <div class="module-card__content">
                <?php echo DisplayClientDocumentsClient($username); ?>
            </div>
        </div>
    </div>
</div>
