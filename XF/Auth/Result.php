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
 * @desc 认证结果类
 * @author jingke
 */
class XF_Auth_Result
{
	/**
	 * 认证状态：失败
	 * @var int
	 */
	const AUTH_STATUS_FAILURE = -1;
	
	/**
	 * 认证状态：成功
	 * @var int
	 */
	const AUTH_STATUS_SUCCESS = 9;
	
	/** 
	 * 认证状态[是否通过认证]
	 * @var boolean
	 */
	protected $_status = FALSE;
	
	/** 
	 * 认证状态码
	 * @var int
	 */
	protected $_code;
	
	/** 
	 * 身份信息
	 * @var mixed
	 */
	protected $_identity;
	
	
	public function __construct($code = self::AUTH_STATUS_FAILURE, $identity)
	{
		$this->_code = $code;
		if ($this->_code == 9)
			$this->_status = TRUE;
		$this->_identity = $identity;	
	}
	
	/**
	 * 获取身份信息
	 * @return mixed
	 */
	public function getIdentity()
	{
		return $this->_identity;
	}
	
	/**
	 * 获取认证状态码
	 * @return int
	 */
	public function getCode()
	{
		$this->_code;	
	}
	
	/**
	 * 是否认证通过
	 * @return boolean
	 */
	public function isAuthOK()
	{
		return $this->_status;
	}
}