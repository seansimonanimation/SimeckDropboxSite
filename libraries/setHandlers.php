<?php
include_once __ROOT__ . '/libraries/timeofflib.php';


   //Sends the user back to the login page if there is no session.
    if(!isset($_SESSION['username'])){
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header("location: login.php");
        exit;
    }
   if (isset($_GET['action']) && $_GET['action'] === 'switch_role') {
      adminViewToggle();
      header("Location: index.php");
      exit;
   }

// ——— Impersonation ———
if (isset($_GET['action']) && $_GET['action'] === 'impersonate' && isset($_GET['artist']) && GetRole() === 'admin') {
    $artistData = pull_artistAdmin_data($_GET['artist']);
    if ($artistData) {
        ImpersonateArtist($artistData);
    }
    header("Location: index.php");
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'stop_impersonating') {
    StopImpersonating();
    // Reset to admin dashboard so we don't try loading artist modules as admin
    SetActiveModule('Dashboard');
    header("Location: index.php");
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'impersonate_client' && isset($_GET['client']) && GetRole() === 'admin') {
    $clientData = pull_client_data($_GET['client']);
    if ($clientData) {
        ImpersonateClient($clientData);
    }
    header("Location: index.php");
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'impersonate_vendor' && isset($_GET['vendor']) && GetRole() === 'admin') {
    $vendorData = pull_vendor_data($_GET['vendor']);
    if ($vendorData) {
        ImpersonateVendor($vendorData);
    }
    header("Location: index.php");
    exit;
}

   if(isset($_GET['action']) && $_GET['action'] === 'logout'){
      logout();
      header("Location: login.php");
      exit;
   }
if(isset($_GET['action']) && $_GET['action'] === 'set_theme' && isset($_GET['theme']) && !IsImpersonating()){
    $theme = preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['theme']);
    $themesDir = __ROOT__ . '/css/siteThemes';
    if(file_exists($themesDir . '/' . $theme . '.css')){
        SetUserTheme($_SESSION['username'], $theme, $_SESSION['role']);
    }
    header("Location: index.php");
    exit;
}
if(isset($_GET['action']) && $_GET['action'] === 'toggle_bgvid' && !IsImpersonating()){
    $current = (int)($_SESSION['bgvid_visibility'] ?? 0);
    $newVal = $current ? 0 : 1;
    $_SESSION['bgvid_visibility'] = $newVal;
    $pdo = DBConnect();
    $username = $_SESSION['username'];
    if($_SESSION['role'] === 'client'){
        $stmt = $pdo->prepare("UPDATE clients SET bgvid_visibility = ? WHERE username = ?");
    } elseif($_SESSION['role'] === 'vendor'){
        $stmt = $pdo->prepare("UPDATE vendors SET bgvid_visibility = ? WHERE username = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE artists SET bgvid_visibility = ? WHERE username = ?");
    }
    $stmt->execute([$newVal, $username]);
    header("Location: index.php");
    exit;
}


if (isset($_GET['module'])) {
    $moduleName = $_GET['module'];
    // Set the active module in session
    $_SESSION['ActiveModule'] = $_SESSION['tempRole'] . $moduleName;
    
    // Set the active module path and redirect to refresh page with new content
    $_SESSION['ActiveModulePath'] = __ROOT__ . '/modules/' . $_SESSION['tempRole'] . '/' . $_SESSION['ActiveModule'] . '/module.php';
    
    // Redirect to refresh the page with new content
    header("Location: index.php");
    exit;
} else {
    // Initialize default module if none is set
    if (!isset($_SESSION['ActiveModule']) || empty($_SESSION['ActiveModule'])) {
        SetActiveModule('Dashboard');
    }
}
if(isset($_GET['action']) && $_GET['action'] === 'set_timezone' && isset($_GET['timezone']) && !IsImpersonating()){
    $tz = preg_replace('/[^a-zA-Z0-9_\/\-+]/', '', $_GET['timezone']);
    $valid = in_array($tz, DateTimeZone::listIdentifiers(), true);
    if($valid){
        SetUserTimezone($_SESSION['username'], $tz, $_SESSION['role']);
        $_SESSION['timezone'] = $tz;  // ← Update session immediately
    }
    header("Location: index.php");
    exit;
}
// ════════════════════════════════════════════════════════
// AJAX: Generate a preview floating island for a file
// ════════════════════════════════════════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'get_preview_island' && isset($_GET['filepath'])) {
    header('Content-Type: application/json');
    include_once __ROOT__ . '/libraries/downloadTokenAnalyzerLib.php';
    $filepath = $_GET['filepath'];
    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'error' => 'File not found.']);
        exit;
    }
    $html = GetFilePreviewIsland($filepath);
    echo json_encode(['success' => true, 'html' => $html]);
    exit;
}
// ════════════════════════════════════════════════════════
// AJAX: Analyze a download token
// ════════════════════════════════════════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'analyze_download_token') {
    header('Content-Type: application/json');
    
    // Load encryption key (needed to decrypt V2 tokens)
    if (file_exists('/var/www/dbconfig.php')) {
        include_once '/var/www/dbconfig.php';
    } elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
        include_once __ROOT__ . '/dbconfig.php';
    } elseif (file_exists(__ROOT__ . '/../dbconfig.php')) {
        include_once __ROOT__ . '/../dbconfig.php';
    }
    
    include_once __ROOT__ . '/libraries/downloadTokenAnalyzerLib.php';
    $token = $_GET['token'] ?? '';
    $result = AnalyzeDownloadToken($token);
    if ($result['success']) {
        $result['author_name'] = LookupAuthorDisplayName($result['author']);
        $result['thumbnail_url'] = GetFileThumbnailUrl($result['filepath']);
        $result['preview_token'] = GetFilePreviewUrl($result['filepath']);
    }
    echo json_encode($result);
    exit;
}


