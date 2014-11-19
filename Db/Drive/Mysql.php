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
 * @desc Mysql数据库驱动类
 * @author jingke
 */
class XF_Db_Drive_Mysql extends XF_Db_Drive_Abstract
{
	

	/**
	 * 单例模式不允许实例化
	 */
	private function __construct(){}
	private function __clone(){}
	
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 连接数据库
	 */
	private function _connection()
	{
		if ($this->_db_config instanceof XF_Db_Config_Interface && $this->_db_connection == null)
		{
			if (!function_exists('mysql_connect'))
			{
				throw new XF_Db_Drive_Exception('Mysql的PECL扩展尚未安装或启用');
			}
				
			$stime = microtime(true);
			$this->_db_connection = @mysql_connect( $this->_db_config->getHost(), $this->_db_config->getAccount(), $this->_db_config->getPassword() ); 
			$etime = microtime(true);
			if (XF_Config::getInstance()->getSaveDebug())
			{
				$str = 'Connection mysql server '.$this->_db_config->getHost().' '.sprintf ("%.5f",($etime - $stime)).'s';
				if ($etime - $stime > 0.5)
					$str = '<font style="color:red">'.$str.'</font>';
				XF_DataPool::getInstance()->addList('ConnectionMysql', $str);
				$count = XF_DataPool::getInstance()->get('ConnectionMysqlTimeCount', 0);
				XF_DataPool::getInstance()->add('ConnectionMysqlTimeCount', sprintf ("%.5f",$count+($etime - $stime>0 ? $etime - $stime:0)));
			}
			if (!$this->_db_connection)
			{
				throw new XF_Db_Drive_Exception('无法连接Mysql服务器('.$this->_db_config->getHost().')  Message: '.mysql_error());
			}
			else
			{
				mysql_query("set names '".$this->_db_config->getChar()."'", $this->_db_connection);
			}	
		}
	}
	
	/**
	 * 查询数据资料
	 * @param string $table 数据表名称
	 * @param string $field 查询的字段
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @param int $size 数量
	 * @param int $ofsset 偏移量
	 * @return mixed
	 */
	public function select($table, $field = null, $where = null, $order = null, $group = null, $size = 20, $ofsset = 0)
	{
		$query = $this->_getSQL($table, $field, $where, $order, $group, $size, $ofsset);
		return $this->execute($query, true);
	}
	
	/**
	 * 插入数据
	 * @param string $table 数据表名称
	 * @param array $data 数据组
	 * @return mixed
	 */
	public function insert($table, Array $data)
	{
		foreach($data as $key => $val)
		{
			//过滤非标量
			if(is_scalar($val))
			{
				if ($val === '$NULL')
				{
					$values[] = "NULL";
				}
				else
				{
					$values[] = "'".XF_Db_Tool::escape($val)."'";
				}
				$fields[] = '`'.$key.'`';
			}
		}
		
		$query = 'INSERT INTO `'.$this->_db_name.'`.`'.$table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
		if ($this->execute($query))
		{
			return $this->getId();
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 更新数据
	 * @param string $table 数据表名称
	 * @param array $data 数据组
	 * @param string $where 条件
	 * @return mixed
	 */
	public function update($table, Array $data, $where)
	{
		$set = array();
		foreach($data as $key=>$val)
		{
			//过滤非标量
			if(is_scalar($val))
			{
				if ($val === '$NULL')
				{
					$set[] = '`'.$key."`= NULL";
				}
				elseif (strpos($val, '$PK') === 0)
				{
					$val = substr($val, 3);
					$set[] = '`'.$key."`=$val";
				}
				else
				{
					$set[] = '`'.$key."`='".XF_Db_Tool::escape($val)."'";
				}	
			}
		}
		
		if(empty($set))
		{
			return false;
		}
		
		$query = 'UPDATE `'.$this->_db_name.'`.`'.$table.'` SET '.implode(',',$set).' '.$where;
		return $this->execute($query);
	}
	
	/**
	 * 移除数据
	 * @param string $table 数据表名称
	 * @param string $where 条件
	 * @return mixed 
	 */
	public function remove($table, $where)
	{
		$query = 'DELETE FROM `'.$this->_db_name.'`.`'.$table.'` '.$where;
		return $this->execute($query);
	}
	
	/**
	 * 强制重置数据库连接。[如果$dbConfig与当前的DBConfig_Interface信息完全一样则不重置连接。]
	 * @param DBConfig_Interface $dbConfig
	 * @return XF_Db_Drive_Mysql
	 */
	public function resetConnection(XF_Db_Config_Interface $db_config)
	{
		if ($this->_db_config != $db_config)
		{
			$this->_db_connection = null;
			$this->_connection();
		}
		return $this;
	}
	
	/**
	 * 将资源转换为数组
	 * @param Resource $result
	 * @return false | array
	 */
	private function _getResultArray($result)
	{
		$tep_array = false;
		try{
			if ($result)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$tep_array[] = $row;
				}
			}
		}
		catch (Exception $e)
		{
			return false;
		}
		return $tep_array;
	}
	
	/**
	 * 自定义查询
	 * @param string $var
	 */
	public function query($var)
	{
		return $this->_getResultArray(mysql_query($var, $this->_db_connection));
	}
	
