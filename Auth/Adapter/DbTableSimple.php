<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-5-28
 * -+-----------------------------------
 *
 * @desc 简单通过数据表中的一列字段进行认证
 * @author mejinke@gmail.com 2013-5-28
 */
class XF_Auth_Adapter_DbTableSimple implements XF_Auth_Adapter_Interface
{
	/** 
	 * 用于认证的表对象
	 * @var XF_Db_Table_Abstract
	 */
	protected $_dbTable = null;
	
	/**
	 * 认证身份列
	 * @var string
	 */
	protected $_identity_column = NULL;
	
	/**
	 * 用于认证的身份
	 * @var string
	 */
	protected $_identity = NULL;
	
	/**
	 * 认证通过后将要存储的列
	 * @var array
	 */
	protected $_storage_column = '*';
	
	/**
	 * 实例化表认证对象
	 * @param XF_Db_Table_Abstract $dbTable
	 * @return void
	 */
	public function __construct(XF_Db_Table_Abstract $dbTable)
	{
		$this->_dbTable = $dbTable;	
	}
	
	/**
	 * 设置认证的身份
	 * @param string $column　　
	 * @return XF_Auth_Adapter_DbTable
	 */
	public function setIdentityColumn($column)
	{
		$column = (string) $column;
		$this->_identity_column = $column;	
		return $this;
	}
	
	
	/**
	 * 设置身份
	 * @param string $var　　
	 * @return XF_Auth_Adapter_DbTable
	 */
	public function setIdentity($var)
	{
		$var = (string) $var;
		$this->_identity = $var;
		return $this;
	}
	
	
	/**
	 * 设置认证通后需要存储的列
	 * @param string $column　列数组
	 * @return XF_Auth_Adapter_DbTable
	 */
	public function setStorageColumn(Array $column)
	{
		$this->_storage_column = $column;
		return $this;
	}
	
	/**
	 * 认证
	 * @return XF_Auth_Result
	 */
	public function authenticate()
	{
		if ($this->_dbTable == NULL || ! $this->_dbTable instanceof XF_Db_Table_Abstract)
			throw new XF_Auth_Adapter_Exception('Lack of effective XF_Db_Table_Abstract authentication adapter　！');
		
		if (XF_Functions::isEmpty($this->_identity_column))
			throw new XF_Auth_Adapter_Exception('Certified identity column name and credential column name can not be empty!');
			
		if (XF_Functions::isEmpty($this->_identity))
		{
			return new XF_Auth_Result(-1, NULL);
		}

		$row = $this->_dbTable->getTableSelect()
					->setWhere(array($this->_identity_column => $this->_identity))->fetchRow();
				
		if ($row instanceof XF_Db_Table_Abstract) 
		{
			
			$tmp = array();
			$array = $row->toArray();
			foreach ($array as $key => $val)
			{
				if (is_array($this->_storage_column))
				{
					if (in_array($key, $this->_storage_column))
					{
						$tmp[$key] = $val;
					}
				}
				else
				{
					$tmp[$key] = $val;
				}
				
			}
			return new XF_Auth_Result(9, (object)$tmp);
		}
		
		return new XF_Auth_Result(-1, NULL);
	}
}