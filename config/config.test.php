<?php
return array(
	// 错误等级
	'error_reporting' => 0,
	// 时区
	'date_timezone' => 'Asia/Shanghai',
	// 数据库
	'db' => array(
		'r_host' => '127.0.0.1',
		'w_host' => '127.0.0.1',
		'port' => 3306,
		'username' => 'root',
		'password' => '223238',
		'dbname' => 'cms',
		'charset' => 'UTF8'
	),
	// 存放Redis 配置
	'redis' => array(
		'series_movie_like' => array(
	        'host' => '192.168.200.50',
	        'port' => 6379,
	        'timeout' => 600,
		),
		'series_cache' => array(
	        'host' => '127.0.0.1',
	        'port' => 6382,
	        'timeout' => 600,
		),
		'movie_data' => array(
	        'host' => '192.168.200.36',
	        'port' => 6378,
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
