<?php
/**
 * tools/decode_download_token.php
 * 
 * CLI tool to decode and inspect Simeck download tokens (V1 and V2).
 * 
 * Usage:
 *   php tools/decode_download_token.php "download.php?download=TOKEN"
 *   php tools/decode_download_token.php "TOKEN"
 * 
 * Output:
 *   Token Version, Author (V2 only), Mode (V2 only), Filepath, Signature validity
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);

require_once __ROOT__ . '/libraries/encryptlib.php';

if (file_exists('/var/www/dbconfig.php')) {
    require_once '/var/www/dbconfig.php';
} elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
    require_once __ROOT__ . '/dbconfig.php';
}

$input = $argv[1] ?? '';
if (empty($input)) {
    echo "Usage: php decode_download_token.php \"TOKEN_OR_URL\"\n";
    exit(1);
}

// Strip full URL down to just the token
if (strpos($input, 'download.php?download=') !== false) {
    parse_str(parse_url($input, PHP_URL_QUERY), $params);
    $input = $params['download'] ?? $input;
}

$decoded = base64_decode($input, true);
if ($decoded === false) {
    echo "Error: Invalid base64 encoding.\n";
    exit(1);
}

$parts = explode('|', $decoded);

// ── V2 Token (2 parts: elFinder|<encrypted>) ────────────────────────
if (count($parts) === 2 && $parts[0] === 'elFinder') {
    echo "Token Version: V2 (AES-256-GCM encrypted)\n";
    echo "----------------------------------------\n";
    $plaintext = decryptImportantData($parts[1]);
    if ($plaintext === false) {
        echo "Error: Failed to decrypt token (wrong key or tampered data).\n";
        exit(1);
    }
    $inner = explode('|', $plaintext);
    if (count($inner) < 3) {
        echo "Error: Decrypted data has unexpected format.\n";
        exit(1);
    }
    echo "Author:   {$inner[0]}\n";
    echo "Mode:     {$inner[1]}\n";
    echo "Filepath: " . implode('|', array_slice($inner, 2)) . "\n";
    
// ── V1 Token (3 parts: elFinder|filepath|hmac or username|docID|hmac) ─
} elseif (count($parts) === 3) {
    echo "Token Version: V1 (HMAC-SHA256 signed)\n";
    echo "----------------------------------------\n";
    echo "Prefix:   {$parts[0]}\n";
    
    $secret = defined('DOWNLOAD_SECRET') ? DOWNLOAD_SECRET : 'fallback_dev_secret_change_me';
    $expectedSig = hash_hmac('sha256', $parts[1], $secret);
    $valid = hash_equals($expectedSig, $parts[2]);
    
    if ($parts[0] === 'elFinder') {
        echo "Type:     elFinder filepath\n";
        echo "Filepath: {$parts[1]}\n";
    } else {
        echo "Type:     Legacy DB document\n";
        echo "Username: {$parts[0]}\n";
        echo "Doc ID:   {$parts[1]}\n";
    }
    echo "Signature: " . ($valid ? "VALID" : "INVALID") . "\n";
    
} else {
    echo "Error: Unknown token format (" . count($parts) . " parts).\n";
    exit(1);
}
