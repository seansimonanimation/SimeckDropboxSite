<?php
//
// libraries/encryptlib.php - Symmetric AES-256-GCM encryption for sensitive data
//
// Uses DB_ENCRYPTION_KEY from dbconfig.php. The key must be a 64-character hex
// string (32 bytes = AES-256). Output is a base64 string containing the 12-byte
// IV, ciphertext, and 16-byte GCM tag all in one portable blob.
//
// Both functions return false on failure (bad key, corrupted data, etc.).
//

function encryptImportantData($data) {
    if ($data === null || $data === '') {
        return $data; // don't encrypt null/empty, store as-is
    }

    if (!defined('DB_ENCRYPTION_KEY') || strlen(DB_ENCRYPTION_KEY) !== 64) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY not defined or not 64 hex chars');
        return false;
    }

    $key = hex2bin(DB_ENCRYPTION_KEY);
    if ($key === false) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY is not valid hex');
        return false;
    }

    $iv = openssl_random_pseudo_bytes(12); // 12-byte IV for GCM
    $tag = ''; // will be filled by openssl_encrypt

    $ciphertext = openssl_encrypt(
        (string)$data,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16 // tag length
    );

    if ($ciphertext === false) {
        error_log('encryptlib.php: openssl_encrypt failed: ' . openssl_error_string());
        return false;
    }

    // Pack as: IV (12) + ciphertext (variable) + tag (16)
    return base64_encode($iv . $ciphertext . $tag);
}

function decryptImportantData($data) {
    if ($data === null || $data === '') {
        return $data; // return null/empty as-is
    }

    if (!defined('DB_ENCRYPTION_KEY') || strlen(DB_ENCRYPTION_KEY) !== 64) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY not defined or not 64 hex chars');
        return false;
    }

    $key = hex2bin(DB_ENCRYPTION_KEY);
    if ($key === false) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY is not valid hex');
        return false;
    }

    $decoded = base64_decode($data, true);
    if ($decoded === false) {
        error_log('encryptlib.php: base64 decode failed — corrupted data?');
        return false;
    }

    // Minimum length: 12 (IV) + 16 (tag) = 28 bytes, with at least 1 byte ciphertext = 29
    if (strlen($decoded) < 29) {
        error_log('encryptlib.php: data too short to be valid encrypted blob');
        return false;
    }

    $iv         = substr($decoded, 0, 12);
    $tag        = substr($decoded, -16);
    $ciphertext = substr($decoded, 12, -16);

    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($plaintext === false) {
        error_log('encryptlib.php: openssl_decrypt failed — wrong key or tampered data');
        return false;
    }

    return $plaintext;
}
function encryptShortlinkId($id) {
    if (!defined('DB_ENCRYPTION_KEY') || strlen(DB_ENCRYPTION_KEY) !== 64) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY not defined or not 64 hex chars');
        return false;
    }
    $key = hex2bin(DB_ENCRYPTION_KEY);
    if ($key === false) {
        error_log('encryptlib.php: DB_ENCRYPTION_KEY is not valid hex');
        return false;
    }
    $block = str_pad((string)$id, 16, "\0", STR_PAD_RIGHT);
    $ciphertext = @openssl_encrypt(
        $block, 'aes-256-ecb', $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
    );
    if ($ciphertext === false) {
        error_log('encryptlib.php: openssl_encrypt (ECB) failed: ' . openssl_error_string());
        return false;
    }
    return rtrim(strtr(base64_encode($ciphertext), '+/', '-_'), '=');
}

function decryptShortlinkId($token) {
    if (!defined('DB_ENCRYPTION_KEY') || strlen(DB_ENCRYPTION_KEY) !== 64) {
        return false;
    }
    $key = hex2bin(DB_ENCRYPTION_KEY);
    if ($key === false) {
        return false;
    }
    $b64 = strtr($token, '-_', '+/');
    $pad = strlen($b64) % 4;
    if ($pad > 0) { $b64 .= str_repeat('=', 4 - $pad); }
    $decoded = base64_decode($b64, true);
    if ($decoded === false || strlen($decoded) !== 16) {
        return false;
    }
    $plaintext = @openssl_decrypt(
        $decoded, 'aes-256-ecb', $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
    );
    if ($plaintext === false) { return false; }
    $trimmed = rtrim($plaintext, "\0");
    if ($trimmed === '' || !ctype_digit($trimmed)) { return false; }
    return (int)$trimmed;
}
