<?php
class Core_Exception extends Exception
{
    static private $_errors = array();
    static private $_error_reporting = null;
    
    static public function handleError( $err_level, $err_message, $err_file, $err_line, $err_context )
    {
        // 不在error_reporting 里的非用户非致命错误级别不做处理
        isset( self::$_error_reporting ) || self::$_error_reporting = error_reporting();
        if ( ! ( self::$_error_reporting & $err_level ) && ! in_array( $err_level, array( E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE ) ) ) {
            return ;
        }

        if ( IS_DEBUG ) {
            self::$_errors[] = '==== handleError ====';
            self::$_errors[] = array(
                'level' => $err_level,
                'message' => $err_message,
                'file' => $err_file,
                'line' => $err_line,
                'context' => $err_context,
            );
        }

        // 转换为相对路径，降低log 长度=。=！
        $err_file = str_replace( APPLICATION_PATH , '', $err_file );

        switch ( $err_level ) {
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                Core_Log::getInstance()->error( "{$err_message} LEVEL:{$err_level} FILE:{$err_file} LINE:{$err_line}" );
                // 终止运行，故在此输出错误信息
                self::showErrors();
                exit;
                break;
            default:
                Core_Log::getInstance()->warn( "{$err_message} LEVEL:{$err_level} FILE:{$err_file} LINE:{$err_line}" );

        }
    }
    
    static public function handleException( $e )
    {
        $err_code = $e->getCode();
        $err_message = $e->getMessage();
        $err_file = $e->getFile();
        $err_line = $e->getLine();

        if ( IS_DEBUG ) {
            self::$_errors[] = '==== handleException ====';
            self::$_errors[] = array(
                'code' => $err_code,
                'message' => $err_message,
                'file' => $err_file,
                'line' => $err_line,
                'trace' => $e->getTrace(),
            );
            // Exception 终止运行，故在此输出错误信息
            self::showErrors();
        } else {
            IS_CLI ? print( $err_message ) : ( isAjax() ? ajaxReturn( 0, '服务器端错误' ) : redirect( SITE_URI . '/error.html' ) );
        }
        // 转换为相对路径，降低log 长度=。=！
        $err_file = str_replace( APPLICATION_PATH , '', $err_file );
        Core_Log::getInstance()->error( "{$err_message} CODE:{$err_code} FILE:{$err_file} LINE:{$err_line}" );
    }

    // 致命错误捕获
    static public function handleFatalError()
    {
        if ( $err = error_get_last() ) {
            // 转换为相对路径，降低log 长度=。=！
            $err['file'] = str_replace( APPLICATION_PATH , '', $err['file'] );
            Core_Log::getInstance()->error( "{$err['message']} TYPE:{$err['type']} FILE:{$err['file']} LINE:{$err['line']}" );
        }
    }

    static public function showErrors()
    {
        array_map( 'dump', self::$_errors );
    }
}