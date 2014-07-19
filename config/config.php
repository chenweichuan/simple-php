<?php
return array(
	// 错误等级
	'error_reporting' => E_ALL,
	// 时区
	'date_timezone' => 'Asia/Shanghai',
	// 数据库
	'db' => array(
		'r_host' => '127.0.0.1',
		'w_host' => '127.0.0.1',
		'port' => 3306,
		'username' => 'root',
		'password' => '',
		'dbname' => 'cms',
		'charset' => 'UTF8'
	),
	// 存放Redis 配置
	'redis' => array(
		'series_movie_like' => array(
	        'host' => '127.0.0.1',
	        'port' => 6380,
	        'timeout' => 600,
		),
		'series_cache' => array(
	        'host' => '127.0.0.1',
	        'port' => 6380,
	        'timeout' => 600,
		),
		'movie_data' => array(
	        'host' => '127.0.0.1',
	        'port' => 6383,
	        'timeout' => 600,
		),
	),
	// COOKIE 配置
	'cookie' => array(
        'prefix' => '&$^JFHG5r7f756%$R', // cookie 名称前缀
        'expire' => 86400, // cookie 保存时间
        'path'   => '/',   // cookie 保存路径
        'domain' => '', // cookie 有效域名
	),
	// 密钥
	'sercure_key' => 'kjsdfKJHKJH*&jhg%V(@@%^ybhj%^JK)CDE#'
);
