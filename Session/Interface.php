<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-12
 * -+-----------------------------------
 *
 * @desc session会话接口
 * @author jingke
 */
interface XF_Session_Interface
{
	/**
	 * session是否为空
	 * @return bool
	 */
	public function isEmpty();
	
	/**
	 * 写入内容
	 * @param mixed $content
	 * @return XF_Session_Interface
	 */
	public function write($content);
	
	/**
	 * 读取内容
	 * @return mixed
	 */
	public function read();
	
	/**
	 * 销毁session
	 * @return XF_Session_Interface
	 */
	public function clear();
	
	/**
	 * 是否存在指定的内容
	 * @param string $key 
	 * @return bool
	 */
	public function hasContent($key);
}