<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-09-24
 * -+-----------------------------------
 *
 * @desc 数据驱动抽象类，所有扩展DB层都必需继承该类
 * @author jingke
 */
abstract class XF_Db_Drive_Abstract implements XF_Db_Drive_Interface
{
	
	/**
	 * 当前实例
	 * @var XF_Db_Drive_Abstract
	 */
	protected static $_instance = null;
	
	/**
	 * 数据库名称
	 * @var string
	 */
	protected $_db_name = null;
	
	/**
	 * 数据库连接
	 * @var Mysql Resource
	 */
	protected $_db_connection = null;
	
	/**
	 * 数据库连接配置对象
	 * @var XF_Db_Config_Interface
	 */
	protected $_db_config = null;
	
	/**
	 * 是否显示查询语句
	 * @var bool
	 */
	protected $_show_query = false;
	
	public function getDatabaseConnectionConfigInfo()
	{
		return $this->_db_config;
	}
	
	public function setDatabaseConnectionConfigInfo(XF_Db_Config_Interface $db_config)
	{
		$this->_db_config = $db_config;
		return $this;
	}
	
	public function getDatabaseName()
	{
		return $this->_db_name;
	}

	public function setDatabaseName($var)
	{
		$this->_db_name = $var;
		return $this;
	}
	
	/**
	 * 是否显示查询语句
	 * @param bool $status
	 * @return XF_Db_Drive_Abstract
	 */
	public function showQuery($status = false)
	{
		$this->_show_query = $status;
		return $this;
	}
}