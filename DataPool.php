<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-10-08
 * -+-----------------------------------
 * 
 * @desc 数据池，在框架运行时保存各种数据
 * @author jingke
 */
class XF_DataPool
{
	
	/**
	 * 当前实例
	 * @var DataPool
	 */
	private static $_instance;
	
	/**
	 * 数据池
	 * @var array
	 */
	private $_data_pools = array();
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前数据池实例
	 * @return XF_DataPool
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 添加数据
	 * @param string $key 键
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function add($key, $value)
	{
		$this->_data_pools[$key] = $value;
		return $this;
	}
	
	/**
	 * 以列表的方式添加数据
	 * @param string $key 键
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function addList($key, $value)
	{
		$this->_data_pools[$key][] = $value;
		return $this;
	}
	
	/**
	 * 添加Hash数据
	 * @param string $key 键
	 * @param string $item 项名称
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function addHash($key, $item, $value)
	{
		$this->_data_pools[$key][$item] = $value;
		return $this;
	}
	
	/**
	 * 以Hash列表的方式添加数据
	 * @param string $key 键
	 * @param string $item 项名称
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function addHashList($key, $item, $value)
	{
		$this->_data_pools[$key][$item][] = $value;
		return $this;
	}
	
	/**
	 * 更新池中的内容
	 * @param string $key 键
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function update($key, $value)
	{
		if (isset($this->_data_pools[$key]))
		{
			$this->_data_pools[$key] = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * 替换池中指定键内容，不存在KEY则添加
	 * @param string $key 键
	 * @param mixed $value 值
	 * @return XF_DataPool
	 */
	public function replace($key, $value)
	{
		return $this->add($key, $value);
		return $this;
	}
	
	/**
	 * 删除池中指定键的内容
	 * @param string $key 键
	 * @return XF_DataPool
	 */
	public function remove($key)
	{	
		if (isset($this->_data_pools[$key]))
			unset($this->_data_pools[$key]);
		return true;
	}
	
	/**
	 * 清空数据池
	 * @return bool
	 */
	public function removeAll()
	{
		$this->_data_pools = array();
		return true;
	}
	
	/**
	 * 获取池中指定 的内容
	 * @param string $key 键
	 * @param mixed $default 如果不存在KEY将要返回的默认为值，默认为false
	 * @return mixed
	 */
	public function get($key, $default = false)
	{
		if (isset($this->_data_pools[$key]))
			return $this->_data_pools[$key];
		return $default;
	}
	
	/**
	 * 获Hash取池中指定 的内容
	 * @param string $key 键
	 * @param string $item 项名称
	 * @param mixed $default 默认值 
	 * @return mixed
	 */
	public function getHash($key, $item, $default = false)
	{
		if (isset($this->_data_pools[$key][$item]))
			return $this->_data_pools[$key][$item];
		return $default;
	}
	
}