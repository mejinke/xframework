<?php
class XF_Auth_Adapter_DbTable implements XF_Auth_Adapter_Interface
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
	 * 认证证书列
	 * @var string
	 */
	protected $_credential_column = NULL;
	
	/**
	 * 认证是否需要加密码匹配
	 * @var bool
	 */
	protected $_authenticate_md5 = FALSE;
	
	/**
	 * 用于认证的身份
	 * @var string
	 */
	protected $_identity = NULL;
	
	/**
	 * 用于认证的证书
	 * @var string
	 */
	protected $_credential = NULL;
	
	/**
	 * 认证通过后将要存储的列
	 * @var array
	 */
	protected $_storage_column = '*';
	
	/**
	 * 实例化表认证对象
	 * @param XF_Db_Table_Abstract $dbTable
	 * @param bool $authenticate_md5 认证密码是否MD5加密？　默认为FALSE
	 * @return void
	 */
	public function __construct(XF_Db_Table_Abstract $dbTable, $authenticate_md5 = FALSE)
	{
		$this->_dbTable = $dbTable;	
		$this->_authenticate_md5 = $authenticate_md5;
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
	 * 设置认证的证书
	 * @param string $column 　
	 * @return XF_Auth_Adapter_DbTable
	 */
	public function setCredentialColumn($column)
	{
		$column = (string) $column;
		$this->_credential_column = $column;
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
	 * 设置证书
	 * @param string $var　
	 * @return XF_Auth_Adapter_DbTable
	 */
	public function setCredential($var)
	{
		$var = (string) $var;
		$this->_credential = $var;
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
		
		if (XF_Functions::isEmpty($this->_identity_column) || XF_Functions::isEmpty($this->_credential_column))
			throw new XF_Auth_Adapter_Exception('Certified identity column name and credential column name can not be empty!');
			
		if (XF_Functions::isEmpty($this->_identity) || XF_Functions::isEmpty($this->_credential))
		{
			return new XF_Auth_Result(-1, NULL);
		}

		$passwd = $this->_authenticate_md5 ? md5($this->_credential) : $this->_credential;
		
		$row = $this->_dbTable->getTableSelect()
					->setWhere(array($this->_identity_column => $this->_identity))->fetchRow();
				
		if ($row instanceof XF_Db_Table_Abstract) 
		{
			//证书是否正确
			if ($row->{$this->_credential_column} != $passwd)
				return new XF_Auth_Result(-2, NULL);
				
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