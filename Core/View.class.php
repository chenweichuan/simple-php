<?php
class Core_View
{
    // 以VIEW_PATH 为根目录的相对路径目录
    public $relative_dir = null;

    public function __construct()
    {
        // 默认Smarty 模板引擎
        $this->_engine = new Smarty();
        $this->_engine->template_dir = VIEW_PATH;
        $this->_engine->compile_dir  = CACHE_PATH . '/view_c';
    }

    public function success($message = '', $ajax_data = null)
    {
        isAjax() ? ajaxReturn(true, $message, $ajax_data) : $this->_jump(true, $message, $ajax_data);
    }
    
    public function error($message = '', $ajax_data = null)
    {
        isAjax() ? ajaxReturn(false, $message, $ajax_data) : $this->_jump(false, $message, $ajax_data);
    }
    
    /**
     * 赋值模版变量
     */
    public function assign( $name, $value = null )
    {
        return $this->_engine->assign( $name, $value );
    }

    /**
     * 获取模版变量的值
     */
    public function getTemplateVars( $name = null )
    {
        return $this->_engine->getTemplateVars( $name );
    }

    /**
     * 清除所有的模版变量赋值
     */
    public function clearAllAssign( $name = null )
    {
        return $this->_engine->clearAllAssign( $name );
    }

    /**
     * 加载模版, 渲染输出页面
     */
    public function display($template_name = null)
    {
        $this->_engine->display( $this->_getTplPath($template_name) );
    }

    /**
     * 加载模版
     */
    public function fetch($template_name = null)
    {
        return $this->_engine->fetch( $this->_getTplPath($template_name) );
    }
    
    private function _jump($status = true, $message = '', $ajax_data = null)
    {
        if ($this->getTemplateVars('close_window'))
            $this->assign('jump_url', 'javascript:window.close();');
            
        $this->assign('status', $status);
        $this->assign('message', $message);
        
        if ($status) {
            if (!$this->getTemplateVars('jump_url'))
                $this->assign('jump_url', uri());
            if (!$this->getTemplateVars('wait_second'))
                $this->assign('wait_second', 1);
        }else {
            if (!$this->getTemplateVars('jump_url'))
                $this->assign('jump_url', 'javascript:history.back(-1);');
            if (!$this->getTemplateVars('wait_second'))
                $this->assign('wait_second', 5);
        }
        
        $this->display( 'Public/redirect');
    }

    /**
     *  获取模板文件路径
     *
     *  @param string $template 若第一个字符为“/” ，则视为相对VIEW_PATH 的路径；否，则视为相对$this->relative_dir
     */
    private function _getTplPath( $template = null )
    {
        if ( 0 !== strpos( $template, '/' ) ) {
            !$template && $template = ACTION_NAME;
            $template_path = $this->relative_dir . "/{$template}.tpl";
        } else {
            $template_path = substr( $template, 1 ) . '.tpl';
        }
        return $template_path;
    }
}