<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-10
 * -+-----------------------------------
 * 
 * @desc 简单的控制器基类
 * @author jingke
 */
abstract class XF_Controller_Easy implements XF_Controller_Interface
{

	/**
	 * Request.
	 * @access protected
	 * @var XF_Controller_Request_Http
	 */
	protected $_request;
	
	/**
	 * 控制器
	 * @access priviate
	 * @var XF_Controller_Abstract
	 */
	private $_controller;
	
	protected function __construct(XF_Controller_Easy $controller)
	{
		$this->_controller = $controller;
		$this->_request = XF_Controller_Request_Http::getInstance();
	}
	
	/**
	 * 获取额外的操作数据
	 * @access public
	 * @param string $name 数据名称
	 * @return mixed
	 */
	public function __get($name)
	{
		return XF_Controller_Front::getInstance()->getHandleData($name);
	}
	
	public function doAction()
	{
		$method = $this->_request->getAction().'Action';
		if ($this->hasAction($method))
		{
			call_user_func(array($this->_controller,$method));
		}
		else
		{
			throw new XF_Controller_Exception('Action 不存在', 404);
		}
	}

	
	public function hasAction($action_name)
	{
		$this->_checkControllerInstance();
		$methods = get_class_methods($this->_controller);
		return array_search($action_name, $methods);
	}
	
	/**
	 * 控制器实例是否为空？
	 * @return void
	 */
	protected function _checkControllerInstance()
	{
		if ($this->_controller == null)
			throw new XF_Controller_Exception('Controller instance invalid!');
	}
	
	public function getParam($key , $default = NULL)
	{
		return $this->_request->getParam($key, $default);
	}

	public function getParamNumber($key, $default = 0)
	{
		$val = $this->_request->getParam($key, $default);
		if ($default == $val) return $val;
		return is_numeric($val) ? floatval($val) : $default;
	}
	
	public function getParams()
	{
		return $this->_request->getParams();
	}	
}