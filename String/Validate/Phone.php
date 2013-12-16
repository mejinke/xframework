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
 * @desc 电话号码验证
 * @author jingke
 */
class XF_String_Validate_Phone implements XF_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		if (!preg_match("/\d{3}-\d{8}|\d{4}-\d{7}/", $var))
		{
			return FALSE;
		}
		return TRUE;
	}
}