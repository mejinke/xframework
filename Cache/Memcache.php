<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-10-21
 * -+-----------------------------------
 *
 * @desc Memcached缓存类
 * @author jingke
 */
class XF_Cache_Memcache extends XF_Cache_Abstract
{
	
	/**
	 * 当前实例
	 * @access private
	 * @var Cache_Memcache
	 */
	private static $_instance = null;
	
	/**
	 * Memcache
	 * @access private
	 * @var Memcache
	 */
	private $_memcache = null;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前缓存对象实例
	 * @access public
	 * @return XF_Cache_Memcache
	 */
	public static function getInstance()
	{
		if (self::$_instance == null)
		{
			//是否存在memcache缓存服务器配置
			$servers = XF_Config::getInstance()->getMemcaches();
			if (!isset($servers[0]))
				throw new XF_Cache_Exception('Memcache server config is empty');
			$server = explode(':', $servers[0]);
				
			//检测是否安装memcache PHP扩展
			if (!class_exists('Memcache', FALSE))
				throw new XF_Cache_Exception('Memcache PECL expands has not installed or has begun using');
			self::$_instance = new self();
			self::$_instance->_memcache = new Memcache;
			$status = self::$_instance->_memcache->connect($server[0], $server[1]);
			if ($status == FALSE)
				throw new XF_Cache_Exception('Memcache connect failure');
		}
		return self::$_instance;
	}
	
	/**
	 * 添加memcache服务器
	 * @param string $host
	 * @param int $port
	 * @return XF_Cache_Memcache
	 */
	public function addServer($host, $port)
	{
		if ($this->_memcache == null)
			$this->_memcache = new Memcache;
			
		$this->_memcache->addServer($host, $port);
		return $this;
	}
	
	/**
	 * 读取缓存内容
	 * @access public
	 * @param string $key 缓存KEY
	 * @return mixed
	 */
	public function read($key)
	{
		$start_time = microtime(true);
		
		//是否强制清除缓存？
		if (XF_DataPool::getInstance()->get('clearCache') === true)
		{
			return XF_CACHE_EMPTY;
		}
		
		$data = XF_CACHE_EMPTY;
		$value = $this->_memcache->get($key);
		if ($value !== false)
			$data = $value;
		
		$this->_addReadDebug($start_time, 'Memcache');
			
		return $data;
	}
	
	/**
	 * 添加缓存内容
	 * @access public
	 * @param string $key 
	 * @param string $value
	 * @return XF_Cache_Memcache
	 */
	public function add($key, $value)
	{
		if ($this->_cache_time == 0 ) return false;
		$this->_memcache->set($key, $value, false, $this->_cache_time * 60);
		return $this;
	}
	
	public function remove($key = NULL)
	{
		return $this->_memcache->delete($key);
	}
	
	public  function removeAll()
	{
		return $this->_memcache->flush();
	}
	
	/**
	 * 是否存在缓存
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function hasCache($key)
	{
		if($this->_memcache->get($key) !== false)
			return true;
		return false;
	}
}