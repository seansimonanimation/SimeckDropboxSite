<?php
// logging interface
// Provides a single-call interface for writing to the `logs` table.
// provides: LogSimeckAction, ShowAdminLogPageData, ShowArtistLogPageData
//

//The Logging function. BE AFRAID!!!!!

function LogSimeckAction( $user_action, $extra_data, $project_target){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO logs (username, time, user_action, ip_address, extra_data, project_target, impersonated_by) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if(!isset($_SESSION['_imp_orig_username'])){ //Make sure that if the user is impersonating admin, we log who they are impersonating as well as who they actually are.
        $stmt->execute([$_SESSION['username'], $user_action, $_SERVER['REMOTE_ADDR'], $extra_data, $project_target, NULL]);
    } else {
        $stmt->execute([$_SESSION['username'], $user_action, $_SERVER['REMOTE_ADDR'], $extra_data, $project_target, $_SESSION['_imp_orig_username']]);
    }
}

// ── Preference helpers ──────────────────────────────────────────────────────

/**
 * Get the user's saved rows-per-page preference from the artists table.
 * Falls back to $default if nothing is saved or the saved value isn't valid.
 */
function GetUserLogRowsPerPage($username, $default = 50){
    $valid = [10, 25, 50, 100, 200, 500];
    if(!in_array($default, $valid, true)){ $default = 50; }

    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT log_rows_per_page FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $saved = $stmt->fetchColumn();
    if($saved !== false && in_array((int)$saved, $valid, true)){
        return (int)$saved;
    }
    return $default;
}

/**
 * Persist the user's rows-per-page preference to the artists table.
 * Only accepts values in [10, 25, 50, 100, 200, 500].
 */
function SetUserLogRowsPerPage($username, $perPage){
    $valid = [10, 25, 50, 100, 200, 500];
    if(!in_array((int)$perPage, $valid, true)){ return false; }
    $pdo = DBConnect();
    $stmt = $pdo->prepare("UPDATE artists SET log_rows_per_page = ? WHERE username = ?");
    return $stmt->execute([(int)$perPage, $username]);
}

// ── Shared helpers ──────────────────────────────────────────────────────────

/**
 * Build an &-encoded query string from a whitelist of parameters,
 * optionally overriding one value.  Used to preserve filter state
 * in pagination links.
 *
 * @param array  $params    Full $_GET array (or equivalent)
 * @param string $overrideKey   Key to override (or null)
 * @param mixed  $overrideValue New value for that key
 * @return string URL query string (without leading ?)
 */
function BuildLogQueryString($params, $overrideKey = null, $overrideValue = null){
    $keep = ['module','filter_username','filter_time_start','filter_time_end',
             'filter_ip','filter_project','filter_hide_impersonated','per_page'];
    $out = [];
    foreach($keep as $k){
        if(isset($params[$k]) && $params[$k] !== ''){
            $out[$k] = $params[$k];
        }
    }
    if($overrideKey !== null){
        $out[$overrideKey] = (string)$overrideValue;
    }
    return http_build_query($out);
}

/**
 * Render a pagination bar.
 *
 * @param int $currentPage
 * @param int $totalPages
 *
 */
