<?php
include_once __DIR__ . '/../libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/auth.php';



function GenerateTimeclockTable(){

$entryArray = GetTimeclockEntries();
echo "<table width='100%' border='1' style='border-collapse: collapse;'>";
foreach($entryArray as $entry){
    echo "<tr>";
    echo "<td>" . $entry['name'] . "</td>";
    echo "<td>" . $entry['time'] . "</td>";
    echo "<td>" . $entry['in_out'] . "</td>";
    echo "</tr>";
}
echo "</table>";

}

?>