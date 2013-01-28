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
abstract class XF_Controller_Easy
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
	 * 执行控制器动作
	 * @return string
	 */
	public function doAction()
	{
		$method = $this->_request->getAction().'Action';
		if ($this->hasAction($method))
		{
			call_user_func(array($this->_controller,$method));
		}
	}
	
	/**
	 * 是否存在Action
	 * @param string $action_name 动作名称[默认为当前请求的Action]
	 * @return bool
	 */
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
	
	/**
	 * 获取参数值
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key , $default = NULL)
	{
		return $this->_request->getParam($key, $default);
	}
	
	/**
	 * 获取所有参数列表
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->_request->getParams();
	}	
}