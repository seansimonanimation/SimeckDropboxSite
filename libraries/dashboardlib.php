<?php 
//Contains all functions related to the dashboard modules, which are used in the admin, client, and artist dashboards.


include_once (__DIR__ . '/session.php');
include_once (__DIR__ . '/db.php');


function DisplayChangelog(){
    //Simply reads the changelog.txt file and returns it as a string to be displayed on the dashboard.
    return file_get_contents(__ROOT__ .'/changelog.txt');
}