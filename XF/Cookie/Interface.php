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
 * @desc Cookie会话接口
 * @author jingke
 */
interface XF_Cookie_Interface
{
	/**
	 * Cookie是否为空
	 * @return bool
	 */
	public function isEmpty();
	
	/**
	 * 写入内容
	 * @param mixed $content
	 * @param int $expire 过期时间 默认为一周7天 单位：秒
	 * @param string $path 有效路径 默认为NULL
	 * @return XF_Cookie_Interface
	 */
	public function write($content, $expire = 604800 , $path = '/');
	
	/**
	 * 读取内容
	 * @return mixed
	 */
	public function read();
	
	/**
	 * 是否存在指定的内容
	 * @param string $key 
	 * @return bool
	 */
	public function hasContent($key);
}