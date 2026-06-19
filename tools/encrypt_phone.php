<?php
//
// tools/encrypt_phone.php — One-off CLI script to encrypt a phone number
//
// Usage (from project root):
//   php tools/encrypt_phone.php "4805551234"
//
// Outputs the base64-encoded AES-256-GCM encrypted blob.
// Copy the output and use it in your UPDATE query directly.
//

// Paths relative to tools/ directory
require_once __DIR__ . '/../../dbconfig.php';
require_once __DIR__ . '/../libraries/encryptlib.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/encrypt_phone.php \"<phone_number>\"\n");
    exit(1);
}

$phoneNumber = trim($argv[1]);

if ($phoneNumber === '') {
    fwrite(STDERR, "Error: phone number cannot be empty.\n");
    exit(1);
}

if (!defined('DB_ENCRYPTION_KEY')) {
    fwrite(STDERR, "Error: DB_ENCRYPTION_KEY is not defined in dbconfig.php\n");
    exit(1);
}

$encrypted = encryptImportantData($phoneNumber);

if ($encrypted === false) {
    fwrite(STDERR, "Error: encryption failed.\n");
    exit(1);
}

echo $encrypted . "\n";
