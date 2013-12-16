<?php
if (!defined('XF_CACHE_EMPTY')) define('XF_CACHE_EMPTY','__EMPTY_CACHE__');
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-09-24
 * -+-----------------------------------
 *
 * @desc 数据表Select抽象类
 * @author jingke
 */
abstract class XF_Db_Table_Select_Abstract implements XF_Db_Table_Select_Interface
{
	
	/**
	 * 主数据库配置资料
	 * @var XF_Db_Config
	 */
	protected $_db_config = null;
	
	/**
	 * 从数据库配置资料
	 * @var XF_Db_Config
	 */
	protected $_slave_db_config = null;
	
	/**
	 * 数据库名称
	 * @var string
	 */
	protected $_db_name = null;
	
	/** 
	 * 表对象.
	 * @var XF_Db_Table_Abstract
	 */
	protected $_db_table = null;

	/**
	 * 是否自动加载 init方法
	 * @var bool
	 */
	protected $_auto_call_init= true;
	
	/**
	 * 是否开启主从库模式 默认为 false
	 * @var bool
	 */
	protected $_open_slave = false;
	
	/**
	 * 从服务器数量
	 * @var int
	 */
	protected $_slave_db_count = 0;
	
	/**
	 * 数据库连接
	 * @var XF_Db_Drive_Abstract 
	 */
	protected $_db_drive_connect = null;	
	
	/**
	 * 数据缓存时间
	 * @var int
	 */
	protected $_data_cache_time = 0;
	
	/**
	 * 数据缓存对象
	 * @var XF_Cache_Interface
	 */
	protected $_cache_class = null;

	/**
	 * 需要查询的列
	 * @var mixed
	 */
	protected $_adv_find = null;

	/**
	 * 查询条件
	 * @var mixed
	 */
	protected $_adv_where = null;

	/**
	 * in 语句条件
	 * @var mixed
	 */
	protected $_adv_in = null;

	/**
	 * not in 语句条件
	 * @var mixed
	 */
	protected $_adv_not_in = null;

	/**
	 * 排序方式
	 * @var mixed
	 */
	protected $_adv_order = null;

	/**
	 * 获取指定长度的资料  例 0,100
	 * @var mixed
	 */
	protected $_adv_limit = null;
	
	/**
	 * 执行相关数据库查询后是否重置各项查询属性 [默认true]
	 * @var bool 
	 */
	public $clear_property = true;
	
	/**
	 * 是否显示查询语句
	 * @var bool
	 */
	protected $_show_query = false;
	
	/**
	 * 设置缓存方式
	 * @access public
	 * @param XF_Cache_Interface $cache 缓存对象
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function setCacheClass(XF_Cache_Interface $cache)
	{
		$this->_cache_class = $cache;
		return $this;
	}
	
	
	/**
	 * 是否自动加载 init
	 * @access public
	 * @param bool $status
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function setInitAutoLoad($status = true)
	{
		$this->_auto_call_init = $status;
		return $this;
	}
	
	/**
	 * 获取总数
	 * @access public
	 * @return int
	 */
	public function fetchCount()
	{
		$this->_allotDatabaseServer();
		$result = $this->_getDataCache($this->_getDataCacheFileName('COUNT'));
		if ($result != XF_CACHE_EMPTY) return $result;
		$this->_db_drive_connect->showQuery($this->_show_query);
		$tep = $this->_db_drive_connect->count($this->_db_table->getTableName(), 
										$this->_adv_find, 
										$this->_adv_where, 
										$this->_adv_order, 
										FALSE, 
										FALSE);
		$dataCount =  isset($tep[0]['count']) ? $tep[0]['count'] : 0;
		$this->_setDataCache($dataCount, $this->_getDataCacheFileName('COUNT'));
		return $dataCount;
	}
	
