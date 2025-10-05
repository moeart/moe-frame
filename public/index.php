<?php
/**
 * Moe PHP Framework Loader
 * unstable version
 */
 
// Load Libraries
foreach (glob("../lib/*.php") as $filename) {
    require_once $filename;
}

// Load Apps
foreach (glob("../app/*.php") as $filename) {
    require_once $filename;
}

// Load Configuration files
foreach (glob("../conf/*.php") as $filename) {
    require_once $filename;
}

$MoeApps = new MoeApps();
$MoeApps->abort(404, '', 'Route Not Found!');
?>