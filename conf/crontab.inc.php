<?php
/**
 * Linux 风格的计划任务配置文件
 */

// 创建 MoeCrontab 实例
global $MoeCrontab;
$MoeCrontab = new MoeCrontab();

// 示例规则：每5分钟执行一次 ExampleApp@Hello
$MoeCrontab->C('* * * * *', 'ExampleApp@Hello');

?>