<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-12-16
 * -+-----------------------------------
 *
 * @desc 手机号码验证
 * @author jingke
 */
class XF_String_Validate_Moblie implements XF_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		if (!preg_match("/^1(3|5|8|)\d{10}/", $var))
		{
			return FALSE;
		}
		return TRUE;
	}
}