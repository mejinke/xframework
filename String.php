<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2012
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-12-23
 * -+-----------------------------------
 *
 * @desc 字符串，文本处理类
 * @author jingke
 */
class XF_String
{


	/**
	 * 截取指定长度的字符串内容 [支持中文]
	 * @access public
	 * @param string $content 字符串内容
	 * @param int $count 要截取的字符个数【以中文个数为准】
	 * @param string $more 超出长度时的表示字符串
	 * @return string
	 */
	public static function substr($content, $count, $more = '')
	{	
		$content = self::text($content);
		$code2Len  = array('gbk' => 2, 'utf-8' => 3);
	    $lenString = mb_strlen($content);
	    $LenSplit  = $count * $code2Len['utf-8'];
	    $spaceLen  = $count * 2;
	    if($lenString <= $LenSplit)
	    {
	        return $content;
	    }
	    else
	    {     
	    	$str = ''; 
	        for($i=-1; $i<=$spaceLen; $i++)
	        {
	            $len = $count + $i;
	            $str = mb_substr($content, 0, $len, 'utf-8');
	            @preg_match_all("/([\x{4e00}-\x{9fa5}]){1}/u", $str, $arrCh);
	            $currentCNs = count($arrCh[0]);
	                       
	            $chrSpace = $len - $currentCNs;
	            $currentSpaces = $currentCNs * 2 + $chrSpace; 
	            $diffSpace = $spaceLen - $currentSpaces;
	            
	            if($more != '')
	            {
	                $str .= $more;
	                if($diffSpace <= 3){break 1;}
	            }
	            else
	            {
	                if($diffSpace <= 1){break 1;}
	            }
	        }
	        return $str;
	    }
	}

	/**
	 * 指定的内容只替换一次
	 * @param string $search 要查找的内容
	 * @param string $replace 要替换的内容
	 * @param string $string 原字符串
	 * @return string
	 */
	public static function str_replace_once($search, $replace, $string) {
	   $pos = strpos($string, $search);
	   if ($pos === false) {
	      return $haystack;
	   }
	   return substr_replace($string, $replace, $pos, strlen($search));
	}
	
	/**
	 * 将字符串转换为HTML代码格式
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public static function shiftHTML($content)
	{
		if ($content == '') return '';
		$content = str_replace('<', '&lt;', $content);
		$content = str_replace('>', '&gt;', $content);
		return $content;
	}


	/**
	 * 过滤JS
	 * @access public
	 * @param string|array $content
	 * @return mixed
	 */
	public static function clearJs($content)
	{
		if ($content == '') return '';
		if(!is_array($content))
		{
			$content = trim($content);
			//完全过滤注释
			$content = preg_replace('/<!--?.*-->/', '', $content);
			//完全过滤动态代码
			$content = preg_replace('/<\?|\?>/', '', $content);
			//完全过滤js
			$content = preg_replace('/<script?.*\/script>/is', '', $content);
			//完全过滤iframe
			$content = preg_replace('/<iframe?.*\/iframe>/is', '', $content);
			//过滤多余html
			$content = preg_replace('/<\/?(html|head|meta|link|base|body|title|script|form|iframe|frame|frameset)[^><]*>/i', '', $content);
			//过滤on事件lang js
			while(preg_match('/(<[^><]+)(onclick|onfinish|onmouse|onexit|onerror|onkey|onload|onchange|onfocus|onblur)[^><]+/i', $content, $mat)) 
			{
				$content=str_replace($mat[0], $mat[1], $content);
			}
			while(preg_match('/(<[^><]+)(window\.|js:|javascript:|about:|file:|document\.|vbs:|vbscript:|cookie)([^><]*)/i', $content, $mat)) 
			{
				$content=str_replace($mat[0], $mat[1].$mat[3], $content);
			}
			//过滤多余空格
			$content = str_replace('  ', ' ', $content);
			return $content;
		}
		else
		{
			foreach ($content as $key => $val)
			{
				if (is_scalar($val))
					$content[$key] == self::clearJs($val);
			}
			return $content;
		}
	}


	/**
	 * 清除样式
	 * @access public
	 * @param string $content
	 * @return mixed
	 */
	public static function clearStyle($content)
	{
	if ($content == '') return '';
		if(!is_array($content))
		{
			$content = trim($content);
			$content = preg_replace('/<style?.*\/style>/is', '', $content);
			while(preg_match('/(<[^><]+)(style)[^><]+/i', $content, $mat)) {
				$content = str_replace($mat[0], $mat[1], $content);
			}
			return $content;
		}
		else
		{
			for($i = 0; $i < count($content); $i++)
			{
				$content[$i] = self::clearStyle($content[$i]);
			}
			return $content;
		}
	}


	/**
	 * 输出纯文本内容 不含任何HTML标记
	 * @param string $content 内容
	 * @return string
	 */
	public static function text($content) {
		$content = str_replace('&nbsp;', '', $content);
		$content = str_replace("\t", '', $content);
		$content = str_replace("\r\n", '', $content);
		$content = str_replace("\n\r", '', $content);
		$content = str_replace(" ", '', $content);
		$content = str_replace("　", '', $content);
		$content = self::clearJs($content);
		//$content = self::clearStyle($content);
		$content = strip_tags($content);
		$content = htmlspecialchars($content, ENT_NOQUOTES);
		
		return $content;
	}
}