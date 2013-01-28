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
 * @desc 数据表实体抽象类
 * @author jingke
 */
abstract class XF_Db_Table_Abstract
{
	/**
	 * 表实体主键
	 * @var string
	 */
	protected $_primary_key;
	
	/**
	 * 数据库名称
	 * @var string
	 */
	protected $_db_name;
	
	/**
	 * 实体的表名称
	 * @var string
	 */
	protected $_table_name;
	
	/**
	 * 字段总数［数据表字段］
	 * @var int
	 */
	private $_field_count = 0;
	
	/**
	 * 对象的数组形式
	 * @var array
	 */
	private $_result_array;
	
	/**
	 * 表字段验证列表
	 * @var XF_Db_Table_ValidateRule
	 */
	protected  $_field_validate_rule;
	
	/**
	 * 数据表查询对象
	 * @var XF_Db_Table_Select_Abstract
	 */
	private static $_select;
	
	/**
	 * 设置属性值时，是否开启数据验证
	 * @var bool
	 */
	private $_set_var_validate = false;
	
	/**
	 * 字段关联列表
	 * @var array
	 */
	private $_field_associateds = array();

	/**
	 * 设置表字段资料
	 * @param string $name 字段名称
	 * @param mixed $value 值
	 */
	public function __set($name, $value)
	{
		//设置表字段资料
		if (is_array($this->_result_array) && array_key_exists($name, $this->_result_array))
		{ 
			if ($name != $this->getPrimaryKey())
			{
				if ($value === NULL)
				{
					unset($this->_result_array[$name]); 
					return;
				}
				$value = XF_Functions::numberToString($value);
				
				if ($this->_set_var_validate == true && $this->_field_validate_rule)
				{
					$data = array($name => $value);
					if(XF_Db_Table_Validate::getInstance()->validateData($data, $this->getFieldValidateRule()->toArray()) === false)
						throw new XF_Db_Table_Exception(XF_DataPool::getInstance()->get('TableFieldDataValidateError'));
					$this->_result_array[$name] = $data[$name];
				}
				else
				{
					$this->_result_array[$name] = $value;
				}
			}
		}
		else
		{
			//2013-1-9
			if (strpos($name, '[]')!== false)
			{
				$name = str_replace('[]', '', $name);
				$this->_result_array[$name][] = XF_Functions::numberToString($value);	
			}
			else
				$this->_result_array[$name] = XF_Functions::numberToString($value);	
		}
			
	}
	
	/**
	 * 获取表字段值
	 * @param string $name 字段名称
	 * @return mixed 不存在时返回NULL
	 */
	public function __get($name)
	{	
		if (!is_array($this->_result_array)) return null;
		if (array_key_exists($name, $this->_result_array))
		{
			return $this->_result_array[$name];
		}
		return null;
	}
	
	/**
	 * 获取主键名称
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		return $this->_primary_key;
	}
	
	/**
	 * 获取主键的值
	 * @return mixed
	 */
	public function getPrimaryKeyValue()
	{ 
		if (isset($this->_result_array[$this->_primary_key]))
			return $this->_result_array[$this->_primary_key];
		else
			return false;
	}
	
	/**
	 * 获取实体对应表名称
	 * @return mixed
	 */
	public function getTableName()
	{
		return $this->_table_name;
	}
	
	/**
	 * 获取数据库名称
	 * @return mixed
	 */
	public function getDbName()
	{
		return $this->_db_name;
	}
	
	/**
	 * 利用数组填充对象
	 * @param Array $var
	 * @param bool $primary_key 是否同时过滤主键值? 默认过滤
	 * @return $this
	 */
	public function fillDataFromArray(Array $var, $primary_key = true)
	{
		if (empty($var)) return;
		
		//开始检测字段缓存
		$this->_makeCacheTableFields();
		$this->_filterDataFromCacheField($var, !$primary_key);
		
		foreach ($var as $key => $val)
		{
			if (!isset($this->_result_array[$key]))
				$this->_field_count++;
			$this->_result_array[$key] = $val;
		}
		
		return $this;
	}
	
