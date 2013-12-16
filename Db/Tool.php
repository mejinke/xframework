<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2010-01-08
 * -+-----------------------------------
 *
 * @desc 数据库操作相关工具
 * @author jingke
 */
class XF_Db_Tool
{

	/**
	 * 条件数组格式化
	 * @access public
	 * @param mixed $var 条件数组或字符串
	 * @return string
	 */
	public static function whereFormat($var)
	{

		if (!is_array($var))
			return $var;
		else
		{	$string = '';
			foreach ($var as $key=>$val)
			{
				$val = self::escape($val);
				$arr = explode(',', $key);
				if (isset($arr[1]))
				{
					if ($arr[1]=='like%.')
					{
						$arr[1] = 'LIKE';
						$val = "%$val";
					}
					elseif ($arr[1] == 'like.%')
					{
						$arr[1] = 'LIKE';
						$val = "$val%";
					}
					elseif ($arr[1] == 'like%.%')
					{
						$arr[1] = 'LIKE';
						$val = "%$val%";
					}
				}
				else
					$arr[1] = '=';
				if ($arr[1] == 'pk')
					$string .= empty($string) ? "(`".$arr[0]."` ".$val.")" : " AND (`".$arr[0]."` ".$val.")";
				else
					$string .= empty($string) ? "(`".$arr[0]."`".$arr[1]."'".$val."')" : " AND (`".$arr[0]."`".$arr[1]."'".$val."')";
			}
			return $string;
		}
	}

	
	/**
	 * 查询指定的字段格式化
	 * @access public
	 * @param mixed $var 字符串或数组 
	 * @return string
	 */
	public static function findFormat($var)
	{
		if (XF_Functions::isEmpty($var))
			return '*';
		if (!is_array($var))
			return $var;
		else 
		{
			$string = '';
			for($i = 0; $i< count($var); $i++)
			{
				if ($i == 0)
					$string ='`'.$var[$i].'`';
				else 
					$string .=',`'.$var[$i].'`';
			}
			return $string;
		}
	}
	
	
	/**
	 * in条件格式化
	 * @param Array $var
	 * @param string $type IN 或 NOT IN
	 */
	public static function inFormat($var ,$type)
	{
		if(!empty($var))
		{
			for ($i = 0; $i< count($var); $i++)
			{
				$var[$i] = str_replace("'", "\'", $var[$i]);
				$var[$i] = '\''.$var[$i].'\'';
			}
			return $type.'('.implode(',',$var).') ';
		}
		return false;
	}

	/**
	 * 获取配置文件中的数据库名称
	 * @access public
	 * @param string $var db_list 键名称
	 * @return string 返回一个数据库名称
	 */
	public static function getDBName($var)
	{
		$dbs = XF_Config::getInstance()->getDBNames();
		if (!is_array($dbs))
			throw  new XF_Application_Exception('缺少dbnames配置资料！', 1000);

		//如果没有预期的数据库名称，则回返当前key名称
		return isset($dbs[$var]) ? $dbs[$var] : $var;
	}


	/**
	 * 对SQL中特殊的字符进行过滤处理
	 * @access public
	 * @param string $value 需要处理的字符串
	 * @return string
	 */
	public static function escape($value)
	{
		if (is_object($value)) return '';
		if(is_null($value)) return 'NULL';
		if (is_string($value)) return $value;
		if(is_bool($value)) return $value ? 1 : 0;
		if(is_numeric($value)) return floatval($value);
		if(@get_magic_quotes_gpc()) $value = stripslashes($value);
		return @mysql_escape_string($value);
	}
}