<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-11-19
 * -+-----------------------------------
 *
 * @desc 数据验证接口
 * @author jingke
 */
interface XF_Validate_Interface
{
	/**
	 * 校验数据
	 * @param mixed $var
	 * @return bool 成功返回TRUE，否则FALSE
	 */
	public static function validate($var);
}