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
 * @desc 标准时间格式验证 例:2012-10-26 17:32:19
 * @author jingke
 */
class XF_String_Validate_Time implements XF_Validate_Interface   
{
	public static function validate($var)
	{
		if (XF_Functions::isEmpty($var))
			return FALSE;
		if (!is_scalar($var))
			return FALSE;
		$tmp = explode(' ', $var);
		if (count($tmp) != 2) return false;
		if (XF_String_Validate_Date::validate($tmp[0]) == false) return false;
		$tmp = explode(':', $tmp[1]);
		if (count($tmp) !=3 )return false;
		if ($tmp[0]>23 || $tmp[0]<0 || $tmp[1]>59 || $tmp[1]<0 || $tmp[2]>59 || $tmp[2]<0) return false;
		return TRUE;
	}
}