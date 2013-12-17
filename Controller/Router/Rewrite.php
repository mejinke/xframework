<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-10
 * -+-----------------------------------
 *
 * @desc 默认路由器重写类
 * @author jingke
 */
class XF_Controller_Router_Rewrite extends XF_Controller_Router_Rewrite_Abstract
{
	
	/**
	 * 初始化
	 * @param string|array $regex　正则表达式,支持数组方式
	 * @param array $mArray　对应的module controller action 数组
	 * @param mixed $paramsArray　对应的参数数组 默认为null  例:array('0:1' => 'page', '1:1'=>'key')
	 * @param array $redirectArray 重定向匹配到的URL
     * @throws XF_Controller_Router_Rewrite_Exception
	 */
	public function __construct($regex, Array $mArray, Array $paramsArray = NULL, $redirectArray = null)
	{
		if (!isset($mArray['module']) || !isset($mArray['controller']) || !isset($mArray['action']))
			throw new XF_Controller_Router_Rewrite_Exception('正则路由缺少对应的Module,Controller,Action数组！');
		$this->_regex = $regex;
		$this->_ma_array = $mArray;
		$this->_params_array = $paramsArray;
		$this->_redirect_array = $redirectArray;
	}
		
	/**
	 * 开始匹配规则
	 * @param string $uri 请求的URI
	 * @return bool
	 */
	public function match($uri)
	{
		$tmp = null;
		//转换成数组
		if (is_string($this->_regex))
			$this->_regex = array($this->_regex);

		foreach ($this->_regex as $k => $regex)
		{
			@preg_match($regex, $uri, $tmp); 
			$this->_matchAnalysis($uri, $regex, $tmp, $k);
			if ($this->_match_status == true)
			{
				//是否存在重定向配置
				if (isset($this->_redirect_array[$k]))
				{
					$redirectUrl = $this->_redirect_array[$k];
					$keys = null;
					foreach ($tmp as $kk => $v)
					{
						if ($kk !=0)
							$keys['$'.$kk] = $v;
					}
					if (is_array($keys))
						$redirectUrl =  str_replace(array_keys($keys), array_values($keys), $redirectUrl);
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.$redirectUrl);
				}
				break;
			}
		}
		return $this->_match_status;
	}
	
	
	/**
	 * 分析匹配结果
	 * @param string $uri 请求的URI
	 * @param string $regex 具体规则
	 * @param array $tmp 匹配后的结果数组
	 * @param int $regexIndex 当前是第几个规则【是指同一个名称里的规则，不是全局】
	 * @return void
	 */
	private function _matchAnalysis($uri, $regex, $tmp, $regexIndex)
	{
		//是否匹配成功
		if (is_array($tmp) && XF_Functions::isEmpty($tmp) === FALSE || ($regex == '/' && $uri == '/'))
		{
			//记录匹配到的规则方便调式用
            if(XF_Config::getInstance()->getSaveDebug())
			    XF_DataPool::getInstance()->addHash('DEBUG', 'Match_Rewrite', $regex);

			$this->_match_status = TRUE;	
			$request = XF_Controller_Request_Http::getInstance();
			$request->setModule($this->_ma_array['module'])
					->setController($this->_ma_array['controller'])
					->setAction($this->_ma_array['action']);
			
			//是否存在自定义的附加参数
			if (isset($this->_ma_array['params']) && is_array($this->_ma_array['params']))
			{
				foreach ($this->_ma_array['params'] as $k => $val)
				{
					$request->setParam($k, $val);
				}
			}
			
			//设置匹配到的参数
			if (is_array($this->_params_array))
			{
				foreach ($this->_params_array as $key => $val)
				{
					//指定的参数是否只匹配指定位置的规则
					$keyTmp = explode(':', $key);
					if (!isset($keyTmp[1]))
					{
						if (isset($tmp[$keyTmp[0]]))
							$request->setParam($val, urldecode($tmp[$keyTmp[0]]));
					}
					else 
					{
						if ($keyTmp[0] == $regexIndex && isset($tmp[$keyTmp[1]])) //规则位置从0开始
							$request->setParam($val, urldecode($tmp[$keyTmp[1]]));
					}	
				}
			}
			
			//////重写URL后，尝试获取设置的参数
			$uri = str_replace($tmp[0], '', $uri);
			$tep = explode('/', $uri);
			if (is_array($tep) && count($tep) >= 2)
			{
				if ($tep[0] == '')
					unset($tep[0]);
				$tep = array_values($tep);
				foreach ($tep as $key => $val)
				{
					if (isset($tep[$key+1]) && $tep[$key+1] != '')
						$request->setParam($val, urldecode($tep[$key+1]));
					else 
						break;
				}
			}
		}
	}
}