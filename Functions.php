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
                if($tep=='')
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
		if ($var === false || $var === null || $var == '' || (is_array($var) && count($var)==0) || $var == ' ' )
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
		$key = md5("*!mV-=");
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
	 * edauth参数解释 By PGcao 排骨
	 * @param string $string 明文 或 密文
	 * @param bool $operation false表示解密,true表示加密
	 * @param int $outtime 密文有效期, 单位为秒
	 * @param string $key 密匙
	 * @param string $entype 加密方式 有md5和sha1两种 加密解密需要统一使用同一种方式才能正确还原明文
	 */
	function edauth($string, $operation = true ,$outtime = 0, $key = '!~*VM,01&?', $entype = 'md5'){
	    $key_length = 4;
	    if($entype == 'md5'){ //使用md5方式
	      $long_len = 32; $half_len = 16; $entype == 'md5';
	    }else{ //使用sha1方式
	      $long_len = 40; $half_len = 20; $entype == 'sha1';
	    }
	    $key = md5($key);
	        $fixedKey = hash($entype, $key); 
	    $egiskeys = md5(substr($fixedKey, $half_len, $half_len)); 
	        $runtoKey = $key_length ? ($operation ? substr(hash($entype, microtime(true)), -$key_length) : substr($string, 0, $key_length)) : ''; 
	        $keys = hash($entype, substr($runtoKey, 0, $half_len) . substr($fixedKey, 0, $half_len) . substr($runtoKey, $half_len) . substr($fixedKey, $half_len));
	        $string = $operation ? sprintf('%010d', $outtime ? $outtime + time() : 0).substr(md5($string.$egiskeys), 0, $half_len) . $string : base64_decode(substr($string, $key_length)); 
	        $i = 0; $result = ''; 
	        $string_length = strlen($string);
	        for ($i = 0; $i < $string_length; $i++){
	            $result .= chr(ord($string{$i}) ^ ord($keys{$i % $long_len})); 
	        }
	    if($operation){
	      return $runtoKey . str_replace('=', '', base64_encode($result));
	    }else{
	            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, $half_len) == substr(md5(substr($result, $half_len+10).$egiskeys), 0, $half_len)) {
	                return substr($result, $half_len+10);
	            } else {
	                return '';
	            }
	    }
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
	 */
	public static function go($url = '')
	{
		//检测是否存在对应的扩展
		if(function_exists('go'))
		{
			go($url);
			return;
		}
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
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
			echo'<script>alert("'.$msg.'");history.go(-1);</script>';
			echo '</body></html>';
		}
		else
		{
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
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
}
