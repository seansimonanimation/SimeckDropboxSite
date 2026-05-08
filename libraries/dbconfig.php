<?php
/**
 * Simeck Portal — Database Configuration
 * Location: /var/www/dbconfig.php
 *
 * This file lives OUTSIDE the web root (/var/www/dropbox.simeck.com/).
 * It is not accessible via HTTP. PHP includes it by absolute path.
 *
 * Permissions: sudo chown root:www-data /var/www/dbconfig.php
 *              sudo chmod 640 /var/www/dbconfig.php
 */
 
return [
 
    // ── Simeck Portal DB (simeckdb, 192.168.1.243) ──────────────────────────
    // Stores client accounts, projects, access rules, logs, modules
    'simeckdb' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'dbname'  => 'simeckdb',
        'user'    => 'root',       // replace with actual credentials
        'pass'    => '',
        'charset' => 'utf8mb4',
    ]
];