function RenderLogPagination($currentPage, $totalPages){
    if($totalPages <= 1){ return; }

    echo '<div class="log-pagination" style="margin-top:12px; text-align:center;">';

    // Previous
    if($currentPage > 1){
        $qs = BuildLogQueryString($_GET, 'page', $currentPage - 1);
        echo '<a href="?' . $qs . '" class="page-link" style="padding:4px 10px; margin:0 2px; border:1px solid #555; border-radius:3px; text-decoration:none;">&laquo; Prev</a> ';
    } else {
        echo '<span class="page-link disabled" style="padding:4px 10px; margin:0 2px; border:1px solid #444; border-radius:3px; color:#666;">&laquo; Prev</span> ';
    }

    // Page numbers — show first, last, and ±5 around current
    $window = 5;
    $pages = [];

    // Always show page 1
    $pages[] = 1;

    $start = max(2, $currentPage - $window);
    $end   = min($totalPages - 1, $currentPage + $window);

    // Ellipsis after 1 if gap
    if($start > 2){
        $pages[] = '...';
    }

    for($i = $start; $i <= $end; $i++){
        $pages[] = $i;
    }

    // Ellipsis before last if gap
    if($end < $totalPages - 1){
        $pages[] = '...';
    }

    // Always show last page (if > 1)
    if($totalPages > 1){
        $pages[] = $totalPages;
    }

    $prevWasEllipsis = false;
    foreach($pages as $p){
        if($p === '...'){
            if(!$prevWasEllipsis){
                echo '<span style="padding:4px 6px; color:#888;">&hellip;</span> ';
                $prevWasEllipsis = true;
            }
            continue;
        }
        $prevWasEllipsis = false;

        if($p == $currentPage){
            echo '<span class="page-link current" style="padding:4px 10px; margin:0 2px; border:1px solid #888; border-radius:3px; background:#444; color:#fff; font-weight:bold;">' . $p . '</span> ';
        } else {
            $qs = BuildLogQueryString($_GET, 'page', $p);
            echo '<a href="?' . $qs . '" class="page-link" style="padding:4px 10px; margin:0 2px; border:1px solid #555; border-radius:3px; text-decoration:none;">' . $p . '</a> ';
        }
    }

    // Next
    if($currentPage < $totalPages){
        $qs = BuildLogQueryString($_GET, 'page', $currentPage + 1);
        echo '<a href="?' . $qs . '" class="page-link" style="padding:4px 10px; margin:0 2px; border:1px solid #555; border-radius:3px; text-decoration:none;">Next &raquo;</a>';
    } else {
        echo '<span class="page-link disabled" style="padding:4px 10px; margin:0 2px; border:1px solid #444; border-radius:3px; color:#666;">Next &raquo;</span>';
    }

    echo '</div>';
}

/**
 * Render the filter bar (shared by admin and artist views).
 * $showAdminFilters controls whether username / IP fields appear.
 */