	/**
	 * 查询所有
	 * @param mixed $options 分页参数
	 * @param bool $emptyObject 当没有查询到任何资料时，是否返回空对象？默认为FALSE
	 * @return XF_Db_Table_Abstract | XF_Db_Table_Abstract Array | NULL
	 */
	public function fetchAll($options = array(), $emptyObject = FALSE)
	{
		$size = isset($this->_adv_limit[1]) ? $this->_adv_limit[1] : 20;
		$offset = isset($this->_adv_limit[0]) ? $this->_adv_limit[0] : 0;
		
		//如果是自定义查询 该sql主要用于获取数据
		$sql =  isset($options['sql']) ? $options['sql'] : FALSE;
		//如果是自定义查询时，是否需要将查询到的结果尝试包装成相对应的对象
		$sqlPacking = isset($options['sqlPacking']) ? $options['sqlPacking'] : false;
		
		//如果是自定义查询，该sql主要用于统计总记录数
		$pageSql = isset($options['pageSql']) ? $options['pageSql'] : FALSE;
	
		//是否自动分页，true时URL存在分页参数时自动分页数 否则始终为第一页的数据
		$autoPage = isset($options['autoPage']) ? $options['autoPage'] : FALSE;
		
		//是否需要将查到的数据包装相对应的对象
		$packing = isset($options['packing']) ? $options['packing'] : true;
		
		$this->_allotDatabaseServer();
		
		//查询的结果
		$result = false;
		
		//如果等于false 则不分页
		if ($size === false || $offset === false)
		{
			if ($sql == FALSE)
			{
				//是否有缓存 
				$result = $this->_getDataCache();
				if ($result == XF_CACHE_EMPTY)
				{
					$this->_db_drive_connect->showQuery($this->_show_query);
					$result = $this->_db_drive_connect->select($this->_db_table->getTableName(), 
													$this->_adv_find, 
													$this->_adv_where, 
													$this->_adv_order, 
													false, 
													false);
					$this->_setDataCache($result);
				}
				//返回包装后的结果
				if ($packing == true)
					$result = $this->_packing($result, $emptyObject);
				$this->_clearProperty();
				return $result;
			}
			else //定自义SQL查询
			{
				//是否有缓存 
				$result = $this->_getDataCache($sql);
				if ($result == XF_CACHE_EMPTY) 
				{
					$result = $this->_db_drive_connect->execute($sql);
					$this->_setDataCache($result, $sql);
				}
				if ($sqlPacking == true)
					$result = $this->_packing($result, $emptyObject);
				$this->_clearProperty();
				return $result;
			}
		}
		/////正常的设置了$size(start), $offset
		elseif (is_numeric($size) && is_numeric($offset) && $autoPage == false)  
		{
			//是否有缓存 
			$result = $this->_getDataCache();
			if ($result == XF_CACHE_EMPTY)
			{
				$this->_db_drive_connect->showQuery($this->_show_query);
				$result = $this->_db_drive_connect->select($this->_db_table->getTableName(), 
												$this->_adv_find, 
												$this->_adv_where, 
												$this->_adv_order, 
												$size, 
												$offset);
				$this->_setDataCache($result);
			}
			//返回包装后的结果
			if ($packing == true)
				$result = $this->_packing($result, $emptyObject);
			$this->_clearProperty();
			return $result;
		}
		
		
		
		//////////////////// 启用分页 //////////////////////
		if ($autoPage == true)
		{
			//总记录数
			$dataCount = 0 ;
			//实例化Page对象
			$paginator = XF_Db_Table_Select_Paginator::getInstance();
			//当前页码
			$p=1;
		
			if ($sql == FALSE)
				$dataCount = $this->fetchCount();
			elseif ($pageSql != false)
			{
				$result = $this->_db_drive_connect->execute($pageSql, true);
				if (isset($result[0]))
				{
					$key = array_keys($result[0]);
					$dataCount = $result[0][$key[0]];	
				}
				else 
					$dataCount = 0;
				
			}
			else
				throw new XF_Db_Table_Select_Exception('Not found!', 404);

			//URL是否存在页码
			$existsPage = FALSE;
			$paginatorParamValue = XF_Controller_Request_Http::getInstance()->getParam($paginator->getPaginatorParamName(), null);
			if ($paginatorParamValue !== null && $paginatorParamValue != '')
			{
				$existsPage = TRUE;
			}
				
			//获取当前页码
			$paginatorParamValue ? $p = intval($paginatorParamValue) : $p=1;
			if ($autoPage === FALSE) $p=1;

			//是否为正常的页码
			$page_count = $dataCount > 0 ? ceil($dataCount/$size) : 0;
			if (($existsPage && $page_count < $p && $autoPage === TRUE) || $p==0)
				throw new XF_Db_Table_Select_Exception('Not found!', 404);
		
			//当前页数据
			$result = FALSE;
			if ($sql == false)
			{
				//读取缓存
				$this->_adv_limit[1] = ($p-1)*$size;
				$result = $this->_getDataCache();
				if($result == XF_CACHE_EMPTY)
				{ 
					$this->_db_drive_connect->showQuery($this->_show_query);
					$result = $this->_db_drive_connect->select($this->_db_table->getTableName(), 
												$this->_adv_find, 
												$this->_adv_where, 
												$this->_adv_order, 
												$size, 
												($p-1)*$size);
					$this->_setDataCache($result);
				}
			}
			else
			{
				$sql = $sql.' LIMIT '.($p-1)*$offset.','.$size;
				
				//读取缓存 
				$result = $this->_getDataCache($sql);
				if ($result == XF_CACHE_EMPTY)
				{
					//获取当前页结果
					$this->_db_drive_connect->showQuery($this->_show_query);
					$result = $this->_db_drive_connect->execute($sql, true);
					$this->_setDataCache($result, $sql);
				}	
			}
			
			//设置分页器内容
			$paginator->set($size, $dataCount);
			$paginator->run();		
			//返回包装后的结果
			if ($packing == true && $sql == false)
				$result = $this->_packing($result, $emptyObject);
			if ($sql != false && $sqlPacking == true)
				$result = $this->_packing($result, $emptyObject);
			$this->_clearProperty();
			return $result;
		
		}
		else //防止自动分页
		{
			//返回包装后的结果
			if ($packing == true && $sql == false)
				$result = $this->_packing($result, $emptyObject);
			if ($sql != false && $sqlPacking == true)
				$result = $this->_packing($result, $emptyObject);
			$this->_clearProperty();
			return $result;
		}		
	}

