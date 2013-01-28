<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jinke
 * Date: 12-12-23
 * Time: 下午8:59
 * To change this template use File | Settings | File Templates.
 */
class IndexController extends  XF_Controller_Abstract
{

    public function __construct()
    {
        parent::__construct($this);
    }

    public function indexAction()
    {
    	ECHO '111';
    	$this->_view->headTitle('test page');
    	$this->setLayout(new Layout_Default());
		$this->setCacheTime(1);
    }
    
    public function aAction()
    {
    	
    }
}