	/**
	 * 执行SQL语句
	 * @param string $query
	 * @param bool $is_select　是否为查询，如果为true，框架会尝试将查询的结果包装成数组［默认为false］
	 * @throws XF_Db_Table_Select_Exception
	 */
	public function execute($query, $is_select = false)
	{	
		
		if ($this->_show_query === TRUE)
		{
			echo $query.'<br/>';
		}
		
		//多次查询相同的SQL时只执行一次。
		if (XF_DataPool::getInstance()->get('CLOSE_SQL_DATA_CACHE') == false)
		{
			$data = XF_DataPool::getInstance()->get('SQL_DATA');
			if (is_array($data) && isset($data[md5($query)]))
			{
				return $data[md5($query)];
			}
		}
		
		$stime = microtime(true);
		$this->_connection();
		$result = mysql_query($query, $this->_db_connection);
		$etime = microtime(true);
		$use = sprintf('%.5f', $etime - $stime);
		
		//是否记录debug信息
		if (XF_Config::getInstance()->getSaveDebug())
		{
			$str = $query.' '.$use.'s';
			if ($use > 0.3)
			{
				$str = '<font style="color:red">'.$str.'</font>';
			}
			XF_DataPool::getInstance()->addList('Querys', $str);
			$count = XF_DataPool::getInstance()->get('QueryTimeCount', 0);
			XF_DataPool::getInstance()->add('QueryTimeCount', sprintf("%.5f", $count + $use));
		}
		
		if (mysql_error($this->_db_connection) !='')
		{
			throw new XF_Db_Table_Select_Exception(mysql_error($this->_db_connection));
		}
			
		if ($result == false)
		{
			return false;
		}
		
		if ($is_select == true && strpos(strtolower(trim($query)), 'select') === 0)
		{
			$result = $this->_getResultArray($result);
			//添加临时缓存
			if (XF_DataPool::getInstance()->get('CLOSE_SQL_DATA_CACHE') == false)
			{
				$data[md5($query)] = $result;
				XF_DataPool::getInstance()->add('SQL_DATA', $data);
			}
		}
			
		return $result;
	}
	
	/**
	 * 获取记录总数
	 * @param string $table 数据表名称
	 * @param string $field
	 * @param string | Array  $where
	 * @param string $order 排序
	 * @param int $size 数量
	 * @param int $ofsset 偏移量
	 */
	public function count($table, $field = null, $where = null, $order = null, $group = null, $size = 20, $ofsset = 0)
	{
		$query = $this->_getSQL($table, $field, $where, $order, $group, $size, $ofsset, true);
		return $this->execute($query, true);
	}
	
	/**
	 * 获取上一次插入数据时生成的ID
	 * @access public
	 * @return int
	 */
	public function getId()
	{
		return ($id = mysql_insert_id($this->_db_connection)) >= 0 ? $id : @mysql_result(mysql_query("SELECT last_insert_id()", $this->_db_connection), 0);
	}
	
	/**
	 * 生成MySQL语句
	 * @access private
	 * @param bool $rowCount 是否为查询总数[对大数据量下进行优化]
	 * @return string SQL语句
	 */
	private function _getSQL($table, $field = null, $where = null, $order = null, $group = null, $size = 20, $ofsset = 0, $rowCount = false)
	{
		$field == null ? $field = '*' : $field;
		$rowCount == true ? $field = 'count(*) as count':'';
		$where == null ? $where = '' : $where;
		$order == null ? $order = '' : $order;
		if ($size!= false && $ofsset!=false)
		{
			$size == null ? $size = 20 : $size;
			$ofsset == null ? $ofsset = 0 : $ofsset;
		}
		
		$sql = 'SELECT '.$field.' FROM `'.$this->_db_name.'`.`'.$table.'`'.$where;
		if (is_string($group) && $group !='')
		{
			if (is_string($order) && $order != '')
			{
				//先排序？
				if (strpos($group, '#GROUP') !== false)
				{
					$sql = 'SELECT '.$field.' FROM ('.$sql.' '.$order.') AS XF_NewTableGroup '.str_replace('#GROUP', 'GROUP', $group);
				}
				else 
				{
					$sql .= $group.$order;
				}
			}
			else
			{
				$sql .= $group;
			}
		}
		else
		{
			$sql .= $order;
		}
		if ($size !== false && $ofsset !== false)
		{
			$sql.= ' LIMIT '.$ofsset.','.$size;
		}
		
		//如果是查询行数，并且是分组查询，则需要再次查询count
		if ($rowCount == true && $group != null)
		{
				$sql = 'SELECT count(*) as count FROM ('.$sql.') AS XF_NewTableGroupCount';
		}
		return $sql;
	}
	
	
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
	public function getSql($table, $field = null, $where = null, $order = null,  $group = null, $size = 20, $offset = 0, $rowCount = false)
	{
		return $this->_getSQL($table, $field, $where, $order, $group, $size, $offset, $rowCount);
	}
	
	/**
	 * 获取指定表的所有字段
	 * @access public
	 * @param string $table 表名称
	 * @return array
	 */
	public function getFields($table)
	{
	
		$tepArray = false;

		//获取表字段信息
		if($this->_tableIsExist($table))
		{
			$this->_connection();
			$fields = mysql_list_fields($this->_db_name, $table, $this->_db_connection);
			$columns = mysql_num_fields($fields);
			for ($i = 0; $i < $columns; $i++)
			{
				$tepArray[$i]=mysql_field_name($fields, $i);
			}
			return $tepArray;
		}
	}
	
	/**
	 * 检测给定的表名称是否存在当前库中
	 * @access public
	 * @param string $table 表名称
	 * @return array
	 */
	protected function _tableIsExist($table)
	{
		$sql="SHOW TABLES FROM  `".$this->_db_name."` like '".$table."'";
		return $this->execute($sql);
	}
}