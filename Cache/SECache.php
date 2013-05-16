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
		if (strlen($key)>32)
			$key = md5($key);
			
		if(XF_Controller_Request_Http::getInstance()->getParam('clear') == 'cache')
		{
			$session = new XF_Session('XF_Role');
			if ($session->isEmpty() == false && $session->read() == '10')
				return XF_CACHE_EMPTY;
		}
		$secache = new secache();
		$secache->workat($this->_cache_file);
		if($secache->fetch($key, $content))
		{
			//缓存是否过过期
			preg_match('/<cache:(.*)?:cache>/', $content, $tep_array);
			if(isset($tep_array[1]))
			{
				$d = explode('_', $tep_array[1]);
				if ($d[1] == 0)
					return unserialize(str_replace($tep_array[0], '', $content));
				if (time() > $d[0]+$d[1]*60)
					return XF_CACHE_EMPTY;
				else 
					return unserialize(str_replace($tep_array[0], '', $content));
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