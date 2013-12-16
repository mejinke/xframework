<?php
/**
 *
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-12-23
 * -+-----------------------------------
 *
 * @desc 框架通用函数静态类
 * @author jingke
 */
class XF_Functions
{

    /**
     * 在数组指定位置插入值
     * @access public
     * @param array $array 源数组
     * @param int $key_index 插入的位置下标(只能是数字下标)
     * @param mixed $value 插入的值
     * @return array
     */
    public static function arrayInsert(Array &$array, $key_index, $value)
    {
        if(!is_array($array) || !is_numeric($key_index))
            return false;
        if(isset($array[$key_index]))
        {
            $tmp = array();
            foreach($array as $key => $val)
            {
                if(is_numeric($key) && $key < $key_index)
                {
                    $tmp[$key] = $val;
                }
                elseif(is_numeric($key) && $key === $key_index)
                {
                    $tmp[$key] = $value;
                    $tmp[$key+1] = $val;
                }
                elseif(is_numeric($key) && $key > $key_index)
                {
                    $tmp[$key+1] = $val;
                }
            }

            $array = $tmp;
        }
        else
            $array[$key_index] = $value;
        return $array;
    }

    /**
     * 删除数组中指定值的元素
     * @access public
     * @param array $array 数组
     * @param string $value 元素值
     * @return array
     */
    public static function arrayDeleteFromValue(Array &$array, $value)
    {
        foreach($array as $key => $val)
        {
            if($val === $value)
                unset($array[$key]);
        }
        return $array;
    }

    /**
     * 删除数组中值为空的元素
     * @access public
     * @param array $array 数组
     * @param bool $sort 是否重新排序？
     * @return array
     */
    public static function arrayDeleteEmptyValue(Array &$array, $sort = FALSE)
    {
        if(!empty($array))
        {
            $keys = array_keys($array);
            for($i=0; $i<count($keys); $i++)
            {
                $tep = trim($array[$keys[$i]]);
                if($tep == '')
                    unset($array[$keys[$i]]);
            }
        }
        $array = $sort ? array_values($array) : $array;
        return $array;
    }


    /**
     * 在数组中查找指定的内容
     * @param array $array
     * @param string $value
     * @return mixed 存在则返回数组下标，否则返回 false
     */
    public static function searchValueFromArray(Array $array, $value)
    {
        foreach ($array as $key => $val)
        {
            if (strpos($val, $value) !== false)
                return $key;
        }
        return false;
    }
    
	/**
	 * 给定的变量是否为空
	 * 为空的值有：null false "" 
	 * @param mixed $var
	 * @return bool
	 */
	public static function isEmpty($var)
	{
		if ($var === false || $var === null || $var === '' || (is_array($var) && count($var)==0) || $var === ' ' )
			return true;
		
		return false;
	}
	
