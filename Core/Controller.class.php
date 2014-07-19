<?php
abstract class Core_Controller extends Core_Core
{
    public function __construct()
    {
    }

    public function __call( $method, $args )
    {
        $class_name = get_called_class();
        throw new Core_Exception("Class '{$class_name}' does not have the method '{$method}'");
    }

    public function __get( $name )
    {
        // 视图实例
        switch ( $name ) {
            case 'view':
                $this->view = new Core_View();
                // 设置模板的relative_dir 为调用该模板的Controller 的controller name
                $class_name = get_called_class();
                $controller_name = str_replace( '_', '/', substr( $class_name, strpos( $class_name, '_' ) + 1 ) );
                $this->view->relative_dir = $controller_name;
                return $this->view;
            default:
                return null;
        }
    }

    public function __set( $name, $value )
    {
        $this->$name = $value;
    }
}