// ════════════════════════════════════════════════════════
// AJAX: Artist search for availability checker
// ════════════════════════════════════════════════════════
if(isset($_GET['action']) && $_GET['action'] === 'search_artists'){
    include_once __ROOT__ . '/libraries/artistmanagementlib.php';
    header('Content-Type: application/json');
    $q = $_GET['q'] ?? '';
    if($q === ''){
        $artists = GetAllActiveArtists();
    } else {
        $artists = SearchArtistsByName($q);
    }
    $results = [];
    foreach($artists as $a){
        $results[] = [
            'username' => $a['username'],
            'name' => GetArtistNicknameAndLegalName($a)
        ];
    }

    echo json_encode($results);
    exit;
}

// ════════════════════════════════════════════════════════
// AJAX: Get availability for a specific artist
// ════════════════════════════════════════════════════════
if(isset($_GET['action']) && $_GET['action'] === 'get_artist_availability' && isset($_GET['username'])){
    include_once __ROOT__ . '/libraries/artistmanagementlib.php';
    header('Content-Type: application/json');
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT availability, availability_this_week, timezone FROM artists WHERE username = ? AND active = 1");
    $stmt->execute([$_GET['username']]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$artist){
        echo json_encode(['error' => 'Artist not found']);
        exit;
    }
    $av = $artist['availability'] ?? '0|0|0|0|0|0|0';
    $avTw = $artist['availability_this_week'] ?? '0|0|0|0|0|0|0';
    $tz = $artist['timezone'] ?? 'UTC';
    $effectiveAv = GetEffectiveAvailability($av, $avTw);
    echo json_encode([
        'available_now' => IsArtistAvailableNow($effectiveAv, $tz),
        'availability_html' => DisplayArtistAvailability($effectiveAv, $tz)
    ]);
    exit;
}

// ════════════════════════════════════════════════════════
// AJAX: Convert a datetime from viewer's timezone to artist's or a specific timezone
// ════════════════════════════════════════════════════════
if(isset($_GET['action']) && $_GET['action'] === 'convert_datetime' && isset($_GET['datetime']) && (isset($_GET['artist']) || isset($_GET['timezone']))){
    header('Content-Type: application/json');
    
    $viewerTz = $_SESSION['timezone'] ?? 'UTC';
    $inputDatetime = $_GET['datetime']; // Format: "Y-m-d H:i"
    
    if(isset($_GET['artist'])){
        // Artist mode: look up the artist's timezone
        $pdo = DBConnect();
        $stmt = $pdo->prepare("SELECT timezone, firstname, lastname FROM artists WHERE username = ? AND active = 1");
        $stmt->execute([$_GET['artist']]);
        $artist = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$artist){
            echo json_encode(['error' => 'Artist not found']);
            exit;
        }
        $targetTz = $artist['timezone'] ?? 'UTC';
        $targetName = $artist['firstname'] . ' ' . $artist['lastname'];
    } else {
        // Timezone mode: use the provided timezone directly
        $targetTz = $_GET['timezone'];
        $valid = in_array($targetTz, DateTimeZone::listIdentifiers(), true);
        if(!$valid){
            echo json_encode(['error' => 'Invalid timezone']);
            exit;
        }
        $targetName = $targetTz;
    }
    
    try {
        $dt = new DateTime($inputDatetime, new DateTimeZone($viewerTz));
        $dt->setTimezone(new DateTimeZone($targetTz));
        $converted = $dt->format('Y-m-d H:i');
        $display = $dt->format('l, F j, Y') . ' at ' . $dt->format('g:i A');
        
        echo json_encode([
            'success' => true,
            'converted' => $converted,
            'display' => $display,
            'artist_name' => $targetName,
            'artist_timezone' => $targetTz,
            'target_timezone' => $targetTz
        ]);
    } catch(Exception $e){
        echo json_encode(['error' => 'Invalid date/time format']);
    }
    exit;
}

// ════════════════════════════════════════════════════════
// PORTFOLIO EDITOR — Action Handlers
// ════════════════════════════════════════════════════════

