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
 * @desc 表查询接口
 * @author jingke
 */
interface XF_Db_Table_Select_Interface
{

	/**
	 * 设置需要查询的字段
	 * @access public
	 * @param string | Array $var
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setFindField($var);
	
	/**
	 * 设置查询条件
	 * @access public
	 * @param string | Array $var
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setWhere($var);
	
	/**
	 * 追加查询条件
	 * @access public
	 * @param mixed $var
	 * @param string $where_type 条件类别 'AND'、 'OR' 默认为AND
	 * @return XF_Db_Table_Select_Interface
	 */
	public function appendWhere($var, $where_type = 'AND');
	
	/**
	 * 设置IN条件
	 * @access public
	 * @param string $fields 字段 例：id
	 * @param array $array 条件值 例：array(1,3,4,23,5)
	 * @param string $where_type 条件类别 'AND'、 'OR' 默认为AND
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setWhereIn($field, Array $array = array(), $where_type = 'AND');
	
	/**
	 * 设置NOT IN条件
	 * @access public
	 * @param string $fields 字段 例：id
	 * @param array $array 条件值 例：array(1,3,4,23,5)
	 * @param string $where_type 条件类别 'AND'、 'OR' 默认为AND
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setWhereNotIn($field, Array $array = array(), $where_type = 'AND');
	
	/**
	 * 设置排序
	 * @access public
	 * @param string $var
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setOrder($var);
	
	/**
	 * 设置分组
	 * @access public
	 * @param unknown_type $var
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setGroup($var);
	
	/**
	 * 获取指定段的数据 Limit
	 * @access public
	 * @param int $start 开始位置
	 * @param int $offset 偏移量
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setLimit($start, $offset = 0);
	
	/**
	 * 是否自动加载 init
	 * @access public
	 * @param bool $status
	 * @return XF_Db_Table_Select_Interface
	 */
	public function setInitAutoLoad($status = true);
	
	/**
	 * 查询所有
	 * @access public
	 * @param mixed $options 分页参数
	 * @param bool $emptyObject 当没有查询到任何资料时，是否返回空对象？默认为FALSE
	 * @return XF_Db_Table_Abstract | XF_Db_Table_Abstract Array
	 */
	public function fetchAll($options = array(), $emptyObject = FALSE);
	
	/**
	 * 查询一行
	 * @access public
	 * @return XF_Db_Table_Abstract
	 */
	public function fetchRow();
	
	/**
	 * 获取总数
	 * @access public
	 * @return int
	 */
	public function fetchCount();
	
	/**
	 * 添加一条新记录
	 * @access public
	 * @param bool $allowPrimaryKey 是否允许插入主键值？默认为false
	 * @return mixed
	 */
	public function insert($allowPrimaryKey = FALSE);
	
	/**
	 * 更新记录
	 * @access public
	 * @param array $data 资料数组
	 * @return mixed
	 */
	public function update(Array $data = null);
	
	/**
	 * 删除记录
	 * @access public
	 * @return mixed
	 */
	public function remove();
	
	/**
	 * 使字段的值<b>增加</b>指定的数值［值一般是数字，默认为　1］
	 * @access public
	 * @param string $fieldName 字段名称
	 * @param int $value　增加的值
	 * @return mixed
	 */
	public function fieldAutoAdd($fieldName, $value = 1);
	
	/**
	 * 使字段的值<b>减少</b>指定的数值［值一般是数字，默认为　1］
	 * @access public
	 * @param string $fieldName 字段名称
	 * @param int $value　减少的值
	 * @return mixed
	 */
	public function filedAutoLessen($fieldName, $value = 1);
	
	/**
	 * 执行自定义查询
	 * @access public
	 * @param string $var
	 * @param bool $packing 尝试包装成对象【默认为false】
	 * @return mixed
	 */
	public function execute($var, $packing = false);
	
	/**
	 * 设置缓存方式
	 * @access public
	 * @param XF_Cache_Interface $cache 缓存对象
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function setCacheClass(XF_Cache_Interface $cache);
	

	/**
	 * 设置数据缓存时间 ［单位：分钟］
	 * @access public
	 * @param int $minutes 时长分钟数
	 * @param bool $queryAfterClear 执行完查询后重置缓存时间为零？ 默认为 TRUE
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function setCacheTime($minutes = 0, $queryAfterClear = TRUE);
	
}