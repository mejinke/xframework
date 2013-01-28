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
	 * @param int $length 截取长度
	 * @param string $more 超出长度时的表示字符串
	 * @return string
	 */
	public static function substr($content, $length, $more = '')
	{	
	
		$content = self::text($content);
		if ($content == '') return '';
		$strlen = strlen($content);
		for($i = 0; $i < $length; $i++)
		{
			$temp_str = substr($content,0,1);
			if(ord($temp_str) > 127)
			{
				$i++;
				if($i < $length)
				{
				$new_str[] = substr($content, 0, 3);
				$content = substr($content, 3);
				}
			}
			else
			{
				$new_str[] = substr($content, 0, 1);
				$content = substr($content, 1);
			}
		}
		$new_str = join($new_str);
	
		if ($strlen > $length)
			$new_str .= $more;
		return $new_str;
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
		$content = self::clearJs($content);
		//$content = self::clearStyle($content);
		$content = strip_tags($content);
		$content = htmlspecialchars($content, ENT_NOQUOTES);
		
		return $content;
	}
}