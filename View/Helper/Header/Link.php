<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-10-25
 * -+-----------------------------------
 *
 * @desc 视图Link类
 * @author jingke
 */
class XF_View_Helper_Header_Link
{
	/**
	 * 实例
	 * @var XF_View_Helper_Header_Link
	 */
	private static $_instance = null;
	
	/**
	 * meta列表
	 * @var array
	 */
	private $_links = null;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper_Header_Link
	 */
	public static function getInstance($link = null)
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		if (XF_Functions::isEmpty($link) === false)
			self::$_instance->appendLink($link);
		return self::$_instance;
	}
	
	/**
	 * 在Link尾部追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Link
	 */
	public function appendLink($str)
	{
		$this->_links[] = XF_String::text($str);
		return $this;
	}
	
	/**
	 * 获取link列表
	 * @access public
	 * @return array
	 */
	public function getLinks()
	{
		return $this->_links;
	}
	
	/**
	 * 清除所有的link信息
	 * @return XF_View_Helper_Header_Link
	 */
	public function clearAll()
	{
		$this->_links = null;
		return $this;
	}
	
	/**
	 * 获取Link内容
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		$str = '';
		if (is_array($this->_links))
		{
			foreach ($this->_links as $val)
			{
				$str .= '<link '.$val." />\n";
			}
		}
		return $str;
	}
}