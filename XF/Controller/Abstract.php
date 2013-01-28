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
 * @desc 控制器抽象基类
 * @author jingke
 */
abstract class XF_Controller_Abstract
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
	
	/**
	 * 动作缓存时间[分钟] 0为不缓存
	 * @access priviate
	 * @var int
	 */
	private $_cache_time = 0;
	
	/**
	 * Action缓存标识(生成缓存的key)
	 * @var string
	 */
	private $_cache_sign = '';
		
	/**
	 * 动作Action所使用的模板
	 * @access priviate
	 * @var string
	 */
	private $_action_template = null;
	
	/**
	 * 是否渲染视图模板
	 * @access priviate
	 * @var bool
	 */
	private $_is_render_view = TRUE;
	
	/**
	 * 布局对象
	 * @access priviate
	 * @var XF_View_Layout_Abstract
	 */
	private $_layout = null;
	
	/**
	 * 视图对象
	 * @access protected
	 * @var XF_View
	 */
	protected $_view = null;
	
	protected function __construct(XF_Controller_Abstract $controller)
	{
		$this->_controller = $controller;
		$this->_request = XF_Controller_Request_Http::getInstance();
		$this->_view = XF_View::getInstance();
	}
	
	
	/**
	 * 执行控制器动作
	 * @return string
	 */
	public function doAction()
	{
		$plugins = XF_Controller_Plugin_Manage::getInstance();
		$this->_checkActionRequestParams();
		//当前Action是否存在文件缓存
		if ($content = $this->_checkCacheContent())
		{
			echo $content;
			return true;
		}
		$this->_checkControllerInstance();
		$actionName = $this->_request->getAction();			
		$valiMethod = 'validate'.ucfirst($actionName);
		$method = $actionName;
		$methods = get_class_methods($this->_controller);
		
		//控制器构造函数里可以改变当前请的Action
		if (strpos($method, '@') !== 0)
		{
			$valiMethod .= 'Action';
			$method .= 'Action';
		}
		else 
		{
			$valiMethod = str_replace('@', '', $valiMethod);
			$method = str_replace('@', '', $method);
			XF_Controller_Request_Http::getInstance()->setAction($method);
		}
		if ($this->hasAction($valiMethod))
		{ 
			if(call_user_func(array($this->_controller,$valiMethod)) === true)
			{
				if ($this->hasAction($method))
				{
					call_user_func(array($this->_controller,$method));
					$this->_display();
				}
				else
					$plugins->exception404($this->_request);
			}
			else
				$plugins->exception404($this->_request);
		}
		else 
		{	
			if ($this->hasAction($method))
			{
				call_user_func(array($this->_controller,$method));
				$this->_display();
			}
			else
				$plugins->exception404($this->_request);
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
	 * 设置Action缓存时间
	 * @param int $minutes
	 * @return XF_Controller_Abstract
	 */
	public function setCacheTime($minutes = 0)
	{
		if (is_int(intval($minutes)))
		{
			$this->_cache_time = intval($minutes);
		}
		return $this;
	}
	
	/**
	 * 设置Action的缓存标识(生成缓存的唯一key)
	 * @param string $var
	 * @return XF_Controller_Abstract
	 */
	public function setCacheSign($var)
	{
		if (is_string($var))
			$this->_cache_sign = $var;
		return $this;
	}
	
	/**
	 * 设置模板文件
	 * @param string $tpl
	 * @return XF_Controller_Abstract
	 */
	public function setTemplate($tpl)
	{
		if (strpos($tpl, '.php') > 0)
			$file = APPLICATION_PATH.'/'.$tpl;
		else
			$file = $this->_view->getTemplateStartLocation().'/'.$this->_request->getController().'/'.$tpl.'.php';
			
		if (is_file($file))
		{
			$this->_action_template = $file;
			return $this;
		}
		else
			throw new XF_Controller_Exception('Action模板文件不存在!');
	}
	
	/**
	 * 设置布局模板文件
	 * @param XF_View_Layout_Abstract $layout 布局对象
	 * @return XF_Controller_Abstract
	 */
	public function setLayout(XF_View_Layout_Abstract $layout = null)
	{
		$this->_layout = $layout;
		return $this;
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
	
	
	/**
	 * 通知控制器，不渲染模板
	 * @access protected
	 * @return XF_Controller_Abstract
	 */
	protected function _notRender()
	{
		$this->_is_render_view = FALSE;
		return $this;
	}
	
	/**
	 * 渲染模板 并输出
	 * @param bool $echo 是否直接输出？默认为true
	 * @return mixed
	 */
	private function _display($echo = true)
	{
		if ($this->_is_render_view === FALSE)
			return true;
		$content = $this->_view->render($this->_action_template, $this->_cache_time, $this->_layout);
		if ($echo === false) 
			return $content;
		else 
			echo $content;
	}
	
	/**
	 * 获取渲染后的模板内容
	 * @return string
	 */
	public function getTemplateContent()
	{
		$this->_is_render_view = TRUE;
		return $this->_display(false);
	}
	
	/**
	 * 检测是否存在缓存文件
	 * @access private
	 * @return void
	 */
	private function _checkCacheContent()
	{
		require_once XF_PATH.'/Custom/secache/secache.php';
		
		$cache_file = TEMP_PATH.'/Cache/ActionViewCache.php';
		if (!is_file($cache_file)) return null;

		//缓存文件名称
		$key = md5($this->_request->getModule().$this->_request->getController().$this->_request->getAction().serialize($this->_request->getCustomParams(false)));

		$secache = new secache();
		$secache->workat(TEMP_PATH.'/Cache/ActionViewCache');
		if($secache->fetch($key, $content))
		{
			preg_match('/<\!--##(.*?)##-->/',$content,$tep_array);
			if(isset($tep_array[1]))
			{
				$tmp = explode('|',$tep_array[1]);
				$_timeTag = explode(':', $tmp[0]);
				if (time()>$_timeTag[0]+$_timeTag[1]*60)
					return null;
				else //没有过期
				{
					$content = str_replace($tep_array[0],'',$content);
					//是否启用布局
					if (isset($tmp[1]))
					{
						$layoutName = $tmp[1];
						$layout = new $layoutName();
						$tep = explode(':', $tmp[2]);
						$layout->setCacheTime($tep[0]);
						$layout->setCacheType($tep[1]);
						//执行布局对象，并渲染布局模板
						if ($layout instanceof XF_View_Layout_Abstract)
						{
							//获取缓存的标题等信息
							$this->_checkCacheTitleMetaSctiptStylesheets($content);
							
							$layout->assign('$layoutContent',$content);
							$content = $layout->render();
						}
					}
					return $content;
				}
			}
		}
 
		return null;
	}
	
	
	
	/**
	 * 检测缓存的标题等资料
	 * @param string $content
	 */
	private function _checkCacheTitleMetaSctiptStylesheets(&$content)
	{
		//title
		preg_match('/<\!--###TITLE:(.*?)###-->/',$content,$matchs);
		if (isset($matchs[1]))
		{
			$this->_view->headTitle($matchs[1]);
			$content = str_replace($matchs[0]."\n", '', $content);
		}
		//metas
		preg_match('/<\!--###METAS:(.*?)###-->/',$content,$matchs);
		if (isset($matchs[1]))
		{
			 
			$tmp = unserialize($matchs[1]);
			if (is_array($tmp))
			{
				foreach ($tmp as $val)
				{
					$this->_view->headMeta($val);
				}
			}
			$content = str_replace($matchs[0]."\n", '', $content);
		}
		
		//scripts
		preg_match('/<\!--###SCRIPTS:(.*?)###-->/',$content,$matchs);
		if (isset($matchs[1]))
		{
			 
			$tmp = unserialize($matchs[1]);
			if (is_array($tmp))
			{
				foreach ($tmp as $val)
				{
					$this->_view->headScript($val);
				}
			}
			$content = str_replace($matchs[0]."\n", '', $content);
		}
		
		//stylesheets
		preg_match('/<\!--###STYLESHEETS:(.*?)###-->/',$content,$matchs);
		if (isset($matchs[1]))
		{
			 
			$tmp = unserialize($matchs[1]);
			if (is_array($tmp))
			{
				foreach ($tmp as $val)
				{
					$this->_view->headStylesheet($val);
				}
			}
			$content = str_replace($matchs[0], '', $content);
		}
	}
	
	
	/**
	 * 过滤自定义Request参数规则  appName/config/request.inc.php
	 * @return void
	 */
	private function _checkActionRequestParams()
	{
		
		//Request参数配置文件是否存在
		$incFile = XF_Controller_Front::getInstance()->getModuleDir().'/configs/request.inc.php';

		if (!is_file($incFile))
			return true;
		$config = require $incFile;
 
		//是否存在当前Action Request参数配置
		if (isset($config[ucfirst($this->_request->getController()).'Controller'][$this->_request->getAction().'Action']))
		{
			$params = $config[ucfirst($this->_request->getController()).'Controller'][$this->_request->getAction().'Action'];
			$getParams_key = array_keys($this->_request->getCustomParams(false));
			$params_key = array_keys($params);
			$count = count($getParams_key);
			for ($i=0; $i<$count; $i++)
			{
				$key = $getParams_key[$i];
				//存在正确的参数，检测是否为指定的类型
				if (array_search($key, $params_key)!== false )
				{
					$error = false;
					$type = is_array($params[$key]) && isset($params[$key]['type']) ? $params[$key]['type'] : $params[$key];
					//检测参数类别是否为允许的
					if ($type === 'int' && !is_numeric($this->getParam($key)))
						$error = true;
					elseif ($type === 'string' && is_numeric($this->getParam($key)))
						$error = true;
					
					//符合参数类型条件时，校正参数值
					if ($error === false)
					{
						//是否在指定值范围内
						$value = is_array($params[$key]) && isset($params[$key]['value']) ? $params[$key]['value'] : null;
						if ($value !== null)
						{
							if(is_array($value) && array_search($this->getParam($key), $value) === false )
							{ 
								//校正参数值
								if (is_array($params[$key]) && isset($params[$key]['default']) && $params[$key]['default'] !== null)
									$this->_request->setParam($key, $params[$key]['default']);
								else
									$this->_request->clearParam($key);
							}
						}
					}
					else //参数类型不符合时，判断是否要设置默认值
					{
						//校正参数值
						if (is_array($params[$key]) && isset($params[$key]['error_default']) && $params[$key]['error_default'] !== null)
							$this->_request->setParam($key, $params[$key]['error_default']);
						else
							$this->_request->clearParam($key);
					}
				}
				else 
					$this->_request->clearParam($key);
			} 
		}
	}	
}