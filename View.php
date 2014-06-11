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
 * @desc View 视图类
 * @author jingke
 */
class XF_View
{
	/**
	 * 当前实例
	 * @var XF_View
	 */
	private static $_instance;
	
	/**
	 * 模板目录开始目录
	 * @access private
	 * @var string
	 */
	private $_template_folder = '';
	
	/**
	 * 视图资料
	 * @var array
	 */
	private $_view_data = null;
	
	/**
	 * 使用的布局文件名称
	 * @access private
	 * @var string
	 */
	private  $_layout = '';
	
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
	 * 页面资源(js、css)起始路径
	 * @var string
	 */
	private $_resource_path;
	
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前实例
	 * @return XF_View
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	
	public function __get($name)
	{
		if (isset($this->_view_data[$name]))
			return $this->_view_data[$name];
		return NULL;
	}
	
	public function __set($name, $value)
	{
		if (strpos($name, '[') >0 && strpos($name, ']') > 0)
		{
			$name = str_replace(']', '', str_replace('[', '', $name));
			$this->_view_data[$name][] = $value;
		}
		else
			$this->_view_data[$name] = $value;
		return $this;
	}
	
	/**
	 * 返回当前视图对象
	 * @return XF_View
	 */
	public function getView()
	{
		return $this;
	}
	
	/**
	 * 填充视图资料
	 * @access public
	 * @param string $key 名称
	 * @param mixed $value
	 * @return XF_View
	 */
	public function assign($key, $value)
	{
		$this->_view_data[$key] = $value;
		return $this;
	}

	/**
	 * 设置Action的缓存驱动对象
	 * @access public
	 * @param XF_Cache_Interface $cache
	 * @return XF_View
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
	 * @return XF_View
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
	 * 设置页面资源起始路径
	 * @param string $path
	 * @return XF_View
	 */
	public function setResourcePath($path)
	{
		$this->_resource_path = $path;
		return $this;
	}
	
	/**
	 * 获取页面资源起始路径
	 * @return string
	 */
	public function getResourcePath()
	{
		return empty($this->_resource_path) ? '' : $this->_resource_path;
	}
		
	/**
	 * 渲染模板
	 * @access public
	 * @param string $template_file Action模板文件
	 * @param string $cache_sign 缓存标识
	 * @param XF_View_Layout_Abstract $layout 
	 * @return string
	 */
	public function render($template_file = null, $cache_sign = '', XF_View_Layout_Abstract $layout = null)
	{
			
		$this->getTemplateStartLocation();
		$request = XF_Controller_Request_Http::getInstance();
		$appName = $request->getModule();
		$controllerName =$request->getController();
		$actionName = $request->getAction();

		//如果没有设置模板，则自动获取当前Action名称相关的模板
		if ($template_file == null)
			$template_file = $this->_template_folder.'/'.$controllerName.'/'.$actionName.'.php';
			
		if (!is_file($template_file))
			throw new XF_View_Exception('Action template not found');
		
		$content = $this->obGetContents($template_file);
		
		XF_Controller_Plugin_Manage::getInstance()->postRender($content);
		
		//是否需要缓存?
		if ($this->_cache_time > 0) 
		{
			$layout_tag = '';
			if ($layout != null)
				$layout_tag = '<!--Layout:'.get_class($layout).','.$layout->getCacheTime().','.(int)$layout->getCacheType().'-->';
				
			$_content = $content.$layout_tag.$this->_makeCacheHeadTitleAndHeadMeta();
		
			//写入缓存
			if ($this->_cache_instance instanceof XF_Cache_SECache)
			{
				XF_File::mkdirs(TEMP_PATH.'/Cache');
				$this->_cache_instance->setCacheSaveFile(TEMP_PATH.'/Cache/ActionViewCache');
			}
			
			$this->_cache_instance->setCacheTime($this->_cache_time);
			$this->_cache_instance->add($cache_sign, $_content);
		}
		
		//是否启用布局
		if ($layout != null)
		{ 
			$layout->assign('$layoutContent', $content);
			$content = $layout->render();
		}
		return $content;
	}
	
	/**
	 * 生成缓存时需要保存的当前view的标题，meta等信息
	 * @access private
	 * @return string
	 */
	private function _makeCacheHeadTitleAndHeadMeta()
	{
		$title = $this->headTitle()->getTitle();
		$metas = $this->headMeta()->getMetas();
		$scripts = $this->headScript()->getScripts();
		$stylesheets = $this->headStylesheet()->getStylesheets();
		$links = $this->headLink()->getLinks();
		$html =  '<!--Title:'.$title.'-->';
		$html .=  '<!--Metas:'.serialize($metas).'-->';
		$html .=  '<!--Scripts:'.serialize($scripts).'-->';
		$html .=  '<!--Stylesheets:'.serialize($stylesheets).'-->';
		$html .=  '<!--Links:'.serialize($links).'-->';
		return $html;
	}

	/**
	 * 添加模板文件获取内存资料
	 * @access public
	 * @param array $data
	 * @param string $file
	 * @return string
	 */
	public function obGetContents($file)
	{
		if (!is_file($file))
			return '';
		$content = '';
		ob_start();
		require $file;
		$content = ob_get_contents();
		ob_end_clean();
		
		//清除BOM
		$charset[1] = substr($content, 0, 1);
		$charset[2] = substr($content, 1, 1);
		$charset[3] = substr($content, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) 
			$content = substr($content, 3);
		return $content;
	}
	

	
	/**
	 * 获取模板起始位置目录
	 * @access public
	 * @return string
	 */
	public function getTemplateStartLocation()
	{
		$this->_template_folder = XF_Controller_Front::getInstance()->getModuleDir().'/views/'.XF_Config::getInstance()->getViewStyle();
		return $this->_template_folder;
	}

	
	/**
	 * 标题Header
	 * @access public
	 * @param string $var
	 * @return XF_View_Helper_Header_Title
	 */
	public function headTitle($var = '')
	{
		return XF_View_Helper::getInstance()->headeTitle($var);
	}
	
	/**
	 * Meta
	 * @param string $var
	 * @return XF_View_Helper_Header_Meta
	 */
	public function headMeta($var = null)
	{
		return XF_View_Helper::getInstance()->headeMeta($var);
	}
	
	/**
	 * Link
	 * @param string $var
	 * @return XF_View_Helper_Header_Link
	 */
	public function headLink($var = null)
	{
		return XF_View_Helper::getInstance()->headeLink($var);
	}
	
	/**
	 * Script
	 * @param string $var
	 * @return XF_View_Helper_Header_Script
	 */
	public function headScript($var = null)
	{
		return XF_View_Helper::getInstance()->headeScript($var);
	}
	
	/**
	 * Stylesheet
	 * @param string $var
	 * @return XF_View_Helper_Header_Stylesheet
	 */
	public function headStylesheet($var = null)
	{
		return XF_View_Helper::getInstance()->headeStylesheet($var);
	}
	
}