	/**
	 * 返回对象的资料数组［表字段部份］
	 * @param bool $getPrimary 是否连同主键一起获取？
	 */
	public function toArray($getPrimary = true)
	{
		if (!is_array($this->_result_array))
			return null;
		if ($getPrimary)
			return $this->_result_array;
		else 
		{
			$var = $this->_result_array;
			if (isset($var[$this->_primary_key]))
				unset($var[$this->_primary_key]);
			return $var;
		}
	}
	
	/**
	 * 获取当前实体字段数量
	 * @return int
	 */
	public function getFieldCount()
	{
		return $this->_field_count;
	}
		
	/**
	 * 获取表字段的验证规则列表
	 * @return XF_Db_Table_ValidateRule
	 */
	public function getFieldValidateRule()
	{
		if ($this->_field_validate_rule instanceof XF_Db_Table_ValidateRule)
			return $this->_field_validate_rule;
		return new XF_Db_Table_ValidateRule();
	}
	
	
	/**
	 * 实体是否为空？
	 * @return bool 
	 */
	public function isEmpty()
	{
		//字段等于零时为空.
		if ($this->getFieldCount() == 0)
			return true;
		return false;
	}
	
	/**
	 * 是否存在［已设置］主键
	 * @return bool
	 */
	public function isExistsPrimaryKey()
	{
		if (XF_Functions::isEmpty($this->getPrimaryKey()))
			return true;
		return false;
	}
	
	/**
	 * 获取数据表查询驱动对象
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function getTableSelect()
	{
		if (self::$_select == NULL)
			self::$_select = new XF_Db_Table_Select_Mysql($this);
		else
		{ 
			//防止在 XF_Db_Table_Select_Interface 中无法获取当前Table已有的数据，所以要重新实例化
			$class = get_class(self::$_select);
			self::$_select = new $class($this);
		} 
		return self::$_select;
	}
	
	/**
	 * 设置字段关联表对象
	 * @param string $name 自定义关联名称
	 * @param string $field　当前表字段名称  特殊用法 ":fieldName" 表示字段为一个"数组" ，值类似: “43,654,234,2”  逗号分开
	 * @param string $tableName 需要关联的表对象名称
	 * @param string $associateFieldName 关联对应表的字段
	 * @param bool $autoAssociated 是否自动关联，[默认为false]
	 * @param bool $oneToMany 是否为一对多，如果是一对多，获取出来的资料将是一个数组列表 [默认为false]
	 * @return XF_Db_Table_Abstract
	 */
	public  function setFieldAssociated($name, $field, $tableName, $associateFieldName, $autoAssociated = false, $oneToMany = false)
	{
		if (is_object($tableName))
			$tableName = get_class($tableName);

		$this->_field_associateds[$name] = array(
				'table' => $tableName,
				'field' => $field,
				'associateField' => $associateFieldName,
				'autoAssociated' => $autoAssociated,
				'oneToMany' => $oneToMany
			
		);
		return $this;
	}
	
	
	
	/**
	 * 设置一个关联为自动状态 
	 * @param string $name 自定义关联名称
	 * @param mixed $size 关联数量。默认为20，如果为false将关联所有数据[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @param mixed $where 关联额外的字段查询条件。默认为NULL,可以是字符串或数组。
	 * @param mixed $order 关联的数据的排序设置，默认为NULL，或例如：'id DESC'
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociatedAuto($name, $size = 20, $where = NULL, $order = NULL)
	{
		if (isset($this->_field_associateds[$name]))
		{
			$this->_field_associateds[$name]['autoAssociated'] = true;
			$this->_field_associateds[$name]['size'] = $size;
			$this->_field_associateds[$name]['where'] = $where;
			$this->_field_associateds[$name]['order'] = $order;
		}
		return $this;
	}
	
	/**
	 * 设置一个关联为手动状态 
	 * @param string $name 自定义关联名称
	 * @param mixed $size 关联数量。默认为20，如果为false将关联所有数据[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @param mixed $where 关联额外的字段查询条件。默认为NULL,可以是字符串或数组。
	 * @param mixed $order 关联的数据的排序设置，默认为NULL，或例如：'id DESC'
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociatedManual($name, $size = false, $where = NULL, $order = NULL)
	{
		if (isset($this->_field_associateds[$name]))
		{
			$this->_field_associateds[$name]['autoAssociated'] = false;
			$this->_field_associateds[$name]['size'] = $size;
			$this->_field_associateds[$name]['where'] = $where;
			$this->_field_associateds[$name]['order'] = $order;
		}
		return $this;
	}
	
	
	/**
	 * 获取字段关联配置列表
	 * @return mixed
	 */
	public function getFieldAssociated()
	{
		return $this->_field_associateds;
	}
	
