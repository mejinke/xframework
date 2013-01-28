<?php
class Layout_Default extends XF_View_Layout_Abstract
{
	
	public function __construct()
	{
		$this->_tpl = 'default.php';
		$this->_cacheTime = 1;
	}
	
	public function _init()
	{
		
	}
}