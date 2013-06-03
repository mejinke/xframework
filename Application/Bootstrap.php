<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-06
 * -+-----------------------------------
 * 
 * @desc 框架启动引导类
 * @author jingke
 */
class XF_Application_Bootstrap
{

	/**
	 * application
	 * @var XF_Application
	 */
	protected $_application;
	
	/**
	 * 构造函数
	 * @param XF_Application $application
	 */
	public function __construct(XF_Application $application)
	{
		$this->_application = $application;
	}
	
	/**
	 * 调用用户自定义的init函数
	 * @return void
	 */
	private function _loadInit()
	{
		$ref = new ReflectionClass($this);
		$methods = $ref->getMethods();
		foreach ($methods as $m)
		{
			if (strpos($m->name, 'init') === 0)
				$this->{$m->name}();
		}
	}

	/**
	 * 运行启动器前执行接口
	 * @return void
	 */
	public function runStartup(){}
	
	/**
	 * 运行启动器
	 * @return void
	 */
	public function run()
	{
		$this->runStartup();
		if (!empty($_GET['xsession_id']))
		{
			$session_id = XF_Functions::authCode(urlencode($_GET['xsession_id']), 'DECODE');
			if ($session_id !='')
				session_id($session_id);
		}

		session_start();
		$this->_loadInit();
		XF_Controller_Front::getInstance()->dispatch();
	}
}