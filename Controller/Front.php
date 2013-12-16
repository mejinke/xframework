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
 * @desc 应用程序前端控制器
 * @author jingke
 */
class XF_Controller_Front
{
	
	/**
	 * 当前实例
	 * @access private
	 * @var XF_Controller_Front
	 */
	private static $_instance;
	
	/**
	 * 模块路径
	 * @access protected
	 * @var string
	 */
	protected $_module_dir = null;
	
	/**
	 * 控制器路径
	 * @access protected
	 * @var string
	 */
	protected $_controller_dir = null;
	
	/**
	 * 已或将要转发的控制器实例
	 * @access protected
	 * @var XF_Controller_Abstract
	 */
	protected $_controller_instance = NULL;
	
	/** 
	 * 将要转发的控制器中可直接使用的数据
	 * @access protected
	 * @var array
	 */
	protected $_controller_handle_data = array();
	
	/**
	 * Request
	 * @access protected
	 * @var XF_Controller_Request_Abstract
	 */
	protected $_request = null;
	
	/**
	 * 路由器
	 * @access protected
	 * @var XF_Controller_Router
	 */
	protected $_router = null;
	
	/**
	 * 插件管理
	 * @access protected
	 * @var XF_Controller_Plugin_Manage
	 */
	protected $_plugin_manage = array();

	/**
	 * 控制器转发次数
	 * @var int
	 */
	protected $_dispath_count = 0;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前实例
	 * @return XF_Controller_Front
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
	 * 初始化相应必要的对象
	 * @access public
	 * @return XF_Controller_Front
	 */
	public function init()
	{
		if ($this->_router == NULL)
		{
			$this->_router = new XF_Controller_Router();
			$this->_plugin_manage = XF_Controller_Plugin_Manage::getInstance();
			$this->_request = XF_Controller_Request_Http::getInstance();
		}
		return $this;
	}
	
	/**
	 * 获取路由
	 * @return XF_Controller_Router
	 */
	public function getRouter()
	{
		return $this->_router;
	}
	
	/**
	 * 获取模块路径
	 * @return string
	 */
	public function getModuleDir()
	{
		$this->_module_dir = APPLICATION_PATH;
		if (ucfirst($this->_request->getModule()) !='Default')
		{
			$this->_module_dir = APPLICATION_PATH.'/modules/'.$this->_request->getModule();
		}
		return $this->_module_dir;
	}
	
	/**
	 * 添加控制器中可直接使用的数据
	 * @access public
	 * @param string $name 数据名称
	 * @param mixed $data 数据资料
	 * @return XF_Controller_Front
	 */
	public function addHandleData($name, $data)
	{
		$this->_controller_handle_data[$name] = $data;
		return $this;
	}
	
	/**
	 * 获取控制器中可直接使用的数据
	 * @access public
	 * @param string $name 数据名称
	 * @return mixed
	 */
	public function getHandleData($name)
	{
		return isset($this->_controller_handle_data[$name]) ? $this->_controller_handle_data[$name] : NULL;
	}
	
	/**
	 * 注册插件
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return XF_Controller_Front
	 */
	public function registerPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		$this->_plugin_manage->registerPlugin($plugin);
		return $this;
	}
	
	/**
	 * 删除插件
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return XF_Controller_Front
	 */
	public function unregisterPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		$this->_plugin_manage->unregisterPlugin($plugin);
		return $this;
	}
	
	/**
	 * 是否存在指定的插件
	 * @param XF_Controller_Plugin_Abstract $plugin
	 * @return bool
	 */
	public function hasPlugin(XF_Controller_Plugin_Abstract $plugin)
	{
		return $this->_plugin_manage->hasPlugin($plugin);
	}
	
	/**
	 * 获取所有的插件
	 * @return array
	 */
	public function getPlugins()
	{
		return $this->_plugin_manage->getPlugins();
	}

	/**
	 * 获取当前转发次数
	 * @return int
	 */
	public function getDispathCount()
	{
		return $this->_dispath_count;
	}
	
	/**
	 * 转发控制器
	 * @param XF_Controller_Request_Abstract $request
	 * @param bool $runRouter 重新运行路由解析 默认为true
	 * @throws XF_Controller_Exception
	 */
	public function dispatch(XF_Controller_Request_Abstract $request = null, $runRouter = true)
	{
		$this->_dispath_count++;
		
		if ($request != null)
			$this->_request = $request;

		if ($this->_dispath_count == 1)
		{
			$this->_plugin_manage->routeStartup($this->_request);
			$this->_router->run();
			$this->_plugin_manage->routeShutdown($this->_request);
		}
		//死循环
		elseif ($this->_dispath_count >= 5)
		{
			XF_Functions::writeErrLog('URI:'.$this->_request->getRequestUrl().' - loop forever dispatch controller');
			$e = new XF_Exception('loop forever dispatch controller');
			echo $e;
			return;
		}
		else
		{
			if ($runRouter == true)
				$this->_router->run();
		} 

		//没有找到正确的模块
		if ($this->_request->getModule() == 'unknown')
		{
			$this->_plugin_manage->exception404($this->_request);
			return;
		}
		
		//加载控制器
		$this->getModuleDir();
		$controllerName = NULL;
		
		if (ucfirst($this->_request->getModule()) !='Default')
		{
			$controllerName = $this->_request->getModule().'_';
		}

		$this->_controller_dir = $this->_module_dir.'/controllers';
		$controllerFile = $this->_controller_dir.'/'.ucfirst($this->_request->getController()).'Controller.php';
		
		if (is_file($controllerFile))
		{
			require_once $controllerFile;
			$controllerName.= ucfirst($this->_request->getController()).'Controller';
			if (class_exists($controllerName, FALSE))
			{
				try 
				{
					$this->_plugin_manage->preDispatch($this->_request);
					$this->_controller_instance = new $controllerName();
					$this->_plugin_manage->postDispatch($this->_request);
				}
				catch (XF_Exception $e)
				{
					$this->_plugin_manage->exception($this->_request, $e);
				}
			}
		}
		if ($this->_controller_instance == null)
		{
			$this->_plugin_manage->exception404($this->_request);
		}
		elseif ($this->_controller_instance instanceof XF_Controller_Interface)
		{
			try 
			{
				$this->_controller_instance->doAction();
			}
            catch (XF_Exception $e)
			{
				try
				{
					$this->_plugin_manage->exception($this->_request,new XF_Exception($e->getMessage(), $e->getCode()));
				}
				catch (XF_Exception $e){
					echo $e;
				}
				
			}
		}
		else 
		{
			$this->_plugin_manage->exception404($this->_request);
		}
	}
}