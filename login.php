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



$sdb1 = array();
$sdb2 = array();
$error = '';

include("login.conf.php");
session_start();
?>