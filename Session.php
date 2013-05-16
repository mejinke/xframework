<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-12
 * -+-----------------------------------
 *
 * @desc session类
 * @author jingke
 */
class XF_Session implements XF_Session_Interface
{
	/**
	 * 默认会话命名空间
	 * @var string
	 */
	const NAMESPACE_DEFAULT = 'XF_MEMBER';
	
	/**
	 * 当前会话命名空间
	 * @var string
	 */
	protected $_namespace;
	
	/**
	 * 当前是否已上锁
	 * @var bool
	 */
	protected $_lock = FALSE;
	
	/**
	 * 初始化session
	 * @param string $namespace 会话命名空间
	 */
	public function __construct($namespace = self::NAMESPACE_DEFAULT)
	{
		$namespace = (string) $namespace;
		$this->_namespace = $namespace;
		if (!isset($_SESSION[$this->_namespace]))
			$_SESSION[$this->_namespace] = NULL;
	}
	
	/**
	 * session是否为空
	 * @return bool
	 */
	public function isEmpty()
	{
		if (isset($_SESSION[$this->_namespace]) && $_SESSION[$this->_namespace]!==null)
			return false;
		return true;
	}
	
	/**
	 * 写入内容
	 * @param mixed $content　内容
	 * @return XF_Session
	 */
	public function write($content)
	{
		if ($this->_lock == FALSE)
		{
			$this->_lock = TRUE;
			if ($content === null && isset($_SESSION[$this->_namespace]))
				unset($_SESSION[$this->_namespace]);
			else
				$_SESSION[$this->_namespace] = $content;
			$this->_lock = FALSE;
		}
		return $this;
	}
	
	/**
	 * 销毁session
	 * @return XF_Session
	 */
	public function clear()
	{
		if (isset($_SESSION[$this->_namespace]))
			unset($_SESSION[$this->_namespace]);
		return $this;
	}
	
	/**
	 * 读取内容
	 * @return mixed
	 */
	public function read()
	{
		return $_SESSION[$this->_namespace];
	}
	
	/**
	 * 是否存在指定的内容
	 * @param string $key 键
	 * @return bool
	 */
	public function hasContent($key)
	{
		$key = (string) $key;
		return isset($_SESSION[$this->_namespace][$key]);
	}
}