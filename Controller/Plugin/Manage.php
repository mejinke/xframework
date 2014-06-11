<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-11
 * -+-----------------------------------
 * 
 * @desc 插件管理类
 * @author jingke
 */
class XF_Controller_Plugin_Manage
{
	/**
	 * 当前实例
	 * @access private
	 * @var XF_Controller_Plugin_Manage
	 */
	private static $_instance;
	
	/**
	 * 插件集合
	 * @var array
	 */
	protected $_plugins = array();
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前实例
	 * @access public
	 * @return XF_Controller_Plugin_Manage
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 注册插件
	 * @access public
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return XF_Controller_Plugin_Manage
	 */
	public function registerPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		$status = FALSE;
		foreach ($this->_plugins as $_plugin)
		{
			if ($plugin == $_plugin)
				$status = TRUE;
		}
		if (!$status)
			$this->_plugins[] = $plugin;
			
		return $this;
	}
	
	/**
	 * 删除插件
	 * @access public
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return XF_Controller_Plugin_Manage
	 */
	public function unregisterPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		foreach ($this->_plugins as $key => $_plugin)
		{
			if ($plugin == $_plugin)
				unset($this->_plugins[$key]);
		}
		return $this;
	}
	
	/**
	 * 是否存在指定的插件
	 * @access public
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return bool
	 */
	public function hasPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		$status = FALSE;
		foreach ($this->_plugins as $key => $_plugin)
		{
			if ($plugin == $_plugin)
				$status = TRUE;
		}
		return $status;
	}
	
	/**
	 * 获取所有的插件
	 * @access public
	 * @return array
	 */
	public function getPlugins()
	{
		return $this->_plugins;
	}
	
	/**
     * 路由开始执行时调用
     * @access public
     * @param XF_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->routeStartup($request);
		}
    }

    /**
     * 路由完成时调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->routeShutdown($request);
		}
    }

    /**
     * 转发控制器之前调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->preDispatch($request);
		}
    }

    /**
     * 转发控制器之后调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->postDispatch($request);
		}
    }
    
	/**
     * Action执行之前调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function preAction(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->preAction($request);
		}
    }

    /**
     * Action执行之后调用[渲染模板之前]
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function postAction(XF_Controller_Request_Abstract $request)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->postAction($request);
		}
    }
    
	/**
     * 模板渲染完成之后[输出之前]
     * @access public
     * @param string $html 将要输出到前台的内容
     * @return string 将要输出到前台的内容
     */
    public function postRender(&$html)
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->postRender($html);
		}
    }
    
	/**
     *  模板渲染完成并完成输出之后
     * @access public
     * @return void
     */
    public function postOutput()
    {
    	foreach ($this->_plugins as $key => $plugin)
		{
			$plugin->postOutput();
		}
    }
    
	/**
     * 404时调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function exception404(XF_Controller_Request_Abstract $request, Exception $e = null)
    {	
    	$emptyPlugin = true;
    	
    	foreach ($this->_plugins as $key => $plugin)
		{
			$selfCalssName = get_class($plugin);
			$ref = new ReflectionClass($plugin);
			$methods = $ref->getMethods();
			foreach ($methods as $m)
			{
				if ($m->name == 'exception404' && $m->class == $selfCalssName )
				{
					$emptyPlugin = false;
					$plugin->exception404($request);
					break;
				}
			}		
		}
		if ($emptyPlugin == true)
		{
			if ($e == null)
				echo new XF_Exception('404 Not found', 404);
			else
				throw $e;	
		} 		
    }
    
	/**
     * 全局异常
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @param  XF_Exception $e
     * @return void
     * @throws XF_Exception
     */
    public function exception(XF_Controller_Request_Abstract $request, Exception $e)
    {
    	if ($e->getCode() == '404')
    	{
    		$this->exception404($request, $e);
    		return;
    	}
    		
    	$emptyPlugin = true;
    	
    	foreach ($this->_plugins as $key => $plugin)
		{
			$selfCalssName = get_class($plugin);
			$ref = new ReflectionClass($plugin);
			$methods = $ref->getMethods();
			foreach ($methods as $m)
			{
				if ($m->name == 'exception' && $m->class == $selfCalssName )
				{
					$emptyPlugin = false;
					$plugin->exception($request, $e);
					break;
				}
			}
				
		}
		if ($emptyPlugin == true) echo $e;
    }
}