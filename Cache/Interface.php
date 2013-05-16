<?php
if (!defined('XF_CACHE_EMPTY')) define('XF_CACHE_EMPTY','__EMPTY_CACHE__');
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-10-21
 * -+-----------------------------------
 *
 * @desc 缓存类接口
 * @author jingke
 */
interface XF_Cache_Interface
{
	
	/**
	 * 获取当前缓存对象实例
	 * @access public
	 * @return XF_Cache_Interface
	 */
	public static function getInstance();
	
	/**
	 * 设置缓存时长
	 * @access public
	 * @param int $minute
	 * @return XF_Cache_Interface
	 */
	public function setCacheTime($minute);
	
	/**
	 * 读取缓存内容
	 * @access public
	 * @param string $key 缓存KEY
	 * @return mixed
	 */
	public function read($key);
	
	/**
	 * 添加缓存内容
	 * @access public
	 * @param string $key 
	 * @param string $value
	 * @return XF_Cache_Interface
	 */
	public function add($key, $value);
	
	/**
	 * 删除缓存
	 * @param null|string $key
	 * @return XF_Cache_Interface
	 */
	public function remove($key = NULL);
	
	/**
	 * 删除所有缓存
	 * @return XF_Cache_Interface
	 */
	public function removeAll();
	
	/**
	 * 是否存在缓存
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function hasCache($key);
}