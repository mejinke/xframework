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
abstract class XF_Controller_Abstract implements XF_Controller_Interface
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
	 * 缓存驱动
	 * @access private
	 * @var XF_Cache_Interface
	 */
	private $_cache_instance;
	
	/**
	 * Action缓存时间[分钟] 0为不缓存
	 * @access priviate
	 * @var int
	 */
	private $_cache_time = 0;
		
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
	 * 获取额外的操作数据
	 * @access public
	 * @param string $name 数据名称
	 * @return mixed
	 */
	public function __get($name)
	{
		return XF_Controller_Front::getInstance()->getHandleData($name);
	}
	
	/**
	 * 获取当前Action对应的缓存标识【需要缓存的Action，控制器中都应该重写该方法】
	 * <br/><b>●强烈建议不要在该方法中操作数据库以及过于复杂的业务逻辑</b>
	 * @access protected
	 * @param string $action_name 当前请求的Action名称
	 * @return string 缓存标识，默认为空
	 */
	protected function __cacheSign($action_name)
	{
		return '';
	}
	
	/**
	 * 获取当前Action最终对应的缓存标识
	 * @access private
	 * @param string
	 */
	private function getCacheSign()
	{
		$sign = $this->__cacheSign($this->_request->getAction());
		if($sign == '')
		{
			if ($this->_cache_time > 0)
			{
				return md5($this->_request->getModule().$this->_request->getController().$this->_request->getAction().serialize($this->_request->getCustomParams(false)));
			}
			return '';
		}
		return md5($sign);
	}
	
	/**
	 * 执行控制器动作
	 * @access public
	 * @return void
	 */
	public function doAction()
	{
		$plugins = XF_Controller_Plugin_Manage::getInstance();
		//当前Action是否存在文件缓存
		if ($content = $this->_checkCacheContent())
		{
			echo $content;
			return true;
		}
		$this->_checkControllerInstance();
		$actionName = $this->_request->getAction();			
		$valiMethod = 'validate'.ucfirst($actionName);
		$method = $actionName.'Action';
		$methods = get_class_methods($this->_controller);
		
		//是否存在Action验证方法
		if ($this->hasAction($valiMethod))
		{ 
			if(call_user_func(array($this->_controller, $valiMethod)) !== true)
			{
				$plugins->exception404($this->_request);
				return;
			}
		}
		
		//是否存在要执行的Action
		if ($this->hasAction($method))
		{
			$plugins->preAction($this->_request);
			call_user_func(array($this->_controller,$method));
			$plugins->postAction($this->_request);
			$this->_render();
			$plugins->postOutput();
			return;
		}
	
		$plugins->exception404($this->_request);
	}
	
	/**
	 * 是否存在Action
	 * @access public
	 * @param string $action_name 动作名称
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
	 * @access protected
	 * @throws XF_Controller_Exception
	 * @return void
	 */
	protected function _checkControllerInstance()
	{
		if ($this->_controller == null)
		{
			throw new XF_Controller_Exception('Controller instance invalid');
		}
	}
	
	/**
	 * 设置Action的缓存驱动对象
	 * @access public
	 * @param XF_Cache_Interface $cache
	 * @return XF_Controller_Abstract
	 */
	public function setCache(XF_Cache_Interface $cache)
	{
		$this->_cache_instance = $cache;
		return $this;	
	}
	
	/**
	 * 设置Action缓存时间
	 * @access public
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
	 * 设置模板文件
	 * @access public
	 * @param string $tpl
	 * @return XF_Controller_Abstract
	 */
	public function setTemplate($tpl)
	{
		if (strpos($tpl, '.php') > 0)
		{
			$file = APPLICATION_PATH.'/'.$tpl;
		}
		else
		{
			$file = $this->_view->getTemplateStartLocation().'/'.$this->_request->getController().'/'.$tpl.'.php';
		}
			
		if (is_file($file))
		{
			$this->_action_template = $file;
			return $this;
		}
		
		throw new XF_Controller_Exception('Action template not found');
	}
	
	/**
	 * 设置布局模板文件
	 * @access public
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
	 * @access public
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key , $default = NULL)
	{
		return $this->_request->getParam($key, $default);
	}
	
	/**
	 * 获取参数，预期该参数的值为数字
	 * @access public
	 * @param string $key 参数名称
	 * @param number $default 如果该参数不存在，需要返回的值，默认为 0
	 */
	public function getParamNumber($key, $default = 0)
	{
		$val = $this->_request->getParam($key, $default);
		if ($default == $val) 
		{
			return $val;
		}
		return is_numeric($val) ? floatval($val) : $default;
	}
	
	/**
	 * 获取所有参数列表
	 * @access public
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
	 * @access private
	 * @param bool $echo 是否直接输出？默认为true
	 * @return mixed
	 */
	private function _render($echo = true)
	{
		if ($this->_is_render_view === FALSE)
		{
			return true;
		}
			
		if ($this->_cache_instance == NULL)
		{
			$this->_cache_instance = XF_Cache_SECache::getInstance();
		}
	
		$html = $this->_view
				->setCache($this->_cache_instance)
				->setCacheTime($this->_cache_time)
				->render($this->_action_template, $this->getCacheSign(), $this->_layout);

		if ($echo === false) 
		{
			return $html;
		}
		
		echo $html;	
	}
	
	/**
	 * 获取渲染后的模板内容
	 * @return string
	 */
	public function getTemplateContent()
	{
		$this->_is_render_view = TRUE;
		return $this->_render(false);
	}
	
	/**
	 * 检测是否存在缓存文件
	 * @access private
	 * @return void
	 */
	private function _checkCacheContent()
	{
		$key = $this->getCacheSign();
		if ($key == '')
		{
			return;
		} 

		//如果没有设置缓存类型，则默认为secache
		if ($this->_cache_instance == null)
		{
			$this->_cache_instance = XF_Cache_SECache::getInstance();
		}

		if ($this->_cache_instance instanceof XF_Cache_SECache)
		{
			$cache_file = TEMP_PATH.'/Cache/ActionViewCache';
			if (!is_file($cache_file.'.php')) return null;
			$this->_cache_instance->setCacheSaveFile($cache_file);
		}

		$content = $this->_cache_instance->read($key);
		if ($content == XF_CACHE_EMPTY)
		{
			return null;
		}
		
		//检测布局
		preg_match('/<!--Layout:(.*?)-->/', $content, $matches);

		if (isset($matches[0]))
		{
			$tmp = explode(',', $matches[1]);
			$layoutName = $tmp[0];
			$layout = new $layoutName();
			$layout->setCacheTime($tmp[1]);
			$layout->setCacheType($tmp[2]);
			
			//清除布局标记
			$content = str_replace($matches[0], '', $content);
			
			//执行布局对象，并渲染布局模板
			if ($layout instanceof XF_View_Layout_Abstract)
			{
				//获取缓存的标题等信息
				$this->_checkCacheHtmlTag($content);
				$layout->assign('$layoutContent', $content);
				$content = $layout->render();
			}
		}
		
		return $content;
	}
	
	
	/**
	 * 检测缓存的标题等资料
	 * @access private
	 * @param string $content
	 * @return void
	 */
	private function _checkCacheHtmlTag(&$content)
	{
		//title
		preg_match('/<\!--Title:(.*?)-->/', $content, $matchs);
		if (isset($matchs[1]))
		{
			$this->_view->headTitle($matchs[1]);
			$content = str_replace($matchs[0], '', $content);
		}
		
		//metas
		preg_match('/<\!--Metas:(.*?)-->/', $content, $matchs);
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
			$content = str_replace($matchs[0], '', $content);
		}
		
		//scripts
		preg_match('/<\!--Scripts:(.*?)-->/', $content, $matchs);
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
			$content = str_replace($matchs[0], '', $content);
		}
		
		//stylesheets
		preg_match('/<\!--Stylesheets:(.*?)-->/', $content, $matchs);
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
		
		//links
		preg_match('/<\!--Links:(.*?)-->/', $content, $matchs);
		if (isset($matchs[1]))
		{
			$tmp = unserialize($matchs[1]);
			if (is_array($tmp))
			{
				foreach ($tmp as $val)
				{
					$this->_view->headLink($val);
				}
			}
			$content = str_replace($matchs[0], '', $content);
		}
	}
}