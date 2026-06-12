<?php
/**
 * ajax/getCommentsIsland.php
 * 
 * Thin AJAX endpoint that returns the HTML for a comments floating island.
 * Called from SeeComments.js (elFinder) via $.get().
 */

if(!defined('__ROOT__')) {
    define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
}
require_once __ROOT__ . '/libraries/session.php';
require_once __ROOT__ . '/libraries/floatingIslandLib.php';

$filepath = $_GET['filepath'] ?? '';
if (empty($filepath)) {
    echo '<p class="seecm-status-error">No file specified.</p>';
    exit;
}

echo LoadCommentsIsland($filepath);
