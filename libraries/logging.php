<?php
// logging interface
// Provides a single-call interface for writing to the `logs` table.
// provides: TBD
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




function ShowAdminLogPageData(){
        try {
        $pdo = DBConnect();
        $stmt = $pdo->query("SELECT * FROM logs ORDER BY time DESC");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($logs)) {
            echo '<p>No logs recorded yet.</p>';
        } else {
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
                $username = htmlspecialchars($row['username'] ?? '');
                $time = htmlspecialchars($row['time'] ?? '');
                $action = htmlspecialchars($row['user_action'] ?? '');
                $ip = htmlspecialchars($row['ip_address'] ?? '');
                $extra = htmlspecialchars($row['extra_data'] ?? '');
                $project = htmlspecialchars($row['project_target'] ?? '');
                $imp = $row['impersonated_by'] ? htmlspecialchars($row['impersonated_by']) : '—';

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
        }
    } catch (Exception $e) {
        echo '<p>Error loading logs: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

function ShowArtistLogPageData(){
        try {
        $pdo = DBConnect();
        $stmt = $pdo->prepare("SELECT * FROM logs WHERE username = ? OR impersonated_by = ? ORDER BY time DESC");
        $stmt->execute([$_SESSION['username'], $_SESSION['username']]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($logs)) {
            echo '<p>No logs recorded yet.</p>';
        } else {
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
                $time = htmlspecialchars($row['time'] ?? '');
                $action = htmlspecialchars($row['user_action'] ?? '');
                $ip = htmlspecialchars($row['ip_address'] ?? '');
                $extra = htmlspecialchars($row['extra_data'] ?? '');
                $project = htmlspecialchars($row['project_target'] ?? '');

                echo '<tr style="border: 1px solid #555;">';
                echo "<td style=\"padding: 8px; border: 1px solid #555;\">$time</td>";
                echo "<td style=\"padding: 8px; border: 1px solid #555;\">$action</td>";
                echo "<td style=\"padding: 8px; border: 1px solid #555;\">$extra</td>";
                echo "<td style=\"padding: 8px; border: 1px solid #555;\">$project</td>";
                echo '</tr>';
            }
        }
    }     catch (Exception $e) {
        echo '<p>Error loading logs: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}