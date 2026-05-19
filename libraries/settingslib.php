<?php
function ArtistSettingsErrorDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:red;">Error: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

function ArtistSettingsSuccessDisplay($inputMessage){
if($inputMessage == ""){ return "";}
echo '<div class="module-card module-card--span-4">';
echo '<center><h1 style="color:green;">Success: ' . $inputMessage;
echo '</h1></center>';
echo '</div>';
}

