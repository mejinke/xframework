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
 * @desc 布局视图抽象类，所以布局对象必须继续此类，并实现init()方法
 * @author jingke
 */
abstract class XF_View_Layout_Abstract
{
	/**
	 * 布局文件路径
	 * @var string
	 */
	protected $_tpl = null;
	
	/**
	 * 布局缓存是否为私有，默认为FALSE<br/>
	 * TRUE：当Action启用布局时，生成与当前Action唯一的布局缓存。不与其它Action调此同一个布局冲突。<br/>
	 * FALSE：当Action启用布局时，如果其它Action之前已调用此布局，并且已存在缓存，则直接使用。
	 * @var bool
	 */
	protected $_cache_is_private = FALSE;
	
	/**
	 * 布局缓存时间
	 * @var int
	 */
	protected $_cache_time = 0;
	
	/**
	 * 填充布局页面的数据资料
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * 布局内部内容获取标识
	 * @var bool
	 */
	private $_flag = false;
	
	public function __get($name)
	{
		if (isset($this->_data[$name]))
		{
			if ($name == '$layoutContent')
			{
				if ($this->_flag == true)
					return $this->_data[$name];
				else 
					return "<?php echo \$this->{'\$layoutContent'};?>\n";
			}
			return $this->_data[$name];
		}
		return NULL;
	}
	
	
	/**
	 * 设置布局缓存时间。单位：分钟
	 * @access public
	 * @param int $minutes
	 * @return XF_View_Layout_Abstract
	 */
	public function setCacheTime($minutes)
	{
		if (is_numeric($minutes))	
			$this->_cache_time = $minutes;
		return $this;
	}
	
	/**
	 * 获取布局缓存时间
	 * @access public
	 * @return int
	 */
	public function getCacheTime()
	{
		return $this->_cache_time;
	}
	
	/**
	 * 布局缓存是否为私有，默认为FALSE<br/>
	 * @access public
	 * @param $status bool TRUE：当Action启用布局时，生成与当前Action唯一的布局缓存。不与其它Action调此同一个布局冲突。 FALSE：当Action启用布局时，如果其它Action之前已调用此布局，并且已存在缓存，则直接使用。
	 * @return void
	 */
	public function setCacheType($status = FALSE)
	{
		$this->_cache_is_private = $status;
		return $this;
	}
	
	/**
	 * 获取布局缓存类型
	 * @access public
	 * @return bool
	 */
	public function getCacheType()
	{
		return $this->_cache_is_private;
	}
	
	/**
	 * 获取布局模板文件
	 * @access public
	 * @return string
	 */
	public function getTpl()
	{
		return $this->_tpl;
	}
	
	/**
	 * 获取视图
	 * @return XF_View
	 */
	protected function getView()
	{
		return XF_View::getInstance();
	}
	
	/**
	 * Request
	 * @return XF_Controller_Request_Http
	 */
	protected function getRequest()
	{
		return XF_Controller_Request_Http::getInstance();
	}
	
	/**
	 * 填充数据
	 * @param string $key
	 * @param mixed $value
	 * @return Layout_Abstract
	 */
	public function assign($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}
	
	/**
	 * 初始化布局相关数据 ，方法
	 * @access protected
	 * @return void
	 */
	protected abstract function _init();
	
	/**
	 * 渲染布局模板文件
	 * @access public
	 * @return string
	 */
	public function render()
	{
		$content = isset($this->_data['$layoutContent']) ? $this->_data['$layoutContent'] : '';

		//布局模板起始路径
		$layoutTemplatePath = APPLICATION_PATH.'/layouts/scripts/';
		if (!is_file($layoutTemplatePath.$this->_tpl))
			return $content;
			
		//检测缓存
		if ($this->_cache_time > 0)
		{
			$request = XF_Controller_Request_Http::getInstance();
			$file = TEMP_PATH.'/Cache/LayoutScripts/';
			$pathinfo = pathinfo($this->_tpl);

			//检测布局缓存类型，定位正确的路径
			if ($this->_cache_is_private)
				$file .= 'Private/'.$request->getModule().'/'.
						$request->getController().'/'.
						$request->getAction().'/'.
						$pathinfo['basename'];
			else
				$file .= 'Public/'.$pathinfo['basename'];

			XF_File::mkdirs(pathinfo($file, PATHINFO_DIRNAME));
			
			if (is_file($file))
			{
				//布局缓存是否过期
				if (time() > (filemtime($file)+$this->_cache_time * 60) )
				{	
					//缓存失效时执行用户的初始化操作init()
					$this->_init();
					XF_File::del($file);
					$this->_flag = false;
					$content = $this->_getObContent($layoutTemplatePath.$this->_tpl);
					XF_File::write($file, $content);
				}
				$this->_flag = true;
				return $this->_getObContent($file);
			}
			else //保存布局缓存
			{
				$this->_init();
				$this->_flag = false;
				$content = $this->_getObContent($layoutTemplatePath.$this->_tpl);
				XF_File::write($file, $content);
				$this->_flag = true;
				return $this->_getObContent($file);
			}
		}
		else 
		{
			//不采用缓存时获取最终输出的HTML内容
			$this->_init();
			$this->_flag = true;
			return $this->_getObContent($layoutTemplatePath.$this->_tpl);
		}
		return $content;
	}
	
	
	
	/**
	 * 获取布局类部Action生成的内容
	 * @access private
	 * @param bool $flag 是否获取实际内容？
	 * @return string
	 */
	private function _wrapContent()
	{
		if ($this->_flag === true)
			return $this->_data['$layoutContent'];
		return "<?php echo \$this->{'\$layoutContent'};?>\n";
	}
	
	/**
	 * 在当前类中获取指定文件在缓冲区的内容
	 * @access private
	 * @param string $file
	 * @return string
	 */
	private function _getObContent($file)
	{
		if (!is_file($file))
			return '';
		$content = '';
		ob_start();
		require_once $file;
		$content = ob_get_contents();
		ob_end_clean();
		//清除BOM
		$charset[1] = substr($content, 0, 1);
		$charset[2] = substr($content, 1, 1);
		$charset[3] = substr($content, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) 
			$content = substr($content, 3);
			
		XF_Controller_Plugin_Manage::getInstance()->postRender($content);
		return $content;
	}
}