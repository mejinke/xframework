<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-05-06
 * -+-----------------------------------
 *
 * @desc 是否存在中文汉字验证
 * @author jingke
 */
class XF_String_Validate_Chinese implements XF_String_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		if (preg_match("/[\x7f-\xff]/", $var))
		{
			return TRUE;
		}
		return FALSE;
	}
}