<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-10-26
 * -+-----------------------------------
 *
 * @desc 日期格式验证 例：1970-01-01
 * @author jingke
 */
class XF_String_Validate_Date implements XF_String_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		$tmp = explode('-', $var);
		if (count($tmp) != 3)
			return FALSE;
		if (!is_numeric($tmp[0]) || !is_numeric($tmp[1]) || !is_numeric($tmp[2]))
			return FALSE;
		if ($tmp[0]<1970) return false;
		if ($tmp[1]<1 || $tmp[1] >12) return false;
		if ($tmp[2]<1 || $tmp[2] > 31) return false;
		return TRUE;
	}
}