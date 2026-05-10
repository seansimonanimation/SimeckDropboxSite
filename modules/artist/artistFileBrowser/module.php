<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistFileBrowser
 * @name FileBrowser
 * @role artist
 * @nav-text File Browser
 * @nav-icon Files
 * @nav-order 10
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/elfinderlib.php';





?>


<?php echo displayArtistFileBrowser(); ?>