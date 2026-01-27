<?php
/**
 * Crontab Configuration
 */

// Create MoeCrontab instance
global $MoeCrontab;
$MoeCrontab = new MoeCrontab();

// Add a crontab job: every 5 minutes, run ExampleApp@Hello
$MoeCrontab->C('*/5 * * * *', 'ExampleApp@Hello');

?>