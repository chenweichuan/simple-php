<?php
class Template_Simple
{
    // 模板目录
    public $template_dir = null;

    // 模板变量
    protected $_template_vars = array();

    /**
     * 赋值模版变量
     */
    public function assign($name, $value = null)
    {
        if (is_array($name))
            $this->_template_vars = array_merge($this->_template_vars, $name);
        else if (is_object($name))
            foreach ($name as $k => $v)
                $this->_template_vars[$k] = $v;
        else
            $this->_template_vars[$name] = $value;
    }
    
    /**
     * 获取模版变量的值
     */
    public function get_template_vars( $name )
    {
        if ( $name ) {
            return $this->_template_vars[$name];
        } else {
            return $this->_template_vars;
        }
    }
    
    /**
     * 加载模版, 渲染输出页面
     */
    public function display($template_path)
    {
        $result = $this->fetch($template_path);
        echo $result;
    }
    
    /**
     * 加载模版
     */
    public function fetch($template_path)
    {
        $template_real_path = $this->_getTemplateRealPath( $template_path );

        if (!is_file($template_real_path))
            throw new Core_Exception("Template \"{$template}\" not exist");
        
        if (!headers_sent()) {
            header("Content-Type:text/html; charset=utf-8");
            header("Cache-control: private"); // 支持页面回跳
        }

        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        // 导入变量
        extract($this->_template_vars, EXTR_OVERWRITE);
        // 载入模版文件
        include $template_real_path;

        // 输入
        return ob_get_clean();
    }

    /**
     * 清除模板变量
     *
     */
    public function clear_all_assign()
    {
        $this->_template_vars = array();
    }

    private function _getTemplateRealPath( $template_path )
    {
        return $this->template_dir . '/' . $template_path;
    }
}