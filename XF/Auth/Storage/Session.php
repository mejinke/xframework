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
 * @desc Session认证存储器
 * @author jingke
 */
class XF_Auth_Storage_Session implements XF_Auth_Storage_Interface
{
	const NAMESPACE_DEFAULT = 'Auth_Storage';
	
	/** 
	 * 命名空间
	 * @var string
	 */
	protected $_namespace;
	
	/** 
	 * session
	 * @var XF_Session_Interface
	 */
	protected $_session = null;
	
	public function __construct($namespace = self::NAMESPACE_DEFAULT)
	{
		$namespace = (string)$namespace;
		$this->_namespace = $namespace;
		$this->_session = new XF_Session($this->_namespace);
	}
	
	/**
     * 存储是为空
     * @return bool
     */
    public function isEmpty()
    {
    	return $this->_session->isEmpty();
    }

    /**
     * 读取存储的内容
     * @return mixed
     */
    public function read()
    {
    	return $this->_session->read();
    }

    /**
     * 将内容写入到存储器中
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
    	$this->_session->write($contents);
    }

    /**
     * 清空当前存储器中的内容
     * @return void
     */
    public function clear()
    {
    	$this->_session->write(NULL);
    }
}