	/**
	 * 关联完所有资料后执行
	 */
	public function initFromAssociateAfter()
	{}
	
	/**
	 * 第一次包装成对象后执行(关联资料前)
	 */
	public function init()
	{}
	
	/**
	 * 查询关联的数据
	 * @param string $name　自定义关联名称
	 * @param mixed $size 关联数量。默认为20，如果为false将关联所有数据[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @param mixed $where 关联额外的字段查询条件。默认为NULL,可以是字符串或数组。
	 * @param mixed $order 关联的数据的排序设置，默认为NULL，或例如：'id DESC'
	 * @return mixed
	 */
	public function selectAssociated($name, $size = 20,$where = null, $order = null)
	{
		if (empty($this->_field_associateds[$name]))
			return false;
		
		///////////兼容额外的设置 /////////////////
		if ($where == null && isset($this->_field_associateds[$name]['where']))
		{
			$where = $this->_field_associateds[$name]['where'];
			//还原设置
			unset($this->_field_associateds[$name]['where']);
		}
			
			
		if ($order == null && isset($this->_field_associateds[$name]['order']))
		{
			$order = $this->_field_associateds[$name]['order'];
			//还原设置
			unset($this->_field_associateds[$name]['order']);
		}
			
			
		if (isset($this->_field_associateds[$name]['size']))
		{
			$size = $this->_field_associateds[$name]['size'];
			//还原设置
			unset($this->_field_associateds[$name]['size']);
		}

		$tableName = $this->_field_associateds[$name]['table'];
	 
	   	$tableNameTmp = explode(':', $this->_field_associateds[$name]['table']);
	   	//实例化关联表时，检测是否同时传递参数 2012-10-16
	   	$newParams = null;
	   	if (count($tableNameTmp)>1)
	   	{ 
	   		$tableName = $tableNameTmp[0];
	   		//使其支持传递特殊的参数
	   		$assTmp = array('$true' => true, '$false' => false);
	   		for ($i=1; $i<count($tableNameTmp); $i++)
	   		{
	   			$trueValue = $tableNameTmp[$i];
	   			if (isset($assTmp[strtolower($trueValue)]))
	   			{
	   				$trueValue = $assTmp[strtolower($trueValue)];
	   			}
	   			$newParams[] = $trueValue;
	   		}
	   	}
	   	$table = null;
	   	if ($newParams == null)
	   		$table = new $tableName();
	   	else
	   	{
	   		$table = new ReflectionClass($tableName);
	   		$table = $table->newInstanceArgs($newParams);
	   	}

	   	//2013-1-9 实现一条关联可以设置多个字段，例如同时关联省份和城市，我们一般省份和城市记录都是在一个表里，结构都一样
	   	$fieldValues = array();
	   	$fieldNames = explode(',', $this->_field_associateds[$name]['field']);
	   	foreach ($fieldNames as $fieldName)
	   	{
	   		if (strpos($fieldName, ':') ===0)
				$fieldName = substr($fieldName, 1);
				
			$value = $this->$fieldName;
			if (XF_Functions::isEmpty($value))
				continue;
				
			$value = explode(',', $value);
			foreach ($value as $v)
			{
				$fieldValues[$v] = $v;
			}
	   	}	
	   	$fieldValues = array_values($fieldValues);
	    $select = $table->getTableSelect()->setWhereIn($this->_field_associateds[$name]['associateField'], $fieldValues);
		$select->appendWhere($where)->setOrder($order);
		//如果是一对多的查询
		if ($this->_field_associateds[$name]['oneToMany'] === true)
		{
			return $select->setLimit($size)->fetchAll();
		}
		elseif (count($fieldNames) > 1) 
		{
			return $select->setLimit(count($fieldNames))->fetchAll();
		}
	   	
		return $select->fetchRow();
	}
	
	
	/**
	 * 表数据校验
	 * @return bool 
	 * @throws XF_Db_Table_Exception
	 */
	public function validate()
	{
		return $this->_validateAllData(true);
	}
	
