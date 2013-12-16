<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-09
 * -+-----------------------------------
 *
 * @desc Ｍysql数据库查询类
 * @author jingke
 */
class XF_Db_Table_Select_Mysql extends XF_Db_Table_Select_Abstract
{
	
	
	public function __construct(XF_Db_Table_Abstract $table)
	{
		$this->_db_name = $table->getDbName();
		$this->_db_table = $table;
		
	}
	
	/**
	 * 设置需要查询的字段
	 * @access public
	 * @param string | Array $var
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setFindField($var)
	{
		$this->_adv_find = XF_Db_Tool::findFormat($var);
		return $this;
	}
	
	/**
	 * 设置查询条件
	 * @access public
	 * @param string | Array $var
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setWhere($var)
	{
		if (XF_Functions::isEmpty($var) === false)
			$this->_adv_where = ' WHERE '.XF_Db_Tool::whereFormat($var);
		return $this;
	}
	
	/**
	 * 追加查询条件
	 * @access public
	 * @param string | Array $var
	 * @param string $where_type 条件类别 [ AND、OR  默认 AND]
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function appendWhere($var, $where_type = 'AND')
	{
		if (XF_Functions::isEmpty($this->_adv_where))
			$this->setWhere($var);
		else if (XF_Functions::isEmpty($var) === false)	
		{
			if ($where_type == 'AND') 
				$this->_adv_where .= ' AND '.XF_Db_Tool::whereFormat($var);
			elseif ($where_type == 'OR')
			{
				$this->_adv_where = XF_String::str_replace_once(' WHERE ', '', $this->_adv_where);
				$this->_adv_where = ' WHERE ('.$this->_adv_where.') OR ('.XF_Db_Tool::whereFormat($var).')';
			} 
		}
		return $this;
	}
	
	/**
	 * 设置IN条件
	 * @access public
	 * @param string $fields 字段 例：id
	 * @param array $array 条件值 例：array(1,3,4,23,5)
	 * @param string $where_type 条件类别 [ AND、OR  默认 AND]
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setWhereIn($field, Array $array = array(), $where_type = 'AND')
	{
		return $this->_setWhereInORNotIn($field, $array, $where_type, 'IN');
	}
	
	/**
	 * 设置NOT IN条件
	 * @access public
	 * @param string $fields 字段 例：id
	 * @param array $array 条件值 例：array(1,3,4,23,5)
	 * @param string $where_type 条件类别  [ AND、OR  默认 AND]
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setWhereNotIn($field, Array $array = array(), $where_type = 'AND')
	{
		return $this->_setWhereInORNotIn($field, $array, $where_type, 'NOT IN');
	}
	
	/**
	 * 设置IN或NOT IN条件
	 * @param string $finds 字段 例：id
	 * @param array $array 条件值 例：array(1,3,4,23,5)
	 * @param string $where_type 条件类别 [ MAND、OR  默认 AND]
	 * @param string $type IN或NOT IN
	 * @return XF_Db_Table_Select_Mysql
	 */
	private function _setWhereInORNotIn($fields, Array $array = array(), $where_type = 'AND', $type = 'IN')
	{
		if(!empty($array))
		{
			$var = XF_Db_Tool::inFormat($array, $type);
			if(XF_Functions::isEmpty($this->_adv_where) == false)
				$this->_adv_where .= ' '.$where_type.' `'.$fields.'`'.$var;
			else
				$this->_adv_where = ' WHERE `'.$fields.'`'.$var;
		}
		return $this;
	}
	
	/**
	 * 设置排序
	 * @access public
	 * @param string $var
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setOrder($var)
	{
		if (XF_Functions::isEmpty($var) == false)
			$this->_adv_order .= ' ORDER BY '.$var;
		return $this;
	}
	
	/**
	 * 设置分组
	 * @access public
	 * @param unknown_type $var
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setGroup($var)
	{
		if (XF_Functions::isEmpty($var) == false)
			$this->_adv_order = ' GROUP BY '.$var;
		return $this;
	}
	
	/**
	 * 获取指定段的数据 Limit
	 * @access public
	 * @param int $start 开始位置
	 * @param int $offset 偏移量
	 * @return XF_Db_Table_Select_Mysql
	 */
	public function setLimit($start, $offset = null)
	{
		if (is_numeric($start) && $offset === null )
		$this->_adv_limit = array(0, $start);
		if(is_numeric($start) && is_numeric($offset))
			$this->_adv_limit = array($start, $offset);
		elseif ($start === false || $offset === false)
			$this->_adv_limit = array(false, false);
		return $this;
	}
	
	public function execute($var, $packing = false)
	{
		$this->_allotDatabaseServer();
		//检测缓存文件
		$cacheFile = TEMP_PATH.'/Cache/Data/'.$this->_db_name.'/'.$this->_db_table->getTableName().'/'.md5($var).'.php';
		 
		$result = $this->_getDataCache($cacheFile);
		if ($result != XF_CACHE_EMPTY)
			return $result;
		
		$this->_db_drive_connect->showQuery($this->_show_query);
		$result = $this->_db_drive_connect->execute($var, true);
		if ($packing === true) 
		{
			$result =  $this->_packing($result);
		}
		$this->_setDataCache($result, $cacheFile);
		return $result;
	}
}