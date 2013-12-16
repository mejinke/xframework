<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-08-30
 * -+-----------------------------------
 *
 * @desc 基于SESSION方式保证访问控制信息的存储器
 * @author jingke
 */
class XF_Acl_Storage_Session implements XF_Acl_Storage_Interface
{
	/** 
	 * 命名空间
	 * @var string
	 */
	const NAMESPACE_DEFAULT = 'Acl_Role_Storage';
	
	/** 
	 * session
	 * @var XF_Session_Interface
	 */
	protected $_session = null;
	
	public function __construct()
	{
		$this->_session = new XF_Session(self::NAMESPACE_DEFAULT);
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
    	$this->_session->clear();
    }
}