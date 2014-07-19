<?php
// Debug Switch
!defined('IS_DEBUG') && define('IS_DEBUG', 0);
// Document Root
!defined('DOCUMENT_ROOT') && define('DOCUMENT_ROOT', isset( $_SERVER['DOCUMENT_ROOT'] ) ? $_SERVER['DOCUMENT_ROOT'] : PUBLIC_PATH);

// URI Var
define('VAR_CONTROLLER',  'c');
define('VAR_METHOD',      'a');
define('VAR_PAGE',        'p');

// Controller Var
define('CONTROLLER_NAME',      coreGetControllerName());
define('ACTION_NAME',          coreGetMethodName());

// System Paths
define('CACHE_PATH',    APPLICATION_PATH . '/cache');
define('CONFIG_PATH',   APPLICATION_PATH . '/config');
define('LIB_PATH',      APPLICATION_PATH . '/Lib');
define('VENDER_PATH',   APPLICATION_PATH . '/Vender');
define('VIEW_PATH',     APPLICATION_PATH . '/view');
define('HTML_PATH',     PUBLIC_PATH . '/html');
define('UPLOAD_PATH',   PUBLIC_PATH . '/upload');

// System URI
define('SITE_URI',          isset( $_SERVER['REQUEST_URI'] ) ? dirname( array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) ) : str_replace( DOCUMENT_ROOT, '', PUBLIC_PATH ));
define('STATIC_URI',        SITE_URI . '/static');
define('JAVASCRIPT_URI',    STATIC_URI . '/js');
define('CSS_URI',           STATIC_URI . '/css');
define('IMAGE_URI',         STATIC_URI . '/img');
define('UPLOAD_URI',        SITE_URI . '/upload');

// Core Methods
function coreGetControllerName()
{
    // 过滤“.”，即禁止“相对路径”
    $controller = empty($_GET[VAR_CONTROLLER]) ? 'Index' : str_replace(array('.'), '', $_GET[VAR_CONTROLLER]);
    unset($_GET[VAR_CONTROLLER]);
    return $controller;
}

function coreGetMethodName()
{
    $method = empty($_GET[VAR_METHOD]) ? 'index' : $_GET[VAR_METHOD];
    unset($_GET[VAR_METHOD]);
    return $method;
}