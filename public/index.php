<?php
/**
 * Moe PHP Framework Loader
 * unstable version
 */
 
// Turn off all error reporting temporarily
error_reporting(0);
ini_set('display_errors', 0);
 
// Load Libraries
foreach (glob("../lib/*.php") as $filename) {
    require_once $filename;
}

// Register custom error handlers after loading libraries
// Set custom error handler
set_error_handler('mf_error_handler');

// Set custom exception handler
set_exception_handler('mf_exception_handler');

// Set custom shutdown function
register_shutdown_function('mf_shutdown_function');

// Set error reporting level (show all errors except notices)
error_reporting(E_ALL & ~E_NOTICE);

// Do not display errors directly (they will be handled by our custom handler)
ini_set('display_errors', 0);

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