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
 * @desc 路由规则重写抽象类
 * @author jingke
 */
abstract class XF_Controller_Router_Rewrite_Abstract implements XF_Controller_Router_Rewrite_Interface
{
	
	/**
	 * 是否匹配成功
	 * @var bool
	 */
	protected $_match_status = FALSE;
	
	/**
	 * 正则表达式
	 * @var mixed
	 */
	protected $_regex;
	
	/**
	 * 对应的module controller action 数组
	 * @var array
	 */
	protected $_ma_array;
	
	/**
	 * 对应的参数数组
	 * @var array
	 */
	protected $_params_array;
	
	/**
	 * 重定向配置
	 * @var array
	 */
	protected $_redirect_array;
	
	/** 
	 * 当前设置的Action是否禁用默认路由
	 * @var bool
	 */
	protected $_disable_default_route;
			
	/**
	 * 初始化
	 * @param string|array $regex　正则表达式,支持数组方式
	 * @param array $mArray　对应的module controller action 数组
	 * @param mixed $paramsArray　对应的参数数组 默认为null  例:array('0:1' => 'page', '1:1'=>'key')
	 * @param array $redirectArray 重定向匹配到的URL
	 * @param bool $disableTheDefaultRoute 禁用当前重写的Action的默认路由(该Action将无法能通过默认的路由方式访问)，默认为TRUE
     * @throws XF_Controller_Router_Rewrite_Exception
	 */
	abstract public function __construct($regex, Array $mArray, Array $paramsArray = NULL, $redirectArray = NULL, $disableTheDefaultRoute = TRUE);
	
	/**
	 * 是否匹配规则
	 * @return bool
	 */
	public function isMatch()
	{
		return $this->_match_status;
	}
	
	/**
	 * 是否禁用该Action的默认路由
	 * @return bool
	 */
	public function isDisableDefaultRoute()
	{
		return $this->_disable_default_route === TRUE ? TRUE : FALSE;
	}
	
	/** 
	 * 获取重写的模块、控制器、动作名
	 */
	public function getMca()
	{
		return $this->_ma_array;
	}
	
	/**
	 * 返回所有的规则表达式
	 * @return array
	 */
	public function toArray()
	{
		if (is_array($this->_regex))
			return $this->_regex;
		return array($this->_regex);
	}
	
	public function __toString()
	{
		if (!is_array($this->_regex))
			return $this->_regex;
		else 
			return implode(',', $this->_regex);
	}
}