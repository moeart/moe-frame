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
// Load other configuration files before route.inc.php
foreach (glob("../conf/*.php") as $filename) {
    if (basename($filename) != 'route.inc.php') {
        require_once $filename;
    }
}
// Create Router instance and load routes from route.inc.php
global $MoeRouter;
$MoeRouter = new MoeRouter();
require_once "../conf/route.inc.php";

$MoeApps = new MoeApps();
$MoeApps->abort(404, '', 'Route Not Found!');
?>