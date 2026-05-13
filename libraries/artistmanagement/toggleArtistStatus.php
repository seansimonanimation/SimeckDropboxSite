<?php
include_once __DIR__ . '/../session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __DIR__ . '/artistmanagementlib.php';

$artistID = $_GET['artist_id'] ?? null;
$newStatus = $_GET['new_status'] ?? null;

if ($artistID && $newStatus !== null) {
    ToggleArtistStatus((int)$artistID, $newStatus);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing artist_id or new_status']);
}
?>