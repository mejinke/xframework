<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-06-13
 * -+-----------------------------------
 *
 * @desc Cookie认证存储器
 * @author jingke
 */
class XF_Auth_Storage_Cookie implements XF_Auth_Storage_Interface
{
	const NAMESPACE_DEFAULT = 'Auth_Storage';
	
	/** 
	 * 命名空间
	 * @var string
	 */
	protected $_namespace;
	
	/** 
	 * cookie
	 * @var XF_Cookie_Interface
	 */
	protected $_cookie = null;
	
	public function __construct($namespace = self::NAMESPACE_DEFAULT)
	{
		$namespace = (string)$namespace;
		$this->_namespace = $namespace;
		$this->_cookie = new XF_Cookie($this->_namespace);
	}
	
	/**
     * 存储是为空
     * @return bool
     */
    public function isEmpty()
    {
    	return $this->_cookie->isEmpty();
    }

    /**
     * 读取存储的内容
     * @return mixed
     */
    public function read()
    {
    	return $this->_cookie->read();
    }

    /**
     * 将内容写入到存储器中
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
    	$this->_cookie->write($contents);
    }

    /**
     * 清空当前存储器中的内容
     * @return void
     */
    public function clear()
    {
    	$this->_cookie->write(NULL);
    }
}