	/**
	 * 插入当前对象到数据库记录
	 * @param bool $validate 是否验证? 默认为true
	 * @return mixed
	 */
	public function insert($validate = true)
	{
		
		$this->getFormData($this->toArray(), false);
		$this->_validateAllData($validate);
		return $this->getTableSelect()->insert();
	}
	
	/**
	 * 更新当前对象到数据记录
	 * @param bool $validate 是否验证? 默认为true
	 * @return mixed
	 */
	public function update($validate = true)
	{	
		$this->getFormData($this->toArray(), true);
		$this->_validateAllData($validate);
		return $this->getTableSelect()->update();
	}
	
	/**
	 * 删除当前对象记录
	 * @param bool
	 */
	public function remove()
	{
		return $this->getTableSelect()->remove();
	}
	
	/**
	 * 进行数据验证
	 * @param bool $validate 是否验证? 默认为true
	 * @throws XF_Db_Table_Exception
	 * @return bool
	 */
	protected function _validateAllData($validate = true)
	{
		if ($validate !== true) return true;
		if ($this->_field_validate_rule == null) return true;
		//验证
		$data = $this->toArray();
		if(XF_Db_Table_Validate::getInstance()->validateData($data, $this->getFieldValidateRule()->toArray(), true) === false)
			throw new XF_Db_Table_Exception(XF_DataPool::getInstance()->get('TableFieldDataValidateError'));
		else
		{
			$this->_set_var_validate = false;
			foreach ($data as $key => $val)
			{
				$this->{$key} = $val;
			}
			$this->_set_var_validate = true;
		} 
		return true;
	}
	
	/**
	 * 获取表单提交的数据
	 * @access public
	 * @param array $data 默认值
	 * @param bool $primary_key 是否同时获取主键值? 默认不获取
	 * @return mixed
	 */
	public function getFormData(Array $data = null, $primary_key = false)
	{
		//获取表单数据
		if(empty($data))
			$data = XF_Controller_Request_Http::getInstance()->getPost();
	 
		$this->_result_array = array();
		$this->fillDataFromArray($data, $primary_key);
		return $this->toArray();
	}

	
	/**
	 * 缓存数据库字段信息
	 * @access	private
	 * @return	void
	 */
	private function _makeCacheTableFields()
	{
		$cache_key = md5($this->_db_name.$this->_table_name);
		$cache = XF_Cache_SECache::getInstance();
		$cache->setCacheSaveFile(TEMP_PATH.'/Cache/DatabaseField');
		$content = $cache->read($cache_key);

		if ($content !== XF_CACHE_EMPTY)
			return true;

		//缓存字段
		$tepArray = $this->getTableSelect()->getFields();
		$cache->add($cache_key, $tepArray);
		return true;
		
	}
	
	/**
     * 根据数据表缓存字段过滤数据
	 * @access	private
	 * @param array $final_data
	 * @param bool $primary_key 是否同时过滤主键值? 默认过滤
	 * @return	void
	 */
	private function _filterDataFromCacheField(&$final_data, $primary_key = true)
	{
		$cache_key = md5($this->_db_name.$this->_table_name);
		$cache = XF_Cache_SECache::getInstance();
		$cache->setCacheSaveFile(TEMP_PATH.'/Cache/DatabaseField');
		$field_keys = $cache->read($cache_key);
		if ($field_keys == XF_CACHE_EMPTY)
			return false;

		//获取表单的KEY
		$form_keys = array_keys($final_data);
		$tep_array = array();
		//分别对每一个表单key 进行分析过滤
		for($i=0; $i<count($form_keys); $i++)
		{ 
			for($j=0; $j<count($field_keys); $j++)
			{
				//如果匹配到表单key
				if($form_keys[$i] === $field_keys[$j])
				{
					//是否过滤主键值
					if($form_keys[$i] == $this->getPrimaryKey())
					{
						if ($primary_key === false)
							$tep_array[$field_keys[$j]] = $final_data[$field_keys[$j]];
					}
					else 
					{
						$tep_array[$field_keys[$j]] = $final_data[$field_keys[$j]];
					}
					$j=count($field_keys);
				}
			}
		}
		$final_data = $tep_array;
	}
}