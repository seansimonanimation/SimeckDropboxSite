<?php
/**
 * getDiscordIsland.php
 * 
 * AJAX endpoint that returns the HTML for a Send-to-Discord floating island.
 * Called from the consolidated SendToDiscord elFinder command.
 */
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __DIR__ . '/../../session.php';
require_once __ROOT__ . '/libraries/floatingIslandLib.php';

// Accept files as JSON either from POST body or GET parameter
$filesJson = $_POST['files'] ?? $_GET['files'] ?? '';
$files = json_decode($filesJson, true);

if (empty($files) || !is_array($files)) {
    echo LoadSendToDiscordIsland([]);
    exit;
}
$folderHash = $_POST['folderHash'] ?? '';
echo LoadSendToDiscordIsland($files, $folderHash);
