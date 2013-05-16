<?php
if(!defined('APPLICATION_PATH'))die('Cannot access the file !');
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
	 * 渲染模板
	 * @access public
	 * @param string $template_file Action模板文件
	 * @param int $cache_time 缓存时间，单位分钟
	 * @param string $cache_sign 缓存标识
	 * @param XF_View_Layout_Abstract $layout 
	 * @return string
	 */
	public function render($template_file = null, $cache_time = 0, XF_View_Layout_Abstract $layout = null)
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
			throw new XF_View_Exception('Action模板没有找到!', 500);
		
		$content = $this->obGetContents($template_file);
		
		//是否需要缓存?
		if ($cache_time > 0) 
		{
				
			if ($layout == null)
				$time_tag = '<!--##'.time().':'.$cache_time.'##-->';
			else
				$time_tag = '<!--##'.time().':'.$cache_time.'|'.get_class($layout).'|'.$layout->getCacheTime().':'.(int)$layout->getCacheType().'##-->';
				
			$_content = $content.$time_tag.$this->_makeCacheHeadTitleAndHeadMeta();
	 
			//缓存文件名称
			$cache_key = md5($appName.$controllerName.$actionName.serialize(XF_Controller_Request_Http::getInstance()->getCustomParams(false)));
			//写入缓存文件
			XF_File::mkdirs(TEMP_PATH.'/Cache');
			$secache = new secache();
			$secache->workat(TEMP_PATH.'/Cache/ActionViewCache');
			$secache->store($cache_key, $_content);
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
		$html =  "<!--###TITLE:$title###-->\n";
		$html .=  "<!--###METAS:".serialize($metas)."###-->\n";
		$html .=  "<!--###SCRIPTS:".serialize($scripts)."###-->\n";
		$html .=  "<!--###STYLESHEETS:".serialize($stylesheets)."###-->";
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
		return $content;
	}
	

	
	/**
	 * 获取模板起始位置目录
	 * @access public
	 * @return string
	 */
	public function getTemplateStartLocation()
	{
		if ($this->_template_folder == '')
			$this->_template_folder = XF_Controller_Front::getInstance()->getModuleDir().'/views/'.XF_Config::getInstance()->getViewType();
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
	 * @return XF_View_Helper_Header_Ｍeta
	 */
	public function headMeta($var = null)
	{
		return XF_View_Helper::getInstance()->headeMeta($var);
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