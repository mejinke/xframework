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
	 * 数据库中的数据备份
	 * @var array
	 */
	private $_db_result_array;
	
	/** 
	 * 记录值更改过的字段
	 * @var array
	 */
	private $_field_change_array;
	
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
	 * 字段关联对象自己的关联设置列表 [2013-03-14新增]
	 * @var array
	 */
	private $_field_associate_object_asssetting = array();

	/**
	 * 当前对象获得数据后需要自动执行的方法列表
	 * @var array
	 */
	private $_init_auto_execute_methods = array();
	
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
				if (strpos($name, '_all_data_count') === false)
				{
					if (isset($this->_field_change_array[$name]))
					{
						$this->_field_change_array[$name]['count']++;
						$this->_field_change_array[$name]['log'][] = $value;	
					}
					else
					{
						$this->_field_change_array[$name]['count'] = 1;
						$this->_field_change_array[$name]['log'][] = $value;
					}
				}

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
	 * @return XF_Db_Table_Abstract
	 */
	public function fillDataFromArray(Array $var)
	{
		if (empty($var)) return;
		
		$tmp = debug_backtrace();
		$fromDBTable = false;
		if ($tmp[1]['class'] == 'XF_Db_Table_Select_Abstract' && $tmp[1]['function'] == '_packing')
			$fromDBTable = true;
 
		foreach ($var as $key => $val)
		{
			if (!isset($this->_result_array[$key]))
				$this->_field_count++;
			if (is_numeric($val))
			{
				$tmp = explode('.', $val);
				if (strlen($tmp[0]) <= 14)
				{
					$val = floatval($val);
				}
			}
			$this->_result_array[$key] = $val;
			
			if ($fromDBTable == true)
				$this->_db_result_array[$key] = $val;
		}
		
		return $this;
	}
	
	/**
	 * 返回对象的资料数组［表字段部份］
	 * @param bool $get_primary 是否连同主键一起获取？默认为true
	 * @return null | array
	 */
	public function toArray($get_primary = true)
	{
		if (!is_array($this->_result_array))
			return null;
		if ($get_primary)
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
	 * 返回与数据库一致的字段值
	 * @param bool $get_primary 是否连同主键一起获取？默认为true
	 * @return null | array
	 */
	public function getDBResultArray($get_primary = true)
	{
		if (!is_array($this->_db_result_array))
			return null;
		if ($get_primary)
			return $this->_db_result_array;
		else 
		{
			$var = $this->_db_result_array;
			if (isset($var[$this->_primary_key]))
				unset($var[$this->_primary_key]);
			return $var;
		}
	}
	
	/**
	 * 获取更改过的字段
	 * @return null | array
	 */
	public function getChangedFields()
	{
		return $this->_field_change_array;
	}
	
	
	/**
	 * 指定的字段是否被更改过
	 * @param string $field_name 字段名
	 * @return null | array
	 */
	public function hasChanged($field_name)
	{
		return isset($this->_field_change_array[$field_name]) ? $this->_field_change_array[$field_name] : false;
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
	 * 获取当前对象获得数据后需要执行的方法列表
	 * @return array
	 */
	public function getInitAutoExecuteMethods()
	{
		return $this->_init_auto_execute_methods;
	}
	
	/**
	 * 获取当前对象指定关联对象需要执行的方法
	 * @param string $associateName 当前对象的关联名称
	 * @return false | array
	 */
	public function getInitAutoExecuteMethodFromAssociate($associateName)
	{
		$methods = false;
		if (is_array($this->_init_auto_execute_methods))
		{
			foreach ($this->_init_auto_execute_methods as $methodName => $m)
			{
				$tmp = explode('$', $methodName);
				if (count($tmp) == 2 && $tmp[1] == $associateName)
					$methods[] = $m;
			}
		}
		return $methods;
	}
	
	
	/**
	 * 添加当前对象获得数据后需要执行的方法[$associateName后面可以添加需要传递给自定义init方法的参数]
	 * @param string $methodName 当前对象的方法名，以init开始；例：initData 传值应该为:Data ;属性必须是public
	 * @param string $associateName 是否设置为关联对象的执行方法，默认为null为当前表对象
	 * @throws XF_Exception
	 * @return XF_Db_Table_Abstract
	 */
	public function addInitAutoExecuteMethod($methodName, $associateName = null)
	{	
		if (strpos($methodName, 'init') ===0)
			$methodName = substr($methodName, 4);
		if ($associateName === null && !method_exists($this, 'init'.$methodName))
			throw new XF_Db_Table_Exception('自动执行方法(init'.$methodName.') 不存在');
		if ($methodName == 'FromAssociateAfter')
			throw new XF_Db_Table_Exception('无法添加 FromAssociateAfter 方法');

		$trueMethodName = 'init'.$methodName;
		//用于区分是否要执行当前对象
		if ($associateName !== null )
			$methodName .= '$'.$associateName;

		$args = func_get_args();
		if (count($args) > 2)
		{
			unset($args[0]);
			unset($args[1]);
			$args = array_values($args);
		}
		else 
			$args = NULL;	
		$this->_init_auto_execute_methods['init'.$methodName] = array(
			'method' => $trueMethodName,
			'params' => $args
		);
		return $this;
	}
	
	/**
	 * 删除当前对象获得数据后需要执行的方法
	 * @param string $methodName 当前对象的方法名，以init开始；例：initData 传值应该为:Data ;属性必须是public
	 * @param string $associateName 是否设置为关联对象的执行方法，默认为null为当前表对象
	 * @throws XF_Exception
	 * @return XF_Db_Table_Abstract
	 */
	public function removeInitAutoExecuteMethod($methodName, $associateName = null)
	{
		//用于区分是否要执行当前对象
		if ($associateName !== null )
			$methodName .= '$'.$associateName;
		if (in_array('init'.$methodName, $this->_init_auto_execute_methods))
			unset($this->_init_auto_execute_methods['init'.$methodName]);
		return $this;
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
	protected function _setFieldAssociated($name, $field, $tableName, $associateFieldName, $autoAssociated = false, $oneToMany = false)
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
	 * 设置当前类字段关联对象自己的关联状态
	 * @param string $name 自定义关联名称
	 * @param string $objectAssName 关联对象的自这义关联名称
	 * @param bool $isAuto 是否设置为自动，默认为 true
	 * @param mixed $size 关联数量。默认为20，如果为false将关联所有数据[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @param mixed $where 关联额外的字段查询条件。默认为NULL,可以是字符串或数组。
	 * @param mixed $order 关联的数据的排序设置，默认为NULL，或例如：'id DESC'
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociateObjectAss($name, $objectAssName, $isAuto = true, $size = 20, $where = NULL, $order = NULL)
	{
		$this->_field_associate_object_asssetting[$name][$objectAssName] = array(
			'assObjectName' => $this->_field_associateds[$name]['table'],
			'autoAssociated' => $isAuto,
			'size' => $size,
			'where' => $where,
			'order' => $order
		);
		return $this;
	}
	
	/**
	 * 设置当前类字段关联对象自己的关联状态【无法修改查询数量、条件、排序】
	 * @param string $name 自定义关联名称
	 * @param string $objectAssName 关联对象的自这义关联名称
	 * @param bool $isAuto 是否设置为自动，默认为 true
	 * @param mixed $size 关联数量。[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociateObjectAssOnly($name, $objectAssName, $isAuto = true, $size = NULL)
	{
		$this->_field_associate_object_asssetting[$name][$objectAssName] = array(
			'assObjectName' => $this->_field_associateds[$name]['table'],
			'autoAssociated' => $isAuto
		);
		
		if (is_numeric($size))
		{
			$this->_field_associate_object_asssetting[$name][$objectAssName]['size'] = $size;
		}
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
	 * 设置一个关联为自动状态 【无法修改查询数量、条件、排序】
	 * @param string $name 自定义关联名称
	 * @param mixed $size 关联数量。[该设置仅在关联类型(oneToMany)为一对多时有效]
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociatedAutoOnly($name, $size = NULL)
	{
		if (isset($this->_field_associateds[$name]))
		{
			$this->_field_associateds[$name]['autoAssociated'] = true;
			if (is_numeric($size))
			{
				$this->_field_associateds[$name]['size'] = $size;
			}
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
	 * 设置一个关联为手动状态 【无法修改查询数量、条件、排序】
	 * @access public
	 * @param string $name 自定义关联名称
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociatedManualOnly($name)
	{
		if (isset($this->_field_associateds[$name]))
		{
			$this->_field_associateds[$name]['autoAssociated'] = false;
		}
		return $this;
	}
	
	/**
	 * 设置所有的关联为手动
	 * @access public
	 * @return XF_Db_Table_Abstract
	 */
	public function setAssociatedAllManual()
	{
		if (is_array($this->_field_associateds))
		{
			foreach ($this->_field_associateds as $name => $val)
			{
				$this->_field_associateds[$name]['autoAssociated'] = false;
			}
		}
		return $this;
	}
	
	/**
	 * 获取字段关联配置列表
	 * @access public
	 * @return array
	 */
	public function getFieldAssociated()
	{
		return $this->_field_associateds;
	}
	
	/**
	 * 获取当前已开启自动关联的配置列表
	 * @access public
	 * @return array 【可能是一个空数组】
	 */
	public function getFieldAutoAssociated()
	{
		if (!is_array($this->_field_associateds)) return array();
		$tmp = array();
		foreach ($this->_field_associateds as $key => $val) 
		{
			if ($val['autoAssociated'] == true)
			{
				$tmp[$key] = $val;
			}
		}
		
		return $tmp;
	}
	
	/**
	 * 获取字段关联对象自己的关联设置列表
	 * @access public
	 * @return array
	 */
	public function getFieldAssociateObjectAssSetting()
	{
		return $this->_field_associate_object_asssetting;
	}
	
	/**
	 * 关联完所有资料后执行
	 */
	public function initAssociatedAfter()
	{}
	
	/**
	 * 第一次包装成对象后执行(关联资料前)
	 */
	public function init()
	{}
	
	/**
	 * 查询关联的数据
	 * @access public
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
	   	$table = new $tableName();
	   
		/////检测是否需要设置当前关联对象自己的关联状态
	   	//if (is_array($this->_field_associateds) && count($this->_field_associateds) > 0)
	   	//{
	   		foreach ($this->_field_associateds as $_k => $_val)
	   		{
	   			if (isset($this->_field_associate_object_asssetting[$_k]))
	   			{
	   				$matchAssting = $this->_field_associate_object_asssetting[$_k];
	   				foreach ($matchAssting as $mk => $mval)
	   				{
	   					if (get_class($table) == $mval['assObjectName'])
	   					{
	   						if ($mval['autoAssociated'] == true)
	   						{
	   							if (!empty($mval['where']))
	   							{
	   								$table->setAssociatedAuto($mk, $mval['size'], $mval['where'], $mval['order']);
	   							}
	   							else
	   							{
	   								$table->setAssociatedAutoOnly($mk, $mval['size']);
	   							}
	   						}
	   						elseif (!empty($mval['where']))
	   						{
	   							$table->setAssociatedManual($mk, $mval['size'], $mval['where'], $mval['order']);
	   						}
	   						else
	   						{
	   							$table->setAssociatedManualOnly($mk, $mval['size']);
	   						}
	   					}
	   				}
	   			}
	   		}	
	   	//}

	   	/////2013-4-9 是否设置了当前关联对象自动执行方法
	   	$methods = $this->getInitAutoExecuteMethodFromAssociate($name);
	   	if (is_array($methods))
	   	{
	   		foreach ($methods as $m)
	   		{
	   			if ($m['params'] !== NULL)
   				{
   					array_unshift($m['params'], $m['method'], NULL);
   					call_user_func_array(array($table, 'addInitAutoExecuteMethod'), $m['params']);
   				}
   				else
   				{
   					$table->addInitAutoExecuteMethod($m['method']);
   				}
	   		}
	   	}

	   	//2013-1-9 实现一条关联可以设置多个字段，例如同时关联省份和城市，我们一般省份和城市记录都是在一个表里，结构都一样
	   	$fieldValues = null;
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
				if ($v !='' || $v == '0')
					$fieldValues[$v] = $v;
			}
	   	}
	   	if (!is_array($fieldValues))	
	   		return false;
	   	$fieldValues = array_values($fieldValues);
	    $select = $table->getTableSelect()->setWhereIn($this->_field_associateds[$name]['associateField'], $fieldValues);
		$select->appendWhere($where)->setOrder($order);
		//如果是一对多的查询
		if ($this->_field_associateds[$name]['oneToMany'] === true)
		{
			return $select->setLimit(false)->fetchAll();
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
	 * @param bool $is_insert 是否为插入前的验证？默认为 true
	 * @throws XF_Db_Table_Exception
	 */
	public function validate($is_insert = TRUE)
	{
		return $this->_validateAllData(true, $is_insert);
	}
	
	/**
	 * 插入当前对象到数据库记录
	 * @param bool $validate 是否验证? 默认为true
	 * @param bool $allowPrimaryKey 是否允许插入主键值？默认为false
	 * @return mixed
	 */
	public function insert($validate = true, $allowPrimaryKey = false)
	{
		$this->getFormData($this->toArray(), true);
		$this->_validateAllData($validate);
		return $this->getTableSelect()->insert($allowPrimaryKey);
	}
	
	/**
	 * 更新当前对象到数据记录
	 * @param bool $validate 是否验证? 默认为true
	 * @return mixed
	 */
	public function update($validate = true)
	{	
		$data = $this->_result_array;
		$this->getFormData($this->toArray(), false);
		$this->_validateAllData($validate, false);
		$status = $this->getTableSelect()->update();
		if ($data != NULL)
			$this->_result_array = $data;
		return $status;
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
	 * @param bool $is_insert 当前验证的数据是否准备插入数据库？只有是插入数据库操作，所有的required强制生效
	 * @throws XF_Db_Table_Exception
	 * @return bool
	 */
	protected function _validateAllData($validate = true, $is_insert = true)
	{
		if ($validate !== true) return true;
		if ($this->_field_validate_rule == null) return true;
		//验证
		$data = $this->toArray();
		if ($is_insert == false)
		{
			//只验证发生改变的字段
			$dbResultArray = $this->getDBResultArray();
			if ($dbResultArray != null && is_array($data))
			{
				foreach ($dbResultArray as $key => $val)
				{
					if (array_key_exists($key, $data) && $data[$key] === $val)
						unset($data[$key]);
				}
			}
		}

		if(XF_Db_Table_Validate::getInstance()->validateData($data, $this->getFieldValidateRule()->toArray(), true, $this, $is_insert) === false)
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
	 * @param bool $filter_primary_key 是否过滤主键值? 默认true
	 * @return mixed
	 */
	public function getFormData(Array $data = null, $filter_primary_key = true)
	{
		//获取表单数据
		if(empty($data))
			$data = XF_Controller_Request_Http::getInstance()->getPost();
	 	
		//开始检测字段缓存
		$this->_makeCacheTableFields();
		//过滤表单提交的数据
		$this->_filterDataFromCacheField($data, $filter_primary_key);

		$this->_result_array = array();
		$this->fillDataFromArray($data);
		return $this->toArray();
	}

	
	/**
	 * 缓存数据库字段信息
	 * @access	private
	 * @return	array 字段列表
	 */
	private function _makeCacheTableFields()
	{
		$cache_key = md5($this->_db_name.$this->_table_name);
		$cache = XF_Cache_SECache::getInstance();
		if (!is_dir(TEMP_PATH.'/Cache/'))
			XF_File::mkdirs(TEMP_PATH.'/Cache/');
		$cache->setCacheSaveFile(TEMP_PATH.'/Cache/DatabaseField');
		$content = $cache->read($cache_key);
		if ($content !== XF_CACHE_EMPTY)
			return $content;

		//缓存字段
		$tmp = $this->getTableSelect()->getFields();
		$cache->setCacheTime(60*24*30)->add($cache_key, $tmp);
		return $tmp;
		
	}
	
	/**
     * 根据数据表缓存字段过滤数据
	 * @access	private
	 * @param array $final_data
	 * @param bool $primary_key 是否过滤主键值? 默认过滤
	 * @return	void
	 */
	private function _filterDataFromCacheField(&$final_data, $filter_primary_key = true)
	{
		$field_keys = $this->_makeCacheTableFields();

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
						if ($filter_primary_key === false)
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