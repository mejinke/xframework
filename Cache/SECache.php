<?php
require_once XF_PATH.'/Custom/secache/secache.php';
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2012
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-12-25
 * -+-----------------------------------
 *
 * @desc 基于secache的缓存类
 * @author jingke
 */
class XF_Cache_SECache extends XF_Cache_Abstract
{
	/**
	 * 当前实例
	 * @access private
	 * @var XF_Cache_SECache
	 */
	private static $_instance = null;
	
	/**
	 * 缓存文件路径
	 * @var string
	 */
	private $_cache_file;
	
	private function __construct(){}
	private function __clone(){}
	
	public static function getInstance()
	{
		if (self::$_instance == null)
		{
			self::$_instance = new self();
		}
		self::$_instance->setCacheSaveFile(TEMP_PATH.'/Cache/Data');
		return self::$_instance;
	}

	/**
	 * 设置缓存文件保存的文件路径
	 * @param string $file
	 * @return XF_Cache_SECache
	 */
	public function setCacheSaveFile($file)
	{
		$this->_cache_file = $file;
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
		
		if (strlen($key)>32)
			$key = md5($key);
			
		$secache = new secache();
		$secache->workat($this->_cache_file);
		
		$content = NULL;
		$chunk_key = XF_DataPool::getInstance()->get(self::ChunkCacheKeyIdentify);
		//区域缓存key不为空
		if ($chunk_key != false)
		{
			$content = $this->_getData($chunk_key);
			if ($content != NULL)
			{
				return is_array($content) && isset($content[$key]) ? $content[$key] : XF_CACHE_EMPTY;
			}
			if ($secache->fetch($chunk_key, $content))
			{
				$this->_addReadDebug($start_time, 'SECache');
			
				//缓存是否过过期
				preg_match('/<cache:(.*)?:cache>/', $content, $tmp);
				if(isset($tmp[1]))
				{
					$d = explode('_', $tmp[1]);
					if ($d[1]==0)
						$content = str_replace($tmp[0], '', $content);
					if (time() > $d[0]+$d[1]*60)
						return XF_CACHE_EMPTY;
					else 
						$content = str_replace($tmp[0], '', $content);
				}
				$content = unserialize($content);	
				$this->_pushData($chunk_key, $content);
			}
			return is_array($content) && isset($content[$key]) ? $content[$key] : XF_CACHE_EMPTY;
		}
		
		$content = $this->_getData($key);
		if ($content != NULL) return $content;
		if($secache->fetch($key, $content))
		{
			$this->_addReadDebug($start_time, 'SECache');
				
			//缓存是否过过期
			preg_match('/<cache:(.*)?:cache>/', $content, $tmp);
			if(isset($tmp[1]))
			{
				$d = explode('_', $tmp[1]);
				if ($d[1] == 0)
					$content = str_replace($tmp[0], '', $content);
				if (time() > $d[0]+$d[1]*60)
					return XF_CACHE_EMPTY;
				else 
					$content = str_replace($tmp[0], '', $content);

				$content = unserialize($content);
				$this->_pushData($key, $content);
				return $content;
			}
		}
		return XF_CACHE_EMPTY;
	}
	
	/**
	 * 添加缓存内容
	 * @access public
	 * @param string $key 
	 * @param string $value
	 * @return Cache_FileSystem
	 */
	public function add($key, $value)
	{
		if (strlen($key)>32)
			$key = md5($key);
		$time_tag = '<cache:'.time().'_'.$this->_cache_time.':cache>';
		$value = serialize($value).$time_tag;
		$secache = new secache();
		$secache->workat($this->_cache_file);
		$secache->store($key, $value);
		$this->_cache_time = 0;
		return $this;
	}
	
	public function remove($key = NULL)
	{}
	
	public  function removeAll()
	{}
	
	/**
	 * 是否存在缓存
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function hasCache($key)
	{
		$secache = new secache();
		$secache->workat($this->_cache_file);
		return $secache->fetch($key, $content);
	}
}