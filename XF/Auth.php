<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-11
 * -+-----------------------------------
 *
 * @desc 认证
 * @author jingke
 */
class XF_Auth
{
	/**
	 * 当前实例
	 * @var XF_Auth
	 */
	protected static $_instance = NULL;
	
	/**
	 * 认证适配器
	 * @var XF_Auth_Adapter_Interface
	 */
	protected $_adapter = NULL;
	
	/**
	 * 存储器
	 * @var XF_Auth_Storage_Interface
	 */
	protected $_storage = NULL;
	
	protected function __clone()
	{}
	
	protected function __construct()
	{}
	
	/**
	 * 获取当前实例
	 * @return XF_Auth
	 */
	public static function getInstance()
	{
		if (self::$_instance === NULL)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 认证
	 * @return XF_Auth_Result
	 */
	public function authenticate()
	{
		if ($this->_adapter === NULL)
			throw new XF_Auth_Exception('Lack of authentication adapter!');
		$authResult = $this->_adapter->authenticate();
		$this->getStorage();
		$this->_storage->clear();
		if ($authResult->isAuthOK())
		{ 
			$this->_storage->write($authResult->getIdentity());
		}
		return $authResult;
	}
	
	/**
	 * 设置认证适配器
	 * @param XF_Auth_Adapter_Interface $adapter
	 * @return XF_Auth
	 */
	public function setAdapter(XF_Auth_Adapter_Interface $adapter)
	{
		$this->_adapter = $adapter;
		return $this;
	}
	
	/**
	 * 获取存储器
	 * @return XF_Auth_Storage_Interface
	 */
	public function getStorage()
	{
		if (NULL === $this->_storage)
		{
			$this->setStorage(new XF_Auth_Storage_Session());
		}
		return $this->_storage;
	}
	
	/**
	 * 设置存储器
	 * @param XF_Auth_Storage_Interface $storage
	 * @return XF_Auth
	 */
	public function setStorage(XF_Auth_Storage_Interface $storage)
	{
		$this->_storage = $storage;
		return $this;
	}
}