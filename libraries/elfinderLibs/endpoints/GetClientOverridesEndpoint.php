<?php
/**
 * API endpoint that has ONE JOB. To return how many lock overrides a client has.
 * Called via AJAX from elFinder frontend.
 * Actions:
 *   No actions. It knows what it's about.
 * Client only.
 */

if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
header('Content-Type: application/json');
$sendArrayToJS = [
    'success' => true,
    'overrides' => GetClientLockOverrides($_SESSION['username'] ?? '')
];
echo json_encode($sendArrayToJS);