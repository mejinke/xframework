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
 * @desc 视图Meta类
 * @author jingke
 */
class XF_View_Helper_Header_Meta
{
	/**
	 * 实例
	 * @var XF_View_Helper_Header_Meta
	 */
	private static $_instance = null;
	
	/**
	 * meta列表
	 * @var array
	 */
	private $_metas = null;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper_Header_Meta
	 */
	public static function getInstance($meta = null)
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		if (XF_Functions::isEmpty($meta) === false)
			self::$_instance->appendMeta($meta);
		return self::$_instance;
	}
	
	/**
	 * 在Meta尾部追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Meta
	 */
	public function appendMeta($str)
	{
		$this->_metas[] = XF_String::text($str);
		return $this;
	}
	
	/**
	 * 获取meta列表
	 * @access public
	 * @return array
	 */
	public function getMetas()
	{
		return $this->_metas;
	}
	
	/**
	 * 清除所有的meta信息
	 * @return XF_View_Helper_Header_Meta
	 */
	public function clearAll()
	{
		$this->_metas = null;
		return $this;
	}
	
	/**
	 * 获取Meta内容
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		$str = '';
		if (is_array($this->_metas))
		{
			foreach ($this->_metas as $val)
			{
				$str .= '<meta '.$val." />\n";
			}
		}
		return $str;
	}
}