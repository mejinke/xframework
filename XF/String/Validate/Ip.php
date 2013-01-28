<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-02-16
 * -+-----------------------------------
 *
 * @desc IP地址验证
 * @author jingke
 */
class XF_String_Validate_Ip implements XF_String_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		if (!preg_match("/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/", $var))
		{
			return FALSE;
		}
		return TRUE;
	}
}