	/**
	 * 加密，解密函数
	 * @param string $string 将要加密码的字符中
	 * @param string $operation ENCODE | DECODE
	 */
	public static function authCode($string, $operation = 'ENCODE')
	{
		$key = md5("*!mV-XF[x0O-9gIU]=");
		$key_length = strlen($key);
	
		$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		$string_length = strlen($string);
	
		$rndkey = $box = array();
		$result = '';
	
		for($i = 0; $i <= 255; $i++) 
		{
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
	
		for($j = $i = 0; $i < 256; $i++) 
		{
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) 
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') 
		{
			if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8))
				return substr($result, 8);
			else
				return '';
		}
		else 
			return str_replace('=', '', base64_encode($result));
	}
	
	/**
	 * 将数字转换成字符串
	 * @param float double integer $value
	 * @return string
	 */
	public static function numberToString($value)
	{
		if (in_array(gettype($value), array('float','double','integer')))
		{ 
			return "$value";
		}
		return $value;
	}
	
	/**
	 * URL 跳转
	 * @access public
	 * @param string $url 跳转的URL
	 * @param int $code 重定向状态码 默认为302
	 * @return void
	 */
	public static function go($url = '', $code = 302)
	{
		//检测是否存在对应的扩展
		if(function_exists('go'))
		{
			go($url);
			return;
		}
		
		if ($code == 301)
			header('HTTP/1.1 301 Permanently Moved');
			
		if(substr($url,0,7)=='http://')
			header("location:".$url);
		else
			header("location:".$url);
		exit;
	}
		
	/**
	 * 弹出 JS Alert 信息框[可扩展]
	 * @access public
	 * @param String $msg 显示的消息
	 * @param String $url 跳转的URL
	 * @return void
	 */
	public static function alert($msg, $url='')
	{
		//检测是否存在对应的扩展
		if(function_exists('alert'))
		{
			alert($msg, $url);
			return;
		}
		if(empty($url))
		{
			echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
			echo'<script>alert("'.$msg.'");history.go(-1);</script>';
			echo '</body></html>';
		}
		else
		{
			echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
			echo'<script>alert("'.$msg.'");location.href=\''.$url.'\'</script>';
			echo '</body></html>';
		}
		exit;
	 }
		
	/**
	 * 返回上一页
	 * @access public
	 */
	public static function back()
	{	
		//检测是否存在对应的扩展
		if(function_exists('back'))
		{
			back();
			return;
		}
		echo'<script>self.location=document.referrer;</script>';
	}
	
	/** 
	 * 获取当前时间 
	 * @access public
	 * @return float 
	 */ 
	public static function getCurrentTime()
    {
        list ($msec, $sec) = explode(" ", microtime());
        return (float)$msec + (float)$sec;
    }
    
	/**
	 * 获取过去或都未来的年月
	 * @access public
	 * @param int $sign 负数为过去的月份数，正数为未来的月份数
	 * @return string
	 */
	public static function getOverFutureYearMonth($sign)
	{
		if($sign == 0) return date('Y-m');
		
	    //年份
	    $tmp_year= date('Y');
	    //月份
	    $tmp_mon = date('m');
	    //获取上X个月
	    if($sign < 0)
	    {
	    	$sign = $sign*-1;
	    	if($sign < $tmp_mon)
	    		return date('Y-m', mktime(0, 0, 0, $tmp_mon-$sign, 1, $tmp_year));
	    	if($sign == $tmp_mon)
	    		return date('Y-m', mktime(0, 0, 0, 12, 1, $tmp_year-1));
	
	    	$tmp_year = $tmp_year-1;
	    	$sign = $sign - $tmp_mon;
	    	//有多少个12月
	    	$x = floor($sign / 12);
	    	if($x == 0)
	    		return date('Y-m', mktime(0, 0, 0, $sign, 1, $tmp_year));
	    	return date('Y-m', mktime(0, 0, 0, $sign-12*$x, 1, $tmp_year-$x));
	    }
	    //获取下X个月
	    if($tmp_mon == 12)
	    {
	    	//有多少个12月
	    	$x = floor($sign / 12);
	    	if($x == 0)
	    		return date('Y-m', mktime(0, 0, 0, $sign, 1, $tmp_year+1));
	    	return date('Y-m', mktime(0, 0, 0, $sign-12*$x, 1, $tmp_year+$x));
	    }
	
	    if($sign+$tmp_mon > 12)
	    {
	    	$sign = $sign - (12- $tmp_mon);
	    	$tmp_year++;
	    	//有多少个12月
	    	$x = floor($sign / 12);
	    	if($x == 0)
	    		return date('Y-m', mktime(0, 0, 0, $sign, 1, $tmp_year));
	    	return date('Y-m', mktime(0, 0, 0, $sign-12*$x, 1, $tmp_year+$x));
	    }
	    return date("Y-m", mktime(0, 0, 0, $sign+$tmp_mon, 1, $tmp_year));
	}
	
	/**
	 * 写入错误日志
	 * @access public
	 * @param string $message 内容
	 * @return void
	 */
	public static function writeErrLog($message)
	{
		if (!is_scalar($message) && $message == '') return;
		
		$dir = TEMP_PATH.'/Logs/Error/'.date('md').'/';
		if (!is_dir($dir)) XF_File::mkdirs($dir);
		$file = $dir.date('H').'.log';
		XF_File::write($file, '['.date('H:i:s').'] '.$message."\n", 'a+');
	}
	
	/**
	 * 写入日志
	 * @access public
	 * @param string $message 内容
	 * @return void
	 */
	public static function log($message)
	{
		if (!is_scalar($message) && $message == '') return;
		$file = TEMP_PATH.'/Logs/'.date('Y-m-d').'.log';
		XF_File::write($file, '['.date('H:i:s').'] '.$message, 'a+');
	}
}