if (isset($_GET['action']) && strpos($_GET['action'], 'portfolio_') === 0) {
    include_once __ROOT__ . '/libraries/portfoliolib.php';
}

// ── portfolio_load ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_load' && isset($_GET['artist'])) {
    header('Content-Type: application/json');
    
    $targetArtist = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['artist']);
    
    // Permission check
    $role = GetTempRole();
    $isAdmin = ($role === 'admin');
    $isOwner = ($_SESSION['username'] === $targetArtist);
    
    if (!$isAdmin && !$isOwner) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    // Clean up any orphaned files not referenced in portfolio.json
    CleanupOrphanedPortfolioFiles($targetArtist);
    
    // For admin/impersonating, load the target artist's portfolio
    $portfolio = LoadPortfolio($targetArtist);

    $pfpFile = FindPortfolioPfp($targetArtist);
    $files = ListPortfolioFiles($targetArtist);
    
    echo json_encode([
        'success' => true,
        'portfolio' => $portfolio,
        'pfp' => $pfpFile ? GetPortfolioWebPath($targetArtist) . '/' . $pfpFile : null,
        'files' => $files
    ]);
    exit;
}

// ── portfolio_save ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_save') {
    header('Content-Type: application/json');
    
    if (IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Cannot save while impersonating']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }
    
    $result = SavePortfolio($_SESSION['username'], $input);
    echo json_encode($result);
    exit;
}

// ── portfolio_upload ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_upload') {
    header('Content-Type: application/json');
    
    if (IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Cannot upload while impersonating']);
        exit;
    }
    
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        exit;
    }
    
    $result = UploadPortfolioFile($_SESSION['username'], $_FILES['file']);
    echo json_encode($result);
    exit;
}

// ── portfolio_upload_pfp ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_upload_pfp') {
    header('Content-Type: application/json');
    
    if (IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Cannot change profile picture while impersonating']);
        exit;
    }
    
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        exit;
    }
    
    $result = UploadPortfolioPfp($_SESSION['username'], $_FILES['file']);
    if ($result['success']) {
        $result['url'] = GetPortfolioWebPath($_SESSION['username']) . '/pfp.' . $result['ext'];
    }
    echo json_encode($result);
    exit;
}

// ── portfolio_delete_file ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_delete_file') {
    header('Content-Type: application/json');
    
    if (IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete while impersonating']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $filename = $input['filename'] ?? $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'error' => 'No filename specified']);
        exit;
    }
    
    $deleted = DeletePortfolioFile($_SESSION['username'], $filename);
    echo json_encode(['success' => $deleted]);
    exit;
}

// ── portfolio_save_text ──
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_save_text') {
    header('Content-Type: application/json');
    
    if (IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Cannot edit while impersonating']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $filename = $input['filename'] ?? '';
    $content = $input['content'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'error' => 'No filename specified']);
        exit;
    }
    
    $result = SavePortfolioTextFile($_SESSION['username'], $filename, $content);
    echo json_encode($result);
    exit;
}
// ——— Portfolio: Embed cover art into an audio file ———
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_embed_cover' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $username = $_SESSION['username'];
    $allowedRoles = ['artist', 'admin'];
    if (!in_array(GetTempRole(), $allowedRoles) || IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    $audioFilename = $_POST['audio_filename'] ?? '';
    $audioFilename = basename($audioFilename); // Sanitize
    
    if (empty($audioFilename) || !isset($_FILES['cover_image'])) {
        echo json_encode(['success' => false, 'error' => 'Missing audio filename or cover image']);
        exit;
    }
    
    $audioPath = GetPortfolioPath($username) . '/' . $audioFilename;
    if (!file_exists($audioPath)) {
        echo json_encode(['success' => false, 'error' => 'Audio file not found']);
        exit;
    }
    
    include_once __ROOT__ . '/libraries/portfoliolib.php';
    $result = EmbedAudioCoverArtIntoFile($audioPath, $_FILES['cover_image']['tmp_name']);
    
    echo json_encode($result);
    exit;
}

// ——— Portfolio: Remove cover art from an audio file ———
if (isset($_GET['action']) && $_GET['action'] === 'portfolio_remove_cover' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $username = $_SESSION['username'];
    $allowedRoles = ['artist', 'admin'];
    if (!in_array(GetTempRole(), $allowedRoles) || IsImpersonating()) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $audioFilename = $input['filename'] ?? '';
    $audioFilename = basename($audioFilename); // Sanitize
    
    if (empty($audioFilename)) {
        echo json_encode(['success' => false, 'error' => 'Missing audio filename']);
        exit;
    }
    
    $audioPath = GetPortfolioPath($username) . '/' . $audioFilename;
    
    include_once __ROOT__ . '/libraries/portfoliolib.php';
    $result = StripAudioCoverArt($audioPath);
    
    echo json_encode($result);
    exit;
}
