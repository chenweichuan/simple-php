<?php
abstract class Core_Core
{
    static private $_instances = array();

    // 错误信息
    protected $errno  = null;
	protected $errmsg = null;

    // 获得调用该静态方法的类的一个实例
    static public function & getInstance()
    {
        $class_name = get_called_class();
        $arguments = func_get_args();
        if ( empty( $arguments ) ) {
            // Controller 和View 不进行缓存
            if ( 0 === strpos( 'Controller', $class_name ) || 'Core_View' === $class_name ) {
                $object = new $class_name();
            } else {
                isset( self::$_instances[$class_name] ) || self::$_instances[$class_name] = new $class_name();
                $object = & self::$_instances[$class_name];
            }
        } else {
            // 带初始化参数的类不缓存
            $class = new ReflectionClass( $class_name );
            $object = & $class->newInstanceArgs( $arguments );
        }
        return $object;
    }

    /**
     * 返回错误码
     *
     * @return string 
     * @author EasyChen
     */
    public function errno()
    {
        return $this->errno;
    }

    /**
     * 返回错误信息
     *
     * @return string 
     * @author EasyChen
     */
    public function errmsg()
    {
        return $this->errmsg;
    }
}