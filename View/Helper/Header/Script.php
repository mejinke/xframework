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
 * @desc 视图Script类
 * @author jingke
 */
class XF_View_Helper_Header_Script
{
	/**
	 * 实例
	 * @var XF_View_Helper_Header_Script
	 */
	private static $_instance = null;
	
	/**
	 * script列表
	 * @var array
	 */
	private $_scripts = array();
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper_Header_Script
	 */
	public static function getInstance($script = null)
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		if (XF_Functions::isEmpty($script) === false)
			self::$_instance->appendFile($script);
			
		return self::$_instance;
	}
	
	/**
	 * 在Scripts尾部追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Script
	 */
	public function appendFile($str)
	{
		$this->_scripts[] = XF_String::text($str);
		return $this;
	}
	
	/**
	 * 在Scripts开头追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Script
	 */
	public function prependFile($str)
	{
		array_unshift($this->_scripts, XF_String::text($str));
		return $this;
	}

	/**
	 * 获取script列表
	 * @access public
	 * @return array
	 */
	public function getScripts()
	{
		return $this->_scripts;
	}
	
	/**
	 * 清除所有的Script信息
	 * @access public
	 * @return XF_View_Helper_Header_Script
	 */
	public function clearAll()
	{
		$this->_scripts = null;
		return $this;
	}
	
	/**
	 * 获取Scripts
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		$str = '';
		if (is_array($this->_scripts))
		{
			foreach ($this->_scripts as $val)
			{
				$str .= '<script type="text/javascript" src="'.$val.'">'."</script>\n";
			}
		}
		return $str;
	}
}