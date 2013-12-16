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
 * @desc 身份证号码验证 15位或18位
 * @author jingke
 */
class XF_String_Validate_Card implements XF_Validate_Interface
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		if (!ereg("\d{15}|\d{18}", $var))
		{
			return FALSE;
		}
		return TRUE;
	}
}