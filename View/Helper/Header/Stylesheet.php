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
 * @desc 视图Stylesheet类
 * @author jingke
 */
class XF_View_Helper_Header_Stylesheet
{
	/**
	 * 类实例
	 * @var XF_View_Helper_Header_Stylesheet
	 */
	private static $_instance = null;
	
	/**
	 * Stylesheet 列表
	 * @var array
	 */
	private $_stylesheets = null;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper_Header_Stylesheet
	 */
	public static function getInstance($stylesheet = null)
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		if (XF_Functions::isEmpty($stylesheet) === false)
			self::$_instance->append($stylesheet);
			
		return self::$_instance;
	}
	
	/**
	 * 在Stylesheets尾部追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Stylesheet
	 */
	public function append($str)
	{
		$this->_stylesheets[] = XF_String::text($str);
		return $this;
	}
	
	/**
	 * 在Stylesheets开头追加内容
	 * @access public
	 * @param string $str
	 * @return XF_View_Helper_Header_Stylesheet
	 */
	public function prepend($str)
	{
		array_unshift($this->_stylesheets, XF_String::text($str));
		return $this;
	}
	
	/**
	 * 获取Stylesheet列表
	 * @access public
	 * @return array
	 */
	public function getStylesheets()
	{
		return $this->_stylesheets;
	}
	
	/**
	 * 获取stylesheets
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		$str = '';
		if(is_array($this->_stylesheets))
		{
			foreach ($this->_stylesheets as $val)
			{
				$str .= '<link rel="stylesheet" type="text/css" href="'.$val.'" media="screen" />'."\n";
			}
		}
		
		return $str;
	}
}