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
 * @desc 视图助手类
 * @author jingke
 */
class XF_View_Helper
{
	/**
	 * @var XF_View_Helper
	 */
	private static $_instance = null;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取实例
	 * @access public
	 * @return XF_View_Helper
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 获取Title
	 * @param string $var
	 * @return XF_View_Helper_Header_Title
	 */
	public function headeTitle($var)
	{
		return XF_View_Helper_Header_Title::getInstance($var);
	}
	
	/**
	 * 获取meat
	 * @param string $var
	 * @return XF_View_Helper_Header_Meta
	 */
	public function headeMeta($var)
	{
		return XF_View_Helper_Header_Meta::getInstance($var);
	}
	
	/**
	 * 获取Script
	 * @param string $var
	 * @return XF_View_Helper_Header_Script
	 */
	public function headeScript($var)
	{
		return XF_View_Helper_Header_Script::getInstance($var);
	}
	
	/**
	 * 获取Stylesheet
	 * @param string $var
	 * @return XF_View_Helper_Header_Stylesheet
	 */
	public function headeStylesheet($var)
	{
		return XF_View_Helper_Header_Stylesheet::getInstance($var);
	}
}