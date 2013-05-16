<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-06-13
 * -+-----------------------------------
 *
 * @desc Cookie类
 * @author jingke
 */
class XF_Cookie implements XF_Cookie_Interface
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
		if (!isset($_COOKIE[$this->_namespace]))
			setcookie($this->_namespace,'',time()-1);
	}
	
	/**
	 * session是否为空
	 * @return bool
	 */
	public function isEmpty()
	{
		if (!isset($_COOKIE[$this->_namespace]))
			return true;
		return XF_Functions::isEmpty($_COOKIE[$this->_namespace]);
	}
	
	/**
	 * 写入内容
	 * @param mixed $content
	 * @param int $expire 过期时间 默认为一周7天 单位：秒
	 * @param string $path 有效路径 默认为NULL
	 * @return XF_Cookie
	 */
	public function write($content, $expire = 604800, $path = '/' )
	{
		if ($this->_lock == FALSE)
		{
			$this->_lock = TRUE;
			if ($content == null)
				setcookie($this->_namespace,'1',time()-1, $path);
			else
			{
				setcookie($this->_namespace, XF_Functions::authCode(serialize($content)), time()+$expire, $path);
			}
				
			$this->_lock = FALSE;
		}
		
		return $this;
		
	}
	
	/**
	 * 读取内容
	 * @return mixed
	 */
	public function read()
	{
		if (isset($_COOKIE[$this->_namespace]))
			return unserialize(XF_Functions::authCode($_COOKIE[$this->_namespace], 'DECODE'));
		return null;
	}
	
	/**
	 * 是否存在指定的内容
	 * @param string $key 键
	 * @return bool
	 */
	public function hasContent($key)
	{
		$key = (string) $key;
		return isset($_COOKIE[$this->_namespace][$key]);
	}
}