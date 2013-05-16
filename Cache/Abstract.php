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
	 * 缓存时长 单位：分钟
	 * @var int
	 */
	protected $_cache_time = 0;
	
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
}