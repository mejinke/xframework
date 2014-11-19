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
 * @desc 框架默认路由器
 * @author jingke
 */
class XF_Controller_Router extends XF_Controller_Router_Abstract
{

	/**
	 * Request
	 * @var XF_Controller_Request_Http
	 */
	private $_request;
	
	/**
	 * 最终请求的URI
	 * @var string
	 */
	private $_final_uri;
	
	/**
	 * 模块域名绑定列表
	 * @var array
	 */
	private $_bin_domain = array();
	
	/**
	 * 被绑定域名的模块允许访问的其它应用
	 * @var array
	 */
	private $_bin_domain_allow_modules;
	
	/**
	 * 关闭的模块[任何情况下都无法访问的模块]
	 * @var array
	 */
	private $_close_modules = array();
	
	/**
	 * 是否关闭所有的子模块[任何情况下都无法访问的模块]
	 * @var bool
	 */
	private $_close_all_module = false;
	
	/**
	 * 开启的模块
	 * @var array
	 */
	private $_open_modules = array();
	
	/**
	 * 当前请求的HTTP HOST
	 * @var string
	 */
	private $_request_http_host;
	
	/**
	 * 传统请求方式的参数字符串
	 * @var string
	 */
	private $_old_request_params_string = '';
	
	/**
	 * 当前请求的是否为默认路由
	 * @var bool
	 */
	private $_request_default_route = TRUE;
	
	
	/**
	 * 获得最终将要处理的URI地址
	 * @access private
	 * @return void
	 */
	private function _getFinalUri()
	{
		$request_uri = $this->_request->getServer('REQUEST_URI');
		 
		//防止接到的GET内容编码不是UTF-8 2013-03-06
		$codeType = mb_detect_encoding($request_uri,array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
		if($codeType!='UTF-8'){
			$request_uri = iconv($codeType,'utf-8',$request_uri);
		}
		
		//通过问号拆分传统参数
        $request_uri = explode('?', $request_uri);
        $this->_final_uri = $request_uri[0];
		$this->_old_request_params_string = isset($request_uri[1]) ? $request_uri[1] : '';//传统请求方式的参数
		$this->_request_http_host = $_SERVER['HTTP_HOST'];
	}
	
	
	/**
	 * 模块域名绑定
	 * @access public
	 * @param string $moduleName 模块名称
	 * @param string $domain 域名
	 * @return XF_Controller_Router
	 */
	public function bindDomain($moduleName, $domain)
	{
		$this->_bin_domain[$domain] = $moduleName;
		return $this;
	}
	
	/**
	 * 获取绑定过域名的模块列表
	 * @access public
	 * @return array 可能是一个空的数组
	 */
	public function getAllBindModuleName()
	{
		if(count($this->_bin_domain) == 0)
			return array();
		return array_values($this->_bin_domain);
	}
	
	/**
	 * 添加被绑定域名的模块允许访问的其它应用
	 * @param string $moduleName 被绑定域名的模块名
	 * @param string $allowModuleName 允许访问的模块名
	 * @return XF_Controller_Router
	 */
	public function binDomainAllowModule($moduleName, $allowModuleName)
	{
		if (!in_array($moduleName, $this->_bin_domain)) return true;
		if (!is_array($this->_bin_domain_allow_modules[$moduleName]))
			$this->_bin_domain_allow_modules[$moduleName] = array();
		$this->_bin_domain_allow_modules[$moduleName][] = $allowModuleName;
		return $this;
	}
	
	/**
	 * 指定的域名是否有绑定模块
	 * @access public
	 * @param string $domain 域名
	 * @return bool
	 */
	public function isBinDomain($domain)
	{
		return isset($this->_bin_domain[$domain]);
	}
	
	/**
	 * 关闭模块[任何情况下都无法访问的模块]
	 * @access public
	 * @param string $moduleName 要关闭的模块名
	 * @return XF_Controller_Router
	 */
	public function closeModule($moduleName)
	{
		$m = (string)$moduleName;
		$this->_close_modules[strtolower($m)] = $m;
		return $this;
	}
	
	/**
	 * 关闭所有的子模块[任何情况下都无法访问的模块]
	 * @access public
	 * @return XF_Controller_Router
	 */
	public function closeAllChildModule()
	{
		$this->_close_all_module = true;
		return $this;
	}
	
	/**
	 * 开启模块
	 * @param string $moduleName 模块名称
	 * @return XF_Controller_Router
	 */
	public function openModule($moduleName)
	{
		$m = (string)$moduleName;
		$this->_open_modules[strtolower($m)] = $m;
		if (isset($this->_close_modules[strtolower($m)]))
		{
			unset($this->_close_modules[strtolower($m)]);
		}
		return $this;
	}

	/**
	 * 启动路由器
	 * @access public
	 * @return void
	 */
	public function run()
	{
		$this->_request = XF_Controller_Request_Http::getInstance();
		$this->_getFinalUri();
		$status = $this->_validateRewrite();
		if ($status == false)
		{
			//是否启用默认路由规则
			if (XF_Config::getInstance()->isUseDefaultRouter() == false)
			{
				if ($this->_final_uri != '' && $this->_final_uri != '/' && $this->_final_uri != '/index.php')
					throw new XF_Controller_Router_Exception('404 Not found!', 404);
				else 
					$this->_setDefault();
			}
			else
				$this->_start();
		}

		//当前分析到的模块是否有效
		if ($this->_request->getModule() == 'unknown')
		{
			throw new XF_Controller_Router_Exception('404 Not found!', 404);
		}
		if (strtolower($this->_request->getModule()) != 'default' && $this->_close_all_module === true && !isset($this->_open_modules[strtolower($this->_request->getModule())]))
		{
			throw new XF_Controller_Router_Exception('The module is close', 404);
		}
		if (isset($this->_close_modules[strtolower($this->_request->getModule())]))
		{
			throw new XF_Controller_Router_Exception('The module is close', 404);
		}
			
		//当前Action是否禁用了默认路由
		if ($this->_request_default_route === TRUE)
		{
			if (isset($this->_disabled_default_routes[strtolower($this->_request->getModule().$this->_request->getController().$this->_request->getAction())]))
			{
				throw new XF_Controller_Router_Exception('The action default router disabled', 404);
			}
		}
		$this->_readOldRequestParams();
	}
	
	
	/**
	 * 验证路由重写规则
	 * @access private
	 * @return bool　是否成功匹配到自定义的路由规则
	 */
	private function _validateRewrite()
	{
		$status = FALSE;
		krsort($this->_rewrite_index);
		foreach ($this->_rewrite_index as $name)
		{
			//记录准备要匹配的规则名称
            if(XF_Config::getInstance()->getSaveDebug())
			    XF_DataPool::getInstance()->addHash('DEBUG', 'NowMatchRewriteName', $name);

			if (TRUE === $this->_rewrites[$name]->match($this->_final_uri))
			{ 
				$status = TRUE;
				break;
			}
		}
		
		//记录URL重写规则匹配顺序,debug信息
        if(XF_Config::getInstance()->getSaveDebug())
        {
            $tmp = array();
            //匹配到的规则
            $matchRewrite = XF_DataPool::getInstance()->getHash('DEBUG', 'Match_Rewrite');
            foreach ($this->_rewrite_index as $name)
            {
                $regxArray = $this->_rewrites[$name]->toArray();
                if (count($regxArray) == 1 && $matchRewrite != false)
                {
                    $regxArray = str_replace($matchRewrite, "<b style=\"color:red\">{$matchRewrite}</b>", $regxArray[0]);
                }
                elseif ($matchRewrite != false)
                {
                    foreach ($regxArray as $k => $v)
                    {
                        $regxArray[$k] = str_replace($matchRewrite, "<b style=\"color:red\">{$matchRewrite}</b>", $v);
                    }
                }

                $tmp[$name] = $regxArray;
            }
            XF_DataPool::getInstance()->addHash('DEBUG', 'Rewites', $tmp);
        }
		
        $this->_request_default_route = !$status;
		return $status;
	}
	
	/**
	 * 通过URL分析模块、控制器、动作名及参数列表
	 * @access private
	 * @return void
	 */
	private function _start()
	{
		if ($this->_final_uri != '' && $this->_final_uri != '/' && $this->_final_uri != '/index.php')
		{
			$tmp = explode('/', $this->_final_uri);
			XF_Functions::arrayDeleteEmptyValue($tmp, TRUE);
			$count = count($tmp);
			$isDefaultModule = FALSE;
			$setController = false;
			
			$binds = $this->getAllBindModuleName();
			
			$domainModuleName = null;
			if (key_exists($this->_request_http_host, $this->_bin_domain))
				$domainModuleName =  $this->_bin_domain[$this->_request_http_host];
     
           	if ($domainModuleName != null) 
           	{
                $isDefaultModule = TRUE;
           		$this->_request->setModule($domainModuleName);
           		//2013-10-17
           		if ($count >= 1 && is_array($this->_bin_domain_allow_modules) && isset($this->_bin_domain_allow_modules[$domainModuleName]) && in_array($tmp[0], $this->_bin_domain_allow_modules[$domainModuleName]))
           		{
           			//是否为自身当前绑定应用的控制器
           			if (!file_exists(APPLICATION_PATH.'/modules/'.$domainModuleName.'/'.ucfirst($tmp[0]).'Controller.php'))
           			{
           				if (is_dir(APPLICATION_PATH.'/modules/'.$tmp[0]))
           				{
           					$isDefaultModule = FALSE;
           					$this->_request->setModule($tmp[0]);
           				}
           			}
           		}
           	}
           	elseif ($count == 1)
           	{
           		if (strtolower($tmp[0]) == 'index')
           		{
           			throw new XF_Exception('Access denied', 404);
           		}
           		
           		//是否为默认module中的控制器
           		if (is_file(APPLICATION_PATH.'/controllers/'.ucfirst($tmp[0]).'Controller.php'))
           		{
           			$setController = $isDefaultModule = TRUE;
           			$this->_request->setModule('default')->setController($tmp[0]);
           		}
           		//是否是一个module，并且不是一个被域名绑定的模块
           		elseif (is_dir(APPLICATION_PATH.'/modules/'.$tmp[0].'/controllers/') && !in_array($tmp[0], $binds))
           		{
           			$this->_request->setModule($tmp[0]);
           		}
           		else 
           			$this->_request->setModule('unknown');
           			
           		return;
           	}
           	else
            {	
                //是否存在此Module
                if (isset($tmp[1]) && is_file(APPLICATION_PATH.'/modules/'.$tmp[0].'/controllers/'.ucfirst($tmp[1]).'Controller.php') && !in_array($tmp[0], $binds))
                {
                	 $this->_request->setModule($tmp[0]);
                }
                else
                {
                    //是否为默module中的控制器(controller)
                    if (is_file(APPLICATION_PATH.'/controllers/'.ucfirst($tmp[0]).'Controller.php'))
                    {
                        $setController = $isDefaultModule = TRUE;
                        $this->_request->setModule('default')->setController($tmp[0]);
                    }
                    else
                        $this->_request->setModule('unknown');
                }
            }
            $index = 0;
            if (!$isDefaultModule)
                $index++;

            if ($setController == false)
                $this->_request->setController(isset($tmp[$index]) ? $tmp[$index] : 'index');
            $this->_request->setAction(isset($tmp[$index+1]) ? $tmp[$index+1] : 'index');

            //分析参数列表
            if (isset($tmp[$index+2]))
            {
                $params = array_slice($tmp, $index+2);
                if ($this->_save_uri_value === true)
                {
                	for ($i =0; $i < count($params); $i++){
                		$this->_request->setParam('$'.($i+1), urldecode($params[$i]));
                	}
                }
                else 
                {
	                for ($i = 0; $i <= count($params); $i+=2)
	                {	
	                    if (isset($params[$i]) && !is_numeric($params[$i]))
	                    {
	                        $this->_request->setParam($params[$i], isset($params[$i+1]) ? urldecode($params[$i+1]) : '');
	                    }
	                }
                }
            }
		}
		else
			$this->_setDefault();
	}
	
	/**
	 * 设置默认的模块、控制器、动作名
	 * @access private
	 * @return void
	 */
	private function _setDefault()
	{
		$this->_request->setModule('default')->setController('index')->setAction('index');
 
		if (key_exists($this->_request_http_host, $this->_bin_domain))
		{
			$this->_request->setModule($this->_bin_domain[$this->_request_http_host]);
		}
	}
	
	
	/**
	 * 分析传统请求方式的参数
	 * @access private 
	 * @return void
	 */
	private function _readOldRequestParams()
	{
		if ($this->_old_request_params_string !='')
		{
			$tmp = explode('&', $this->_old_request_params_string);
			foreach ($tmp as $t)
			{
				$_tmp = explode('=', $t);
				//GET 数组参数
				$_tmp[0] = str_replace('%5B%5D', '[]', $_tmp[0]);
				$_tmp[0] = str_replace('%5b%5d', '[]', $_tmp[0]);
				$this->_request->setParam($_tmp[0], isset($_tmp[1]) ? urldecode($_tmp[1]) : NULL);
			}
		}
	}
} 