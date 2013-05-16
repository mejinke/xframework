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
 * @desc 数据库操作类驱动接口
 * @author jingke
 */
interface XF_Db_Drive_Interface
{
	
	/**
	 * 获取实例
	 * @return XF_Db_Drive_Interface
	 */
	public static function getInstance();
	
	/**
	 * 获取数据库连接配置信息
	 * @return XF_Db_Config_Interface
	 */
	public function getDatabaseConnectionConfigInfo();
	
	/**
	 * 设置数据库连接配置信息
	 * @param XF_Db_Config_Interface $db_config 数据库配置对象
	 * @return XF_Db_Drive_Interface
	 */
	public function setDatabaseConnectionConfigInfo(XF_Db_Config_Interface $db_config);
	
	/**
	 * 强制重置数据库连接。[如果$dbConfig与当前的XF_Db_Config_Interface信息完全一样则不重置连接。]
	 * @param XF_Db_Config_Interface $db_config
	 * @return XF_Db_Drive_Interface
	 */
	public function resetConnection(XF_Db_Config_Interface $db_config);
	
	/**
	 * 获取数据库名称
	 * @return string
	 */
	public function getDatabaseName();
	
	/**
	 * 设置数据库名称
	 * @param string $var 数据库名称
	 * @return XF_Db_Drive_Interface
	 */
	public function setDatabaseName($var);
	
	/**
	 * 查询数据资料
	 * @param string $table 数据表名称
	 * @param string $field
	 * @param string | Array | DBWhere_Interface $where
	 * @param string $order 排序
	 * @param int $size 数量
	 * @param int $ofsset 偏移量
	 * @return mixed
	 */
	public function select($table, $field = null, $where = null, $order = null, $size = 20, $ofsset = 0);
	
	/**
	 * 插入数据
	 * @param string $table 数据表名称
	 * @param array $data 数据组
	 * @return mixed
	 */
	public function insert($table, Array $data);
	
	/**
	 * 更新数据
	 * @param string $table 数据表名称
	 * @param array $data 数据组
	 * @param string $where 条件
	 * @return mixed
	 */
	public function update($table, Array $data, $where);
	
	/**
	 * 移除数据
	 * @param string $table 数据表名称
	 * @param string $where 条件
	 * @return mixed 
	 */
	public function remove($table, $where);
	
	/**
	 * 自定义查询
	 * @param mixed $var
	 * @param bool $is_select　是否为查询，如果为true，框架会尝试将查询的结果包装成数组［默认为false］
	 */
	public function execute($var, $is_select = false);
	
	/**
	 * 获取记录总数
	 * @param string $table 数据表名称
	 * @param string $field
	 * @param string | Array | DBWhere_Interface $where
	 * @param string $order 排序
	 * @param int $size 数量
	 * @param int $ofsset 偏移量
	 * @return int
	 */
	public function count($table, $field = null, $where = null, $order = null, $size = 20, $ofsset = 0);
	
	/**
	 * 获取SQL语句
	 * @param string $table 表名称
	 * @param string $field 查询的字段名
	 * @param string $where 条件
	 * @param string $order 排序
	 * @param int $size 查询数据
	 * @param int $offset 分页偏移量
	 * @param bool $rowCount 是否为查询总数
	 * @return string
	 */
	public function getSql($table, $field = null, $where = null, $order = null, $size = 20, $offset = 0, $rowCount = false);
}