function RenderLogFilterBar($showAdminFilters = true){
    $currentModule = $_GET['module'] ?? ($_SESSION['ActiveModule'] ?? '');
    $moduleVal = isset($_GET['module']) ? htmlspecialchars($_GET['module']) : '';

    $fUsername   = htmlspecialchars($_GET['filter_username'] ?? '');
    $fTimeStart  = htmlspecialchars($_GET['filter_time_start'] ?? '');
    $fTimeEnd    = htmlspecialchars($_GET['filter_time_end'] ?? '');
    $fIp         = htmlspecialchars($_GET['filter_ip'] ?? '');
    $fProject    = htmlspecialchars($_GET['filter_project'] ?? '');
    $fHideImp    = isset($_GET['filter_hide_impersonated']) ? 'checked' : '';
    $perPage     = (int)($_GET['per_page'] ?? GetUserLogRowsPerPage($_SESSION['username'] ?? 'admin'));

    echo '<form method="GET" class="log-filter-bar" style="margin-bottom:14px; padding:10px; border:1px solid var(--color-border-bright); border-radius:6px; display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">';

    if($moduleVal){
        echo '<input type="hidden" name="module" value="' . $moduleVal . '">';
    }

    if($showAdminFilters){
        echo '<div style="display:flex; flex-direction:column;">';
        echo '<label for="filter_username" style="font-size:0.85em; margin-bottom:2px;">Username</label>';
        echo '<input type="text" id="filter_username" name="filter_username" value="' . $fUsername . '" placeholder="Username" class="log-filter-input">';
        echo '</div>';
    }

    echo '<div style="display:flex; flex-direction:column;">';
    echo '<label for="filter_time_start" style="font-size:0.85em; margin-bottom:2px;">From</label>';
    echo '<input type="datetime-local" id="filter_time_start" name="filter_time_start" value="' . $fTimeStart . '" class="log-filter-datetime">';
    echo '</div>';

    echo '<div style="display:flex; flex-direction:column;">';
    echo '<label for="filter_time_end" style="font-size:0.85em; margin-bottom:2px;">To</label>';
    echo '<input type="datetime-local" id="filter_time_end" name="filter_time_end" value="' . $fTimeEnd . '" class="log-filter-datetime">';
    echo '</div>';

    if($showAdminFilters){
        echo '<div style="display:flex; flex-direction:column;">';
        echo '<label for="filter_ip" style="font-size:0.85em; margin-bottom:2px;">IP Address</label>';
        echo '<input type="text" id="filter_ip" name="filter_ip" value="' . $fIp . '" placeholder="IP Address" class="log-filter-input">';
        echo '</div>';
    }

    echo '<div style="display:flex; flex-direction:column;">';
    echo '<label for="filter_project" style="font-size:0.85em; margin-bottom:2px;">Project</label>';
    echo '<input type="text" id="filter_project" name="filter_project" value="' . $fProject . '" placeholder="Project (e.g. C01)" class="log-filter-input">';
    echo '</div>';

    echo '<div style="display:flex; flex-direction:column; justify-content:flex-end;">';
    echo '<label for="filter_hide_impersonated" style="font-size:0.85em; margin-bottom:2px;">&nbsp;</label>';
    echo '<div><input type="checkbox" id="filter_hide_impersonated" name="filter_hide_impersonated" value="1" ' . $fHideImp . '> <label for="filter_hide_impersonated">Hide impersonated</label></div>';
    echo '</div>';

    echo '<div style="display:flex; flex-direction:column;">';
    echo '<label for="per_page" style="font-size:0.85em; margin-bottom:2px;">Rows</label>';
    echo '<select id="per_page" name="per_page" onchange="this.form.submit()" class="log-filter-select">';
    foreach([10,25,50,100,200,500] as $pp){
        $sel = ($pp === $perPage) ? ' selected' : '';
        echo '<option value="' . $pp . '"' . $sel . '>' . $pp . '</option>';
    }
    echo '</select>';
    echo '</div>';

    echo '<div style="display:flex; flex-direction:column; justify-content:flex-end;">';
    echo '<label style="font-size:0.85em; margin-bottom:2px;">&nbsp;</label>';
    echo '<div style="display:flex; gap:4px;">';
    echo '<button type="submit" class="log-filter-btn">Apply</button>';
    $resetQs = $moduleVal ? ('module=' . urlencode($moduleVal)) : '';
    echo '<a href="?' . $resetQs . '" class="log-filter-link">Reset</a>';
    echo '</div>';
    echo '</div>';

    echo '</form>';
}

// ── Admin log viewer ────────────────────────────────────────────────────────

