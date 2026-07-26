<?php
/**
 * @module vendorDocuments
 * @name Documents
 * @role vendor
 * @nav-text Documents
 * @nav-icon file-text
 * @nav-order 3
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/vendorLib.php';
include_once __ROOT__ . '/libraries/logging.php';

$username = $_SESSION['username'];
$companyName = $_SESSION['company_name'] ?? '';
$pocFirstname = $_SESSION['firstname'] ?? '';
$pocLastname = $_SESSION['lastname'] ?? '';

// ── Handle upload ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['vendor_doc_file'])) {
    $uploadType = $_POST['doc_type'] ?? '';
    if (!in_array($uploadType, ['invoice', 'contract', 'receipt'])) {
        $uploadType = 'invoice';
    }
    UploadVendorDocument($username, $companyName, $pocFirstname, $pocLastname, $_FILES['vendor_doc_file'], $uploadType);
    // UploadVendorDocument calls RefreshPortal() which handles AJAX vs redirect
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
                        <span style="display:block;margin-bottom:4px;">Document Type</span>
                        <select name="doc_type" class="module-input" required>
                            <option value="">— Select Type —</option>
                            <?php echo GetDocumentTypeOptions(); ?>
                        </select>
                    </div>
                    <div class="module-form-group" style="margin-bottom:12px;">
                        <span style="display:block;margin-bottom:4px;">File</span>
                        <input class="module-input" type="file" name="vendor_doc_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" required />
                    </div>
                    <button class="module-button" type="submit">Upload Document</button>
                </form>
            </div>
        </div>


        <!-- ── Document List Card ── -->
        <div class="module-card module-card--span-full">
            <div class="module-card__header">
                <h3 class="module-card__title">Your Documents</h3>
            </div>
            <div class="module-card__content">
                <?php echo DisplayVendorDocumentsVendor($username); ?>
            </div>
        </div>
    </div>
</div>
