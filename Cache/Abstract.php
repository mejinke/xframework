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
 * @desc 缓存抽象类，所有缓存类都必须继承此类
 * @author jingke
 */
abstract class XF_Cache_Abstract implements XF_Cache_Interface
{
	/**
	 * 区域批量缓存识标
	 * @var string
	 */
	const ChunkCacheIdentify = 'XF_ChunkCacheIdentify';
	
	/**
	 * 区域批量缓存统一key识标
	 * @var string
	 */
	const ChunkCacheKeyIdentify = 'XF_ChunkCacheKeyIdentify';
	
	/**
	 * 缓存时长 单位：分钟
	 * @var int
	 */
	protected $_cache_time = 0;
	
	/**
	 * 被取出过的缓存内容列表
	 * @var array
	 */
	private $_tmp_data;
	
	/**
	 * 设置缓存时长
	 * @access public
	 * @param int $minute
	 * @return XF_Cache_Abstract
	 */
	public function setCacheTime($minute)
	{
		if (is_int($minute))
		{
			$this->_cache_time = $minute;
		}
		
		return $this;
	}
	
	/**
	 * 添加已取出的缓存
	 * @access protected
	 * @param string $key 缓存键名
	 * @param string $data 缓存内容
	 */
	protected function _pushData($key, $data)
	{
		$this->_tmp_data[$key] = $data;
		return $this;
	}
	
	/**
	 * 获取之前取出过的缓存
	 * @access protected
	 * @param string $key 缓存键名
	 * @return mixed  不存在则返回NULL
	 */
	protected function _getData($key)
	{
		return 	isset($this->_tmp_data[$key]) ? $this->_tmp_data[$key] : NULL;
	}
	
	/**
	 * 开始块缓存
	 * @access public
	 * @param int $minute 缓存时间：分钟
	 * @param string $cacheKeyName 当前区域缓存保存的key名 默认为NULL
	 * @return bool
	 */
	public function begin($minute, $cacheKeyName = NULL)
	{
		if (!is_numeric($minute) || $minute <= 0) return false;
		XF_DataPool::getInstance()->add(self::ChunkCacheIdentify, get_class($this).'$'.strval($minute));
		if (is_string($cacheKeyName) && !empty($cacheKeyName))
			XF_DataPool::getInstance()->add(self::ChunkCacheKeyIdentify, md5($cacheKeyName));
	}
	
	/**
	 * 结束块缓存
	 * @access public
	 * @return void
	 */
	public function end()
	{
		XF_DataPool::getInstance()->remove(self::ChunkCacheIdentify)->remove(self::ChunkCacheKeyIdentify);
	}
	
	/**
	 * 记录缓存读取时间用于调试
	 * @access protected
	 * @param int $start_time
	 * @param string $by
	 * @return void
	 */
	protected function _addReadDebug($start_time, $by)
	{
		//
		$end_time = microtime(true);
		$now_time = sprintf ("%01.5f",($end_time-$start_time)).'s By '.$by;
		$count_time = XF_DataPool::getInstance()->get('CacheTimeCount', 0);
		XF_DataPool::getInstance()->add('CacheTimeCount', sprintf ("%01.5f",$count_time+($end_time-$start_time>0 ? $end_time-$start_time:0)));
		XF_DataPool::getInstance()->addList('CacheTimeList', $now_time);
	}
}