function ShowAdminLogPageData(){
    $pdo = DBConnect();

    // ── Read parameters ──────────────────────────────────────────────────
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $perPage    = (int)($_GET['per_page'] ?? GetUserLogRowsPerPage($_SESSION['username'] ?? 'admin'));
    $validPP    = [10, 25, 50, 100, 200, 500];
    if(!in_array($perPage, $validPP, true)){ $perPage = 50; }

    $fUsername  = trim($_GET['filter_username'] ?? '');
    $fTimeStart = trim($_GET['filter_time_start'] ?? '');
    $fTimeEnd   = trim($_GET['filter_time_end'] ?? '');
    $fIp        = trim($_GET['filter_ip'] ?? '');
    $fProject   = trim($_GET['filter_project'] ?? '');
    $fHideImp   = isset($_GET['filter_hide_impersonated']);

    // ── Persist per-page preference if changed ───────────────────────────
    $savedPP = GetUserLogRowsPerPage($_SESSION['username'] ?? 'admin');
    if($perPage !== $savedPP){
        SetUserLogRowsPerPage($_SESSION['username'] ?? 'admin', $perPage);
    }

    // ── Build WHERE clauses ──────────────────────────────────────────────
    $conditions = [];
    $params     = [];

    if($fUsername !== ''){
        $conditions[] = 'username LIKE ?';
        $params[]     = '%' . $fUsername . '%';
    }
    if($fTimeStart !== ''){
        $conditions[] = 'time >= ?';
        $params[]     = $fTimeStart;
    }
    if($fTimeEnd !== ''){
        $conditions[] = 'time <= ?';
        $params[]     = $fTimeEnd;
    }
    if($fIp !== ''){
        $conditions[] = 'ip_address LIKE ?';
        $params[]     = '%' . $fIp . '%';
    }
    if($fProject !== ''){
        $conditions[] = 'project_target LIKE ?';
        $params[]     = '%' . $fProject . '%';
    }
    if($fHideImp){
        $conditions[] = 'impersonated_by IS NULL';
    }

    $whereSQL = '';
    if(!empty($conditions)){
        $whereSQL = 'WHERE ' . implode(' AND ', $conditions);
    }

    // ── Count total matching rows ────────────────────────────────────────
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM logs $whereSQL");
    $countStmt->execute($params);
    $totalRows  = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalRows / $perPage));

    if($page > $totalPages){ $page = $totalPages; }
    $offset = ($page - 1) * $perPage;

    // ── Fetch data ───────────────────────────────────────────────────────
    $dataSQL = "SELECT * FROM logs $whereSQL ORDER BY time DESC LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($dataSQL);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Output ───────────────────────────────────────────────────────────
    echo '<div class="logging-container">';
    echo '<h1>System Logs</h1>';

    // Filter bar
    RenderLogFilterBar(true);

    // Summary
    echo '<p style="margin-bottom:8px; font-size:0.9em; color:#aaa;">';
    echo 'Page ' . $page . ' of ' . $totalPages . ' &mdash; ' . number_format($totalRows) . ' total record' . ($totalRows !== 1 ? 's' : '');
    echo '</p>';

    if(empty($logs)){
        echo '<p>No logs match the current filters.</p>';
        echo '</div>';
        return;
    }

    echo '<table class="admin-table" style="width:100%; border-collapse: collapse;">';
    echo '<thead>';
    echo '<tr style="background: #333; color: #fff;">';
    echo '<th style="padding: 8px; border: 1px solid #555;">Username</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Time</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Action</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">IP Address</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Extra Data</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Project Target</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Impersonated By</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($logs as $row) {
        $username = GetLogUsernameDisplay($row['username'] ?? '');
        $time     = htmlspecialchars($row['time'] ?? '');
        $action   = htmlspecialchars($row['user_action'] ?? '');
        $ip       = htmlspecialchars($row['ip_address'] ?? '');
        $extra    = htmlspecialchars($row['extra_data'] ?? '');
        $project  = htmlspecialchars($row['project_target'] ?? '');
        $imp      = $row['impersonated_by'] ? htmlspecialchars($row['impersonated_by']) : '—';

        echo '<tr style="border: 1px solid #555;">';
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$username</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$time</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$action</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$ip</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$extra</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$project</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$imp</td>";
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Pagination controls
    RenderLogPagination($page, $totalPages, '');

    echo '</div>';
}

// ── Artist log viewer ───────────────────────────────────────────────────────

