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
 * @desc 认证适配器接口
 * @author jingke
 */
interface XF_Auth_Adapter_Interface
{
	/**
	 * 认证
	 * @return XF_Auth_Result
	 */
	public function authenticate();
}