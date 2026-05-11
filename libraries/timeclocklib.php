<?php
include_once __DIR__ . '/../../../libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';



function GenerateTimeclockTable(){

return GetTimeclockEntries();
}

?>