function ShowArtistLogPageData(){
    $pdo = DBConnect();

    // ── Read parameters ──────────────────────────────────────────────────
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $perPage    = (int)($_GET['per_page'] ?? GetUserLogRowsPerPage($_SESSION['username'] ?? 'artist'));
    $validPP    = [10, 25, 50, 100, 200, 500];
    if(!in_array($perPage, $validPP, true)){ $perPage = 50; }

    $fTimeStart = trim($_GET['filter_time_start'] ?? '');
    $fTimeEnd   = trim($_GET['filter_time_end'] ?? '');
    $fProject   = trim($_GET['filter_project'] ?? '');
    $fHideImp   = isset($_GET['filter_hide_impersonated']);

    // ── Persist per-page preference if changed ───────────────────────────
    $savedPP = GetUserLogRowsPerPage($_SESSION['username'] ?? 'artist');
    if($perPage !== $savedPP){
        SetUserLogRowsPerPage($_SESSION['username'] ?? 'artist', $perPage);
    }

    // ── Build WHERE clauses ──────────────────────────────────────────────
    // Base: the artist sees their own logs (or those impersonated by them)
    $conditions = ['(username = ? OR impersonated_by = ?)'];
    $params     = [$_SESSION['username'], $_SESSION['username']];

    if($fTimeStart !== ''){
        $conditions[] = 'time >= ?';
        $params[]     = $fTimeStart;
    }
    if($fTimeEnd !== ''){
        $conditions[] = 'time <= ?';
        $params[]     = $fTimeEnd;
    }
    if($fProject !== ''){
        $conditions[] = 'project_target LIKE ?';
        $params[]     = '%' . $fProject . '%';
    }
    if($fHideImp){
        $conditions[] = 'impersonated_by IS NULL';
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $conditions);

    // ── Count total matching rows ────────────────────────────────────────
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM logs $whereSQL");
    $countStmt->execute($params);
    $totalRows  = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalRows / $perPage));

    if($page > $totalPages){ $page = $totalPages; }
    $offset = ($page - 1) * $perPage;

    // ── Fetch data ───────────────────────────────────────────────────────
    $dataSQL = "SELECT * FROM logs $whereSQL ORDER BY time DESC LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($dataSQL);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Output ───────────────────────────────────────────────────────────
    echo '<div class="logging-container">';
    echo '<h1>Activity Log</h1>';

    // Filter bar (no username/IP filters for artist view)
    RenderLogFilterBar(false);

    // Summary
    echo '<p style="margin-bottom:8px; font-size:0.9em; color:#aaa;">';
    echo 'Page ' . $page . ' of ' . $totalPages . ' &mdash; ' . number_format($totalRows) . ' total record' . ($totalRows !== 1 ? 's' : '');
    echo '</p>';

    if(empty($logs)){
        echo '<p>No logs match the current filters.</p>';
        echo '</div>';
        return;
    }

    echo '<table class="admin-table" style="width:100%; border-collapse: collapse;">';
    echo '<thead>';
    echo '<tr style="background: #333; color: #fff;">';
    echo '<th style="padding: 8px; border: 1px solid #555;">Time</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Action</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Extra Data</th>';
    echo '<th style="padding: 8px; border: 1px solid #555;">Project Target</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($logs as $row) {
        $time    = htmlspecialchars($row['time'] ?? '');
        $action  = htmlspecialchars($row['user_action'] ?? '');
        $extra   = htmlspecialchars($row['extra_data'] ?? '');
        $project = htmlspecialchars($row['project_target'] ?? '');

        echo '<tr style="border: 1px solid #555;">';
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$time</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$action</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$extra</td>";
        echo "<td style=\"padding: 8px; border: 1px solid #555;\">$project</td>";
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Pagination controls
    RenderLogPagination($page, $totalPages, '');

    echo '</div>';
}

/**
 * Get a formatted username string for log display.
 * Returns "username (First Last)" or "username (First Last) (Nickname)".
 */
function GetLogUsernameDisplay($username){
    if(empty($username)){
        return '';
    }
    $pdo = DBConnect();
    // Check artists first
    $stmt = $pdo->prepare("SELECT firstname, lastname, nickname FROM artists WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $base = htmlspecialchars($username) . ' (' . htmlspecialchars($result['firstname']) . ' ' . htmlspecialchars($result['lastname']) . ')';
        if(!empty($result['nickname'])){
            $base .= ' (' . htmlspecialchars($result['nickname']) . ')';
        }
        return $base;
    }
    // Check clients
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM clients WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        return htmlspecialchars($username) . ' (' . htmlspecialchars($result['firstname']) . ' ' . htmlspecialchars($result['lastname']) . ')';
    }
    return htmlspecialchars($username);
}

