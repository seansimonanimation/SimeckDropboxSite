<?php
/**
 * tools/build_weekly_availability.php
 *
 * Cronjob — run Sunday evening to rebuild availability_this_week for all artists.
 * Schedule with Windows Task Scheduler or *nix cron.
 *
 * Example cron line (Sunday 8pm):
 *   0 20 * * 0 php /path/to/tools/build_weekly_availability.php
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Bootstrap the application
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);

require_once __ROOT__ . '/libraries/db.php';
require_once __ROOT__ . '/libraries/logging.php';
require_once __ROOT__ . '/libraries/timeofflib.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting weekly availability rebuild...\n";

// Pass the server's configured timezone, or default to UTC
$timezone = 'America/Phoenix'; // Adjust this to your server's timezone
$results = RebuildAvailabilityThisWeek($timezone);

echo "[" . date('Y-m-d H:i:s') . "] Done. Processed {$results['total']} artists, updated {$results['updated']}, skipped {$results['skipped']}.\n";

if (!empty($results['errors'])) {
    foreach ($results['errors'] as $err) {
        echo "  ERROR: {$err}\n";
    }
}

exit(0);
