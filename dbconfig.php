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
    'portal' => [
        'host'    => '192.168.1.243',
        'port'    => 3306,
        'dbname'  => 'simeck_portal',
        'user'    => 'portal_user',       // replace with actual credentials
        'pass'    => 'CHANGEME',
        'charset' => 'utf8mb4',
    ],
 
    // ── Vimbadmin DB (simeckdb, 192.168.1.243) ──────────────────────────────
    // Read-only access to artist/admin accounts
    'vimbadmin' => [
        'host'    => '192.168.1.243',
        'port'    => 3306,
        'dbname'  => 'vimbadmin',
        'user'    => 'vimbadmin_ro',      // replace with actual read-only credentials
        'pass'    => 'CHANGEME',
        'charset' => 'utf8mb4',
    ],
 
];