	/**
	 * 查询一行
	 * @param bool $emptyObject 当没有查询到任何资料时，是否返回空对象？默认为true
	 * @return XF_Db_Table_Abstract | NULL
	 */
	public function fetchRow($emptyObject = FALSE)
	{
		$this->setLimit(1);
		$result = $this->_getDataCache();
		if($result == XF_CACHE_EMPTY)
		{ 
			$this->_allotDatabaseServer();
			$this->_db_drive_connect->showQuery($this->_show_query);
			$result = $this->_db_drive_connect->select($this->_db_table->getTableName(), 
											$this->_adv_find, 
											$this->_adv_where, 
											$this->_adv_order, 
											$this->_adv_limit[1], 
											$this->_adv_limit[0]);
			$this->_setDataCache($result);
		}
		
		$this->_clearProperty();
		
		//返回包装后的结果
		if (isset($result[0]))
			return $this->_packing($result[0]);
		return $this->_packing($result, $emptyObject);
	}
	
	/**
	 * 根据主键查询一条记录
	 * @param int $primary_key_value
	 * @param bool $emptyObject 当没有查询到任何资料时，是否返回空对象？默认为FALSE
	 * @return XF_Db_Table_Abstract
	 */
	public function findRow($primary_key_value, $emptyObject = false)
	{
		if (!is_scalar($primary_key_value)) return false;
		$this->setLimit(1);
		$this->_allotDatabaseServer();
		$this->setWhere(array($this->_db_table->getPrimaryKey() => $primary_key_value));
		$result = $this->_getDataCache();
		if ($result == XF_CACHE_EMPTY)
		{
			$this->_db_drive_connect->showQuery($this->_show_query);
			$result = $this->_db_drive_connect->select($this->_db_table->getTableName(),
											$this->_adv_find,
											$this->_adv_where,
											$this->_adv_order,
											$this->_adv_limit[1],
											$this->_adv_limit[0]);
			//设置缓存
			$this->_setDataCache($result);
		}
		$this->_clearProperty();
		
		if (isset($result[0]))
			return $this->_packing($result[0]);
		else 
		{
			if ($emptyObject == true)
				return clone $this->_db_table;
			return false;
		}
	}
	
	/**
	 * 是否显示查询语句
	 * @param bool $status
	 * @return XF_Db_Table_Select_Abstract
	 */
	public function showQuery($status = true)
	{
		$this->_show_query = $status;
		return $this;
	}
	
	/**
	 * 获取SQL语句
	 * @return string
	 */
	public function getSql()
	{
		$this->_allotDatabaseServer();
		return $this->_db_drive_connect->getSql($this->_db_table->getTableName(), 
												$this->_adv_find, 
												$this->_adv_where, 
												$this->_adv_order, 
												@$this->_adv_limit[1], 
												@$this->_adv_limit[0]);
	}
	
	/**
	 * 设置数据缓存时间 ［单位：分钟］
	 * @access public
	 * @param int $minutes 时长分钟数
	 * @return Model_Abstract
	 */
	public function setCacheTime($minutes = 0)
	{
		if (is_numeric($minutes) && $minutes != 0)
			$this->_data_cache_time = $minutes;
		return $this;
	}
	
	/**
	 * 获取数据缓存唯一名称标识
	 * @access protected
	 * @param string $appendString 添加的字符串，主要用于区分count(*)查询 
	 * @return string
	 */
	protected function _getDataCacheFileName($appendString = '')
	{
		$code = $this->_db_name.$this->_db_table->getTableName().serialize($this->_adv_find).serialize($this->_adv_where).
				serialize($this->_adv_in).serialize($this->_adv_not_in).serialize($this->_adv_order).
				serialize($this->_adv_limit);
		$path = XF_Controller_Request_Http::getInstance()->getModule().'/'.
				XF_Controller_Request_Http::getInstance()->getController().'/'.
				md5(serialize($this->_adv_where)).'/';
		
		return TEMP_PATH.'/Cache/Data/'.$this->_db_name.'/'.$this->_db_table->getTableName().'/'.$path.md5($code.$appendString).'.php';
		
	}
	
