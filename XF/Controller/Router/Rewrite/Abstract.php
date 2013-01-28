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
	protected $_matchStatus = FALSE;
	
	/**
	 * 正则表达式
	 * @var mixed
	 */
	protected $_regex;
	
	/**
	 * 对应的module controller action 数组
	 * @var array
	 */
	protected $_mAarray;
	
	/**
	 * 对应的参数数组
	 * @var array
	 */
	protected $_paramsArray;
	
	/**
	 * 重定向配置
	 * @var array
	 */
	protected $_redirectArray;
	
	/**
	 * 初始化
	 * @param string $regex　正则表达式
	 * @param array $mArray　对应的module controller action 数组
	 * @param array $paramsArray　对应的参数数组
	 * @param array $redirectArray 重定向匹配到的URL
	 */
	abstract public function __construct($regex, Array $mArray, Array $paramsArray = NULL, $redirectArray = null);
		
	/**
	 * 是否匹配规则
	 * @return bool
	 */
	public function isMatch()
	{
		return $this->_matchStatus;
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