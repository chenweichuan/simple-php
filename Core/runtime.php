<?php
/**
 * 核心运行时文件
 * 
 * 1. 加载系统运行必须的常量定义文件、配置类、核心函数库
 * 2. 注册autoload
 */

// 指定字符集
header("Content-Type:text/html; charset=utf-8");

// Is cli?
define( 'IS_CLI', 'cli' === PHP_SAPI );
// cli 模式处理
IS_CLI && require APPLICATION_PATH . '/Core/cli.php';

// 系统常量定义
require APPLICATION_PATH . '/Core/define.php';
// 系统函数库
require APPLICATION_PATH . '/Core/functions.php';

// register autoload
// diy
spl_autoload_register('coreAutoloader');
function coreAutoloader( $class_name )
{
    $file = APPLICATION_PATH . '/' . str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.class.php';
    file_exists( $file ) && include $file;
}
// from composer
require APPLICATION_PATH . '/vendor/autoload.php';