	/**
	 * 获取数据缓存
	 * @access protected
	 * @param array $result
	 */
	protected  function _getDataCache($file = null)
	{
		if ($this->_cache_class instanceof  XF_Cache_Interface)
		{
			if ($file == null)
				$file = $this->_getDataCacheFileName();
			return $this->_cache_class->read($file);
		}
		elseif (XF_DataPool::getInstance()->get(XF_Cache_Abstract::ChunkCacheIdentify) !== false)
		{ 
			$cache = XF_DataPool::getInstance()->get(XF_Cache_Abstract::ChunkCacheIdentify);
			$tmp = explode('$', $cache);
			if (count($tmp) != 2) return XF_CACHE_EMPTY;
			$this->_cache_class = call_user_func(array($tmp[0], 'getInstance'));
			$this->_data_cache_time = intval($tmp[1]);
			if ($file == null) $file = $this->_getDataCacheFileName();
			return $this->_cache_class->read($file);
		}
		return XF_CACHE_EMPTY;
	}
	
	/**
	 * 设置数据缓存
	 * @access protected
	 * @param mixed $data
	 */
	protected function _setDataCache($data, $saveFile = null)
	{
		if ($this->_data_cache_time > 0 )
		{ 
			if (!$this->_cache_class instanceof XF_Cache_Interface)
				throw new XF_Db_Table_Select_Exception('缓存器无法识别', 500);
			if ($saveFile == null)
				$saveFile = $this->_getDataCacheFileName();
			$this->_cache_class->setCacheTime($this->_data_cache_time)->add($saveFile, $data);
		}
		return true;
	}
	
	/**
	 * 实体对象包装
	 * @param mixed $result
	 * @param bool $emptyObject 当没有查询到任何资料时，是否返回空对象？默认为true
	 * @return XF_Db_Table_Abstract || Array
	 */
	protected function _packing($result , $emptyObject = true)
	{
	
		//如果查询资料不为数组，则返回空对象	
		if (!is_array($result)) 
		{
			if ($emptyObject === true)
				return array(clone $this->_db_table);
			return false;
		}
		
		//单一数据表资料
		if (!isset($result[0]))
		{
			$entity = clone $this->_db_table;
			$entity->fillDataFromArray($result, false);
			
			//init
			if (method_exists($entity, 'init') && $this->_auto_call_init)
				$entity->init();
			
			$entity = $this->_autoSelectFieldAssociated($entity);
			//关联完后执行指定的init
			if (method_exists($entity, 'initAssociatedAfter') && $this->_auto_call_init)
				$entity->initAssociatedAfter();
				
			//检测需要自动执行的方法
			$methods = $entity->getInitAutoExecuteMethods();
			if (is_array($methods) && count($methods) > 0 && $this->_auto_call_init)
			{
				foreach ($methods as $methodName => $m)
				{
					if (strpos($methodName, '$') === false)
					{
						$ref = new ReflectionMethod(get_class($entity), $m['method']);
						$paramCount = $ref->getNumberOfParameters();
							
						if ($m['params'] !== NULL)
						{
							if (count($m['params']) < $paramCount)
							{
								while (count($m['params']) < $paramCount)
								{
									$m['params'][] = NULL;
								}
							}
							call_user_func_array(array($entity, $m['method']), $m['params']);
						}
						else
						{
							if ($paramCount == 0)
								$entity->{$m['method']}();
							else 
							{
								$m['params'] = array();
								while ($paramCount > 0)
								{
									$m['params'][] = NULL;
									$paramCount--;
								}
								call_user_func_array(array($entity, $m['method']), $m['params']);
							}
						} 
					}
				}
			}
				
			return $entity;
		}
		else  //多组数据表资料
		{
			$entityList = false;
			foreach ($result as $key => $rs)
			{
				$entity = clone $this->_db_table;
				$entity->fillDataFromArray($rs, false);
				//init
				if (method_exists($entity, 'init') && $this->_auto_call_init)
					$entity->init();
					
				
				$entityList[] = $entity;
			}
			
			$entityList =  $this->_autoSelectFieldAssociated($entityList);
			$methods = $entity->getInitAutoExecuteMethods();
			//关联完后执行指定的init
			foreach ($entityList as $entity)
			{
				if (method_exists($entity, 'initAssociatedAfter') && $this->_auto_call_init)
					$entity->initAssociatedAfter();
					
				//检测需要自动执行的方法
				if (is_array($methods) && count($methods) > 0 && $this->_auto_call_init)
				{
					foreach ($methods as $methodName => $m)
					{
						if (strpos($methodName, '$') === false)
						{
							$ref = new ReflectionMethod(get_class($entity), $m['method']);
							$paramCount = $ref->getNumberOfParameters();
								
							if ($m['params'] !== NULL)
							{
								if (count($m['params']) < $paramCount)
								{
									while (count($m['params']) < $paramCount)
									{
										$m['params'][] = NULL;
									}
								}
								call_user_func_array(array($entity, $m['method']), $m['params']);
							}
							else
							{
								if ($paramCount == 0)
									$entity->{$m['method']}();
								else 
								{
									$m['params'] = array();
									while ($paramCount > 0)
									{
										$m['params'][] = NULL;
										$paramCount--;
									}
									call_user_func_array(array($entity, $m['method']), $m['params']);
								}
							} 
						}
					}
				}
			}
			
			return $entityList;
		}
	}
	
