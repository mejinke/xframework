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
 * @desc 数据库配置信息接口
 * @author jingke
 */
interface XF_Db_Config_Interface
{
	
	/**
	 * 获取主机地址
	 * @return string
	 */
	public function getHost();
	
	/**
	 * 设置主机地址
	 * @param string $var
	 * @return $this
	 */
	public function setHost($var);
	
	/**
	 * 获取数据库编码
	 * @return string
	 */
	public function getChar();
	
	/**
	 * 设置数据库编码
	 * @return string
	 */
	public function setChar($var);
	
	/**
	 * 获取主机端口
	 * @return int
	 */
	public function getHostPort();
	
	/**
	 * 设置主机端口
	 * @param int $var
	 * @return $this
	 */
	public function setHostPort($var);
	
	/**
	 * 获取数据库访问账号
	 * @return string
	 */
	public function getAccount();
	
	/**
	 * 设置数据库访问账号
	 * @param string $var
	 * @return $this
	 */
	public function setAccount($var);
	
	/**
	 * 获取数据库访问账户密码
	 * @return string
	 */
	public function getPassword();
	
	/**
	 * 设置数据库访问账户密码
	 * @param string $var
	 * @return $this
	 */
	public function setPassword($var);
}