<?php
//
// login.php - Login page for Simeck Entertainment's Dropbox
// This page is a shared login page for all roles.
//
// Auth sequence:
// 1) Try artist/admin against simeckdb.artists table (sha512-crypt)
// 2) On failure, assume client and try against simeckdb.clients table (sha512-crypt)
// 3) On success, set session and redirect to index.php
//

require_once __DIR__ . '/libraries/db.php';
require_once __DIR__ . '/libraries/auth.php';
require_once __DIR__ . '/libraries/logging.php';
require_once __DIR__ . '/libraries/helpers.php';


$sdb1 = array();
$sdb2 = array();
$error = '';

include("login.conf.php");
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST") {
csrf_validate($_POST['_csrf_token'] ?? '');
post_func(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
}

function post_func(string $username, string $password){

}

?>