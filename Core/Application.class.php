<?php
class Core_Application
{
    static public function run()
    {
        error_reporting( C( 'error_reporting' ) );
        session_start();
        date_default_timezone_set( C( 'date_timezone' ) );

        register_shutdown_function( array( 'Core_Exception', 'handleFatalError' ) );
        set_error_handler( array( 'Core_Exception', 'handleError' ) );
        set_exception_handler( array( 'Core_Exception', 'handleException' ) );

        // If already slashed, strip it
        if ( get_magic_quotes_gpc() ) {
            $_GET    = stripslashes_deep( $_GET );
            $_POST   = stripslashes_deep( $_POST );
            $_COOKIE = stripslashes_deep( $_COOKIE );
        }

        // 链接数据库
        Core_Model::connectDb();

        $controller_class_name = 'Controller_' . str_replace( '/', '_', CONTROLLER_NAME );
        $method_name     = ACTION_NAME . 'Action';
        $controller      = new $controller_class_name();

        // 禁止直接调用基类Core_Controller的方法
        if ( in_array( $method_name, get_class_methods( 'Core_Controller' ) ) ) {
            throw new Core_Exception("Method '{$method_name}' access denied");
        }
        // Controller 不存在
        if ( ! $controller ) {
            throw new Core_Exception("Fail to new {$controller_class_name} object");
        }

        // 给cli 模式下来个起始换行 = =！
        IS_CLI && print( "\n" );

        // 页面缓存 [ CLI 模式不缓存 ]
        IS_CLI || ob_start();
        // 执行操作
        call_user_func(array($controller, $method_name));
        // 页面输出 [ CLI 模式没缓存 ]
        IS_CLI || ob_end_flush();

        // 断开数据库
        Core_Model::closeDb();

        // 打印错误信息
        IS_DEBUG && Core_Exception::showErrors();

        // 给cli 模式下来个结束换行 = =！
        IS_CLI && print( "\n" );
    }
}