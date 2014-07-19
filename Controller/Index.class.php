<?php
class Controller_Index extends Core_Controller
{
    public function indexAction()
    {
    	$this->view->display( 'index' );
    }

    public function testAction()
    {
    	dump( __FUNCTION__ );
    	$this->index();
    }
}