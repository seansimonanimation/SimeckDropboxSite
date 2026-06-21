<?php
/**
 * libraries/shortlinklib.php - Shortlink generation and resolution
 *
 * Shortlinks convert a long V2 download token (base64-encoded) into
 * a tiny integer-based link: https://dropbox.simeck.com/download.php?download=[base64(id)]
 *
 * The id is base64-encoded to keep it minimal -- early links will just
 * be base64("1"), base64("2"), etc. -- and the actual V2 download token
 * is stored in the database so it can be replayed on each access.
 *
 * Table schema:
 *   shortlinks ( id BIGINT AUTO_INCREMENT,
 *                download_token VARCHAR(400),
 *                expiry DATETIME,
 *                download_count BIGINT UNSIGNED DEFAULT 0 )
 */

function CreateShortlink($downloadToken, $expiryDateTime) {
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO shortlinks (download_token, expiry) VALUES (?, ?)");
    $stmt->execute([$downloadToken, $expiryDateTime]);
    $newId = $pdo->lastInsertId();
    if ($newId === false || $newId === '0') { return false; }
    return encryptShortlinkId((int)$newId);
}

function ResolveShortlink($encodedId) {
    $id = decryptShortlinkId($encodedId);
    if ($id === false) {
        $decoded = base64_decode($encodedId, true);
        if ($decoded !== false && $decoded !== '' && ctype_digit($decoded)) {
            $id = (int)$decoded;
        }
    }
    if ($id === false || $id <= 0) {
        return ['valid' => false, 'reason' => 'not_found'];
    }
    $pdo = DBConnect();
    $stmt = $pdo->prepare("SELECT download_token, expiry FROM shortlinks WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { return ['valid' => false, 'reason' => 'not_found']; }
    $expiryTimestamp = strtotime($row['expiry']);
    if ($expiryTimestamp === false || $expiryTimestamp < time()) {
        return ['valid' => false, 'reason' => 'expired'];
    }
    try {
        $countStmt = $pdo->prepare("UPDATE shortlinks SET download_count = download_count + 1 WHERE id = ?");
        $countStmt->execute([$id]);
    } catch (Exception $e) {
        error_log('shortlinklib.php: Failed to increment download_count for id ' . $id . ': ' . $e->getMessage());
    }
    return ['valid' => true, 'download_token' => $row['download_token']];
}

