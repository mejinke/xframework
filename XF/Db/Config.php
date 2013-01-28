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
 * @desc 数据库配置信息类
 * @author jingke
 */
class XF_Db_Config implements XF_Db_Config_Interface
{
	
	/**
	 * 主机地址
	 * @var string
	 */
	protected $_host;
	
	/**
	 * 主机连接端口
	 * @var int
	 */
	protected $_port;
	
	/**
	 * 默认UTF8编码
	 * @var string
	 */
	protected $_char = 'utf8';
	
	/**
	 * 连接账号
	 * @var string
	 */
	protected $_account;
	
	/**
	 * 账号密码
	 * @var string
	 */
	protected $_password;
	
	public function getHost()
	{
		return $this->_host;
	}
	
	public function setHost($var)
	{
		$this->_host = $var;
		return $this;
	}
	
	public function getHostPort()
	{
		return $this->_port;
	}
	
	public function setHostPort($var)
	{
		$this->_port = $var;
		return $this;
	}

	public function getChar()
	{
		return $this->_char;
	}
	
	public function setChar($var)
	{
		$this->_char = $var;
	}
	
	public function getAccount()
	{
		return $this->_account;
	}
	
	public function setAccount($var)
	{
		$this->_account = $var;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->_password;
	}
	
	public function setPassword($var)
	{
		$this->_password = $var;
		return $this;
	}
}