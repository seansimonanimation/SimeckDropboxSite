<?php
/**
 * viewfolder.php
 * 
 * Relays a user to the elFinder file browser at a specific folder location.
 * Accepts ?hash=... with an elFinder folder hash.
 * Verifies the user has access to that volume; shows a popup if not.
 */

include_once __DIR__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';
require_once __ROOT__ . '/libraries/elfinderLibs/SimeckVolumeDriver.php';
require_once __ROOT__ . '/libraries/elfinderLibs/volumeConfig.php';

$role = $_SESSION['tempRole'] ?? $_SESSION['role'] ?? '';

// Determine which module page the user should land on based on role
$modulePages = [
    'admin'  => '/index.php?module=FileBrowser',
    'artist' => '/index.php?module=FileBrowser',
    'client' => '/index.php?module=ProjectManagement',
];

$targetPage = $modulePages[$role] ?? '/index.php';

$folderid = $_GET['folderid'] ?? '';

if (empty($folderid)) {
    header('Location: ' . $targetPage);
    exit;
}

// Build the vol options for the current role
switch ($role) {
    case 'admin':  $elfinderOptions = getAdminFileBrowserOptions();  break;
    case 'artist': $elfinderOptions = getArtistFileBrowserOptions(); break;
    case 'client': $elfinderOptions = getClientFileBrowserOptions(); break;
    default:
        // Unknown role, redirect to home
        header('Location: /index.php');
        exit;
}

// Decode the hash using the shared decoder — validates volume access automatically
$decodedPath = DecodeElfinderHash($folderid, $elfinderOptions);
$hasAccess = ($decodedPath !== null);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>
    <style>
        body { background: #1a1a2e; color: #e0e0e0; font-family: Arial, sans-serif; text-align: center; padding: 40px; }
        .access-error { background: #2a1a1a; border: 1px solid #ff4444; border-radius: 8px; padding: 30px; max-width: 500px; margin: 100px auto; }
        .access-error h2 { color: #ff6666; }
        .access-error p { color: #ccc; }
        .access-error a { color: #66aaff; }
        .redirecting { margin-top: 50px; color: #888; }
    </style>
</head>
<body>
    <?php if ($hasAccess): ?>
        <div class="redirecting">
            <p>Redirecting to the file browser...</p>
            <p><small>If you are not redirected automatically, <a href="<?php echo htmlspecialchars($targetPage . '#' . $folderid); ?>">click here</a>.</small></p>
        </div>
        <script>
            window.location.href = '<?php echo htmlspecialchars($targetPage . '#' . $folderid, ENT_QUOTES); ?>';
        </script>
    <?php else: ?>
        <div class="access-error">
            <h2>Access Denied</h2>
            <p>You do not have permission to access this folder, or the folder does not exist.</p>
            <p><a href="<?php echo htmlspecialchars($targetPage); ?>">Return to your file browser</a></p>
        </div>
    <?php endif; ?>
</body>
</html>
