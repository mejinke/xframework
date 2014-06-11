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
	 * 是否自动按顺序执行init方法?
	 * @var bool
	 */
	private $_auto_call = TRUE;
	
	/**
	 * 关闭自动执行init方法
	 * @access protected
	 * @return void
	 */
	protected function _closeAutoCall()
	{
		$this->_auto_call = FALSE;	
	}
	
	/**
	 * 当前请求域名是否合法
	 * @access private
	 * @return void
	 */
	private function checkDomain()
	{
		$domain = XF_Config::getInstance()->getDomain();
		if (empty($domain)) return;
		if (strpos($_SERVER['HTTP_HOST'], $domain) === FALSE)
		{
			throw new XF_Application_Exception('当前请求的域名不正确');
		}
	}
	
	/**
	 * 调用用户自定义的init函数
	 * @access private
	 * @return void
	 */
	private function _loadInit()
	{
		$ref = new ReflectionClass($this);
		$methods = $ref->getMethods();
		foreach ($methods as $m)
		{
			if ($this->_auto_call === FALSE)
				break;
			if (strpos($m->name, 'init') === 0)
				$this->{$m->name}();
		}
	}

	/**
	 * 运行启动器前执行接口
	 * @access public
	 * @return void
	 */
	protected function runStartup(){}
	
	/**
	 * 运行启动器
	 * @access public
	 * @return void
	 */
	public function run()
	{
		$st = microtime(true);
		$this->checkDomain();
		$this->runStartup();
		if (!empty($_GET['xsessid']))
		{
			$session_id = XF_Functions::authCode(urlencode($_GET['xsessid']), 'DECODE');
			if ($session_id !='')
				session_id($session_id);
		}
		session_start();
		header("Content-type:text/html;charset=utf-8");
		$this->_loadInit();
		XF_DataPool::getInstance()->add('RunBootstrap', sprintf("%.6f", microtime(true) - $st));
	    XF_Controller_Front::getInstance()->init()->dispatch();
	}
}