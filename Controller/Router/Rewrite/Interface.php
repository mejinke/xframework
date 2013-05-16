<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-10
 * -+-----------------------------------
 *
 * @desc 路由规则重写接口
 * @author jingke
 */
interface XF_Controller_Router_Rewrite_Interface
{
	
	/**
	 * 开始匹配规则
	 * @param string $uri
	 * @return void
	 */
	public function match($uri);
	
	/**
	 * 是否匹配规则
	 * @return bool
	 */
	public function isMatch();
	
}