<?php
return array(
	// 错误等级
	'error_reporting' => 0,
	// 时区
	'date_timezone' => 'Asia/Shanghai',
	// 数据库
	'db' => array(
		'r_host' => '192.168.1.67',
		'w_host' => '192.168.1.67',
		'port' => 3306,
		'username' => 'root',
		'password' => '223238',
		'dbname' => 'cms',
		'charset' => 'UTF8'
	),
	// 存放Redis 配置
	'redis' => array(
		'series_movie_like' => array(
	        'host' => '114.112.70.73',
	        'port' => 6381,
	        'timeout' => 600,
        	'password' => 'hkqnUrN>--gx,!=m~s8d,P[xB6|V_=ed:s{,!H4VA<e5EM\vqHe,yP$hmn[s,)i',
		),
		'series_cache' => array(
	        'host' => '127.0.0.1',
	        'port' => 6382,
	        'timeout' => 600,
        	'password' => '*pq4O|*b(+fV+![(8C1K?>8y!9IW,>ZP)b_X/-O!$B%&0~cCIthRy0',
		),
		'movie_data' => array(
	        'host' => '127.0.0.1',
	        'port' => 6383,
	        'timeout' => 600,
		),
	),
	// COOKIE 配置
	'cookie' => array(
        'prefix' => '&$l(*&^ksi87sihf756%$R', // cookie 名称前缀
        'expire' => 86400, // cookie 保存时间
        'path'   => '/',   // cookie 保存路径
        'domain' => '', // cookie 有效域名
	),
	// 密钥
	'sercure_key' => 'kjs)%#HHKsdfjk%^%hJG:g&*&^JH*V(@@%^yblsf[8v^JK)CDE#'
);
