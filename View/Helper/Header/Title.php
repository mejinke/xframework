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
 * @desc 视图Title类
 * @author jingke
 */
class XF_View_Helper_Header_Title
{
	/**
	 * 实例
	 * @var XF_View_Helper_Header_Title
	 */
	private static $_instance = null;
	
	/**
	 * HTML标题
	 * @var string
	 */
	private $_title = '';
	
	/**
	 * 标题分隔符
	 * @var string
	 */
	private $_linkSymbol = '';
	
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper_Header_Title
	 */
	public static function getInstance($title = '')
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		if (XF_Functions::isEmpty($title) === false)
			self::$_instance->appendTitle($title);
		return self::$_instance;
	}
	
	/**
	 * 设置标题连接符号
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Title
	 */
	public function setLinkSymbol($str)
	{
		$this->_linkSymbol = XF_String::text($str, false);
		return $this;
	}
	
	
	/**
	 * 在标题尾部追加内容
	 * @access public
	 * @param string $str
	 * @return View_Header_Title
	 */
	public function appendTitle($str)
	{
		$this->_title .= $this->_linkSymbol.XF_String::text($str, false);
		return $this;
	}
	
	/**
	 * 在标题前方添加内容
	 * @access public
	 * @param string $str
	 * @return View_Header_Title
	 */
	public function prependTitle($str)
	{
		$this->_title = XF_String::text($str, false).$this->_linkSymbol.$this->_title;
		return $this;
	}
	
	/**
	 * 获取标题内容
	 * @access public
	 * @return string
	 */
	public function getTitle()
	{
		return $this->_title;
	}
	
	/**
	 * 清空标题
	 * @return XF_View_Helper_Header_Title
	 */
	public function clear()
	{
		$this->_title = '';
		return $this;
	}
	
	/**
	 * 获取标题内容
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		return '<title>'.str_replace('&amp;nbsp;', ' ', $this->_title)."</title>\n";
	}
}