	/**
	 * 读取表对象中设置的关联规则
	 * @param XF_Db_Table_Abstract $table
	 * @return array
	 */
	private function _readAutoSelectFieldAssociated(XF_Db_Table_Abstract $table)
	{
		$ass = $table->getFieldAssociated();
		$tep = array();
		foreach ($ass as $key => $val)
		{
			//是否需要自动关联数据
			if ($val['autoAssociated'] === true)
			{
				$tep[] = array($key => $val);
			}
		}
		return $tep;
	}
	
	/**
	 * 自动查询关联的资料
	 * @param mixed 封装好的查询结果
	 * @return mixed
	 */
	private function _autoSelectFieldAssociated($info)
	{

		$tableName = null;
	
		//一条资料
		if ($info instanceof XF_Db_Table_Abstract)
		{
			$tep = $this->_readAutoSelectFieldAssociated($info);
			if (XF_Functions::isEmpty($tep))
					return $info;
			foreach ($tep as $key => $val)
			{
				$assName = array_keys($val);
				$tmp = $info->selectAssociated($assName[0]);
				if (strpos($val[$assName[0]]['field'], ',') !== false)
				{
					$fields = explode(',', $val[$assName[0]]['field']);
					if (is_array($tmp))
					{
						foreach ($tmp as $t)
						{
							foreach ($fields as $f)
							{
								if (strpos($f, ':') ===0)
								{
									$f = str_replace(':', '', $f);
									$fvs = explode(',', $info->$f);
									foreach ($fvs as $_fvs)
									{
										if ($_fvs == $t->{$val[$assName[0]]['associateField']})				
											$info->{$assName[0].'_'.$f.'[]'} = $t;
									}
								}
								else
								{
									if ($info->$f == $t->{$val[$assName[0]]['associateField']})				
										$info->{$assName[0].'_'.$f} = $t;
								}
									
							}
						}
					}
				}
				else
					$info->{$assName[0]} = $tmp;
			}
		}
		elseif (isset($info[0]) && $info[0] instanceof XF_Db_Table_Abstract)
		{
			$fieldValues = array();
			$allSelectFieldAssociate = array();
			//按关联的表名组合将要查询的条件值
			foreach ($info as $k => $v)
			{
				$tep = $this->_readAutoSelectFieldAssociated($v);
				 
				if (XF_Functions::isEmpty($tep))
					return $info;
				$allSelectFieldAssociate[$k] = $tep;
				//分析要查询关联值
				foreach ($tep as $kk => $vv)
				{
					$assName = array_keys($vv);
					//去重复的值
					$values = @$fieldValues[$vv[$assName[0]]['table']][$vv[$assName[0]]['associateField']]['data'];
					$tmpFields = explode(',', $vv[$assName[0]]['field']);
					foreach ($tmpFields as $field)
					{
						//字段第一位字符是否为':'符号，该符号表示当前这个字段值是一个"数组"。例：34,531,98,1,409
						if (strpos($field, ':') ===0)
						{
							$field = str_replace(':', '', $field);
							$infoValues = explode(',', $v->$field);
							foreach ($infoValues as $infoValue)
							{
								if ($infoValue !='' || $infoValue == '0')
									$values[$infoValue] = $infoValue;
							}
						}
						else 
						{
							$infoValue = $v->$field;
							if ($infoValue !='' || $infoValue == '0')
								$values[$infoValue] = $infoValue;
						}
					}
					
					if ($values != null)
					{
						$fieldValues[$vv[$assName[0]]['table']][$vv[$assName[0]]['associateField']]['data'] = $values;
						$fieldValues[$vv[$assName[0]]['table']][$vv[$assName[0]]['associateField']]['_other'] = array(
							'size' => isset($vv[$assName[0]]['size']) ? $vv[$assName[0]]['size'] : null,
							'where' => isset($vv[$assName[0]]['where']) ? $vv[$assName[0]]['where'] : null,
							'order' => isset($vv[$assName[0]]['order']) ? $vv[$assName[0]]['order'] : null,
							'oneToMany' => $vv[$assName[0]]['oneToMany'],
							'autoinit' => $this->_db_table->getInitAutoExecuteMethodFromAssociate($assName[0])
						);
					}
						
				}
			}
		 
			
			$allAssociateInfos = array();
			foreach ($fieldValues as $fk => $fv)
			{
				$table = new $fk();
			   	
				/////检测是否需要设置当前关联对象自己的关联状态
			   	$objectAssting = $this->_db_table->getFieldAssociateObjectAssSetting();
			   	$_ass = $this->_db_table->getFieldAssociated();
				if (is_array($_ass) && count($_ass) > 0)
			   	{
			   		foreach ($_ass as $_k => $_val)
			   		{
			   			if (isset($objectAssting[$_k]))
			   			{
			   				$matchAssting = $objectAssting[$_k];
			   				foreach ($matchAssting as $mk => $mval)
			   				{
			   					if (get_class($table) == $mval['assObjectName'])
			   					{
			   						if ($mval['autoAssociated'] == true)
			   							$table->setAssociatedAuto($mk, $mval['size'], $mval['where'], $mval['order']);
			   						else
			   							$table->setAssociatedManual($mk, $mval['size'], $mval['where'], $mval['order']);
			   					}
			   				}
			   			}
			   		}	
			   	}

			   	foreach ($fv as $k => $v)
			   	{
			   		///设置关联对象的init
			   		if (is_array($v['_other']['autoinit']))
			   		{
			   			foreach ($v['_other']['autoinit'] as $m)
			   			{
			   				if ($m['params'] !== NULL)
			   				{
			   					array_unshift($m['params'], $m['method'], NULL);
			   					call_user_func_array(array($table, 'addInitAutoExecuteMethod'), $m['params']);
			   				}
			   				else
			   					$table->addInitAutoExecuteMethod($m['method']);
			   			}
			   		}

			   		$select = $table->getTableSelect();
			   		$select->setWhere($v['_other']['where'])->setWhereIn($k, array_values($v['data']))->setOrder($v['_other']['order'])->setLimit(false);
			   		$sql = $select->getSql();
			   		//是否为一对一查询 IN条件查询的每个值对应的查询结果可能是多条，所以需要再次分组
			   		if ($v['_other']['oneToMany'] !== true)
			   			$sql = "SELECT * FROM({$sql}) AS XF_NewTable GROUP BY {$k}";
			   			
			   		//主查询是否已缓存 
			   		if ($this->_data_cache_time > 0 && $this->_cache_class instanceof  XF_Cache_Interface)
			   			$select->setCacheClass($this->_cache_class)->setCache($this->_data_cache_time);
			   			
			   		$rs = $select->execute($sql, true);
			   		$allAssociateInfos[$fk.'=>'.$k.''] = $rs;
			   	}
			}
			
			//组合资料
			foreach ($info as $info_k => $info_v)
			{
				//获取当前表对象所有的关联设置
				$associateAllConfig = $info_v->getFieldAssociated();
				foreach ($allAssociateInfos as $assInfo_k => $assInfo_v)
				{
					if (is_array($assInfo_v))
					{
						foreach ($associateAllConfig as $assConfig_k => $assConfig_v)
						{
							
							if ($assConfig_v['table'].'=>'.$assConfig_v['associateField'] == $assInfo_k)
							{
								//字段列表
								$fields = explode(',', $assConfig_v['field']);
								foreach ($assInfo_v as $assInfo_vv)
								{
									foreach ($fields as $field)
									{
										//该字段值是否为一个"数组"。例：34,531,98,1,409
										if (strpos($field, ':') === 0)
										{
											$field = substr($field, 1);
											$fieldValues = explode(',', $info_v->$field);
											foreach ($fieldValues as $fieldValue)
											{
												if ($fieldValue == $assInfo_vv->{$assConfig_v['associateField']})
												{
													
													if (count($fields) >1)
													{
														if ($assConfig_v['oneToMany'] == true)
														{
															//一对多时，记录总数
															$info[$info_k]->{$assConfig_k.'_all_data_count'}+=1;
															$_count = is_array($info[$info_k]->{$assConfig_k.'_'.$field}) ? count($info[$info_k]->{$assConfig_k.'_'.$field}) : 0;
															if ($_count < $assConfig_v['size'])
																$info[$info_k]->{$assConfig_k.'_'.$field.'[]'} = $assInfo_vv;
														}
														else
															$info[$info_k]->{$assConfig_k.'_'.$field} = $assInfo_vv;
													}
													else
													{
														if ($assConfig_v['oneToMany'] == true)
														{
															//一对多时，记录总数
															$info[$info_k]->{$assConfig_k.'_all_data_count'}+=1;
															$_count = is_array($info[$info_k]->{$assConfig_k}) ? count($info[$info_k]->{$assConfig_k}) : 0;
															if ($_count < $assConfig_v['size'])
																$info[$info_k]->{$assConfig_k.'[]'} = $assInfo_vv;
														}
														else
															$info[$info_k]->{$assConfig_k} = $assInfo_vv;
													}  
												}
											}
										}
										elseif ($info_v->$field == $assInfo_vv->{$assConfig_v['associateField']})
										{
											if (count($fields) >1)
											{
												if ($assConfig_v['oneToMany'] == true)
												{
													//一对多时，记录总数
													$info[$info_k]->{$assConfig_k.'_all_data_count'}+=1;
													$_count = is_array($info[$info_k]->{$assConfig_k.'_'.$field}) ? count($info[$info_k]->{$assConfig_k.'_'.$field}) : 0;
													if ($_count < $assConfig_v['size'])
														$info[$info_k]->{$assConfig_k.'_'.$field.'[]'} = $assInfo_vv;
												}
												else
													$info[$info_k]->{$assConfig_k.'_'.$field} = $assInfo_vv;
											}
											else
											{
												if ($assConfig_v['oneToMany'] == true)
												{
													//一对多时，记录总数
													$info[$info_k]->{$assConfig_k.'_all_data_count'}+=1;
													$_count = is_array($info[$info_k]->{$assConfig_k}) ? count($info[$info_k]->{$assConfig_k}) : 0;
													if ($_count < $assConfig_v['size'])
														$info[$info_k]->{$assConfig_k.'[]'} = $assInfo_vv;
												}
												else
													$info[$info_k]->{$assConfig_k} = $assInfo_vv;
											}  
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $info;
	}
	
	
	/**
	 * 添加新对象/记录
	 * @access public
	 * @param bool $allowPrimaryKey 是否允许插入主键值？默认为false
	 * @return mixed
	 */
	public function insert($allowPrimaryKey = FALSE)
	{	
		if ($this->_db_table->toArray() === null) return false;
		$this->_allotDatabaseServer('INSERT');
		return $this->_db_drive_connect->insert($this->_db_table->getTableName(), $this->_db_table->toArray($allowPrimaryKey));
	}
	
	/**
	 * 更新记录
	 * @access public
	 * @param array $data 资料数组
	 * @return mixed
	 */
	public function update(Array $data = null)
	{
		if ($this->_db_table->toArray() === null && $data == null) return false;
		$this->_allotDatabaseServer('UPDATE');
		if ($this->_adv_where == null)
		{
			$this->setWhere(array($this->_db_table->getPrimaryKey() => $this->_db_table->getPrimaryKeyValue()));
		}

		if ($data == null)
			$data = $tableData = $this->_db_table->toArray(false);

		//动态更新字段[只更新发生改变的字段]
		$dbResultArray = $this->_db_table->getDBResultArray(false);
		if ($dbResultArray != null && is_array($data))
		{
			foreach ($dbResultArray as $key => $val)
			{
				if (array_key_exists($key, $data) && $data[$key] === $val)
					unset($data[$key]);
			}
		}
		
		if ($data == null) return true;
		///防止直接调用此方法所造成的字段错误
		$tmp = debug_backtrace();
		if (!isset($tmp[0]['file']) || strpos(str_replace('\\', '/', $tmp[0]['file']), 'Db/Table/Abstract.php') == false)
			$data = $this->_db_table->getFormData($data);

		$this->_db_drive_connect->showQuery($this->_show_query);
		$result = $this->_db_drive_connect->update($this->_db_table->getTableName(), $data, $this->_adv_where);
		$this->_clearProperty();
		return $result;
	}
	
	
	/**
	 * 分析查询条件
	 * @access private
	 * @return void
	 */
	private function _checkWhere()
	{
		if ($this->_adv_where == null)
		{
			if ($this->_db_table->isExistsPrimaryKey() != TRUE && $this->_db_table->getPrimaryKeyValue() != false)
				$this->setWhere(array($this->_db_table->getPrimaryKey() => $this->_db_table->getPrimaryKeyValue()));
		}
	}
	
	/**
	 * 删除记录[如果没有设置条件，将删除当前对象]
	 * @access public
	 * @return mixed
	 */
	public function remove()
	{	
		$this->_checkWhere();
		if ($this->_adv_where == null)
			throw new XF_Db_Table_Select_Exception('缺少删除条件', 500);
			
		$this->_allotDatabaseServer('REMOVE');
		$this->_db_drive_connect->showQuery($this->_show_query);
		$result = $this->_db_drive_connect->remove($this->_db_table->getTableName(), $this->_adv_where);
		$this->_clearProperty();
		return $result;
	}
	
	/**
	 * 使字段的值<b>增加</b>指定的数值［值一般是数字，默认为　1］
	 * @access public
	 * @param string $fieldName 字段名称 可以是一个数组，例：array('pcount' => 1, 'acount' => 2)
	 * @param int $value　增加的值
	 * @return mixed
	 */
	public function fieldAutoAdd($fieldName, $value = 1)
	{
		$this->_checkWhere();
		
		$this->_allotDatabaseServer('UPDATE');
		$this->_db_drive_connect->showQuery($this->_show_query);
		$sql = '';
		if (is_array($fieldName))
		{
			foreach ($fieldName as $key => $val)
			{
				if ($sql != '') $sql .= ',';
				$sql .= '`'.$key.'`=`'.$key.'`-'.XF_Db_Tool::escape($val);
			}
			$sql = 'UPDATE `'.$this->_db_name.'`.`'.$this->_db_table->getTableName().'` SET '.$sql.' '.$this->_adv_where;
		}
		else 
			$sql = 'UPDATE `'.$this->_db_name.'`.`'.$this->_db_table->getTableName().'` SET `'.$fieldName.'`=`'.$fieldName.'`+'.XF_Db_Tool::escape($value).' '.$this->_adv_where;
		$result = $this->_db_drive_connect->execute($sql);
		$this->_clearProperty();
		return $result;
	}
	
	/**
	 * 使字段的值<b>减少</b>指定的数值［值一般是数字，默认为　1］
	 * @access public
	 * @param string $fieldName 字段名称 可以是一个数组，例：array('pcount' => 1, 'acount' => 2)
	 * @param int $value　减少的值
	 * @return mixed
	 */
	public function filedAutoLessen($fieldName, $value = 1)
	{
		$this->_checkWhere();
		
		$this->_allotDatabaseServer('UPDATE');
		$this->_db_drive_connect->showQuery($this->_show_query);
		
		$sql = '';
		if (is_array($fieldName))
		{
			foreach ($fieldName as $key => $val)
			{
				if ($sql != '') $sql .= ',';
				$sql .= '`'.$key.'`=`'.$key.'`-'.XF_Db_Tool::escape($val);
			}
			$sql = 'UPDATE `'.$this->_db_name.'`.`'.$this->_db_table->getTableName().'` SET '.$sql.' '.$this->_adv_where;
		}
		else 
			$sql = 'UPDATE `'.$this->_db_name.'`.`'.$this->_db_table->getTableName().'` SET `'.$fieldName.'`=`'.$fieldName.'`-'.XF_Db_Tool::escape($value).' '.$this->_adv_where;
		$result = $this->_db_drive_connect->execute($sql);
		$this->_clearProperty();
		return $result;
	}
	
	/**
	 * 获取数据库服务器配置资料
	 * @access protected
	 * @return void
	 */
	protected function _selectDatabaseServer()
	{
		$config = XF_Config::getInstance();
		$this->_open_slave = $config->getDBOpenSlave();

		//如果开启主从模式
		if ($this->_open_slave)
		{
			$dbs = $config->getDBServerSlaves();
			
			//随机获取一个从数据库连接
			$this->_slave_db_count = count($dbs);
			$slave =$dbs[rand(0, $this->_slave_db_count-1)];

			$this->_slave_db_config =  new XF_Db_Config();
			$this->_slave_db_config->setHost($slave['host'])
							->setHostPort($slave['port'])
							->setAccount($slave['user'])
							->setPassword($slave['pass'])
							->setChar('utf8');
		}
		//设置主数据库（可写）
		$dbs = $config->getDBServerMasters();
		if (is_array($dbs))
		{
			$master =$dbs[rand(0, count($dbs)-1)];		
			$this->_db_config =  new XF_Db_Config();
			$this->_db_config->setHost($master['host'])
							->setHostPort($master['port'])
							->setAccount($master['user'])
							->setPassword($master['pass'])
							->setChar('utf8');
		}
	}
	
	/**
	 * 开启主从数据库同步时，不同的操作类型将分配不同的服务器进行处理
	 * @access private
	 * @param string $type 数据库操作类型  SELECT | ADD |UPDATE | REMOVE
	 * @return void
	 */
	protected function _allotDatabaseServer($type = 'SELECT')
	{
		if ($this->_db_drive_connect == NULL)
		{
			$this->_selectDatabaseServer();
			$this->_db_drive_connect = XF_Db_Drive_Mysql::getInstance();
			$this->_db_drive_connect->setDatabaseName($this->_db_table->getDbName());
		}
			
		if ($this->_open_slave && $type == 'SELECT')
		{ 
			if(rand(0, 1) == 0 && $this->_slave_db_count ==1 )
				$this->_db_drive_connect->setDatabaseConnectionConfigInfo($this->_db_config);
			else 
				$this->_db_drive_connect->setDatabaseConnectionConfigInfo($this->_slave_db_config);
		}
		else 
			$this->_db_drive_connect->setDatabaseConnectionConfigInfo($this->_db_config);
	}
	
	/**
	 * 获取表字段
	 * @return mixed
	 */
	public function getFields()
	{
		$this->_allotDatabaseServer();
		return $this->_db_drive_connect->getFields($this->_db_table->getTableName());
	}
	
	/**
	 * 执行查询后，重置相关属性
	 * @access protected
	 * @return Model_Abstract
	 */
	protected function _clearProperty()
	{
		//重置SQL属性
		if ($this->clear_property === true)
		{
			$this->_adv_find = null;
			$this->_adv_where = null;
			$this->_adv_in = null;
			$this->_adv_not_in = null;
			$this->_adv_order = null;
			$this->_adv_limit = null;
			$this->_data_cache_time = 0;
			$this->_show_query = false;
		}
		return $this;
	}
	
}