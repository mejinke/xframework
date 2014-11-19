<?php
class IndexController extends XF_Controller_Abstract
{
	public function __construct()
	{
		parent::__construct($this);
	}
	
	public function indexAction()
	{
		$this->_view->title = 'welcome to xframework2';
	}
}