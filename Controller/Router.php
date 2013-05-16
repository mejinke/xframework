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
	private $_bin_domain;
	
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
	 * 获得最终将要处理的URI地址
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
		
        $request_uri = explode('?', $request_uri);
        $this->_final_uri = $request_uri[0];
		$this->_old_request_params_string = isset($request_uri[1]) ? $request_uri[1] : '';//传统请求方式的参数
		$this->_request_http_host = $_SERVER['HTTP_HOST'];
	}
	
	
	
	/**
	 * 模块域名绑定
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
	 * 启动路由器
	 * @return void
	 */
	public function run()
	{
		$this->_request = XF_Controller_Request_Http::getInstance();
		$this->_getFinalUri();
		if (!$this->_validateRewrite())
		{
			$this->_getModule();
		}
		$this->_readOldRequestParams();
	}
	
	
	/**
	 * 验证路由重写规则
	 * @return bool　是否成功匹配到自定义的路由规则
	 */
	private function _validateRewrite()
	{
		$status = FALSE;
		krsort($this->_rewriteIndex);
		foreach ($this->_rewriteIndex as $name)
		{
			//记录准备要匹配的规则名称
            if(XF_Config::getInstance()->getSaveDebug())
			    XF_DataPool::getInstance()->addHash('DEBUG', 'NowMatchRewriteName', $name);

			if (TRUE === $this->_rewrites[$name]->match($this->_request->getServer('REQUEST_URI')))
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
            foreach ($this->_rewriteIndex as $name)
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

		return $status;
	}
	
	private function _getModule()
	{
		if ($this->_final_uri != '' && $this->_final_uri != '/' && $this->_final_uri != '/index.php')
		{
			$tmp = explode('/', $this->_final_uri);
			XF_Functions::arrayDeleteEmptyValue($tmp, TRUE);
			$count = count($tmp);
			$defaultModule = FALSE;
			$setController = false;

			$domainModuleName = null;
			if (is_array($this->_bin_domain) && key_exists($this->_request_http_host, $this->_bin_domain))
				$domainModuleName =  $this->_bin_domain[$this->_request_http_host];
           
           	if ($domainModuleName != null) 
           	{
                $defaultModule = TRUE;
           		$this->_request->setModule($domainModuleName);
           	}	
            elseif ($count >= 1)
            {
            	
                //是否存在此Module
                if (isset($tmp[1]) && is_file(APPLICATION_PATH.'/modules/'.strtolower($tmp[0]).'/controllers/'.ucfirst($tmp[1]).'Controller.php'))
                {
                	 $this->_request->setModule($tmp[0]);
                }
                else
                {
                    //是否为默module中的控制器(controller)
                    if (is_file(APPLICATION_PATH.'/controllers/'.ucfirst($tmp[0]).'Controller.php'))
                    {
                        $setController = TRUE;
                        $defaultModule = TRUE;
                        $this->_request->setModule('Default')->setController($tmp[0]);
                    }
                    else
                        $this->_request->setModule('Unknown');
                }

            }
            $index = 0;
            if (!$defaultModule)
                $index ++;

            if ($setController == false)
                $this->_request->setController(isset($tmp[$index]) ? $tmp[$index] : 'index');
            $this->_request->setAction(isset($tmp[$index+1]) ? $tmp[$index+1] : 'index');

            //分析参数列表
            if (isset($tmp[$index+2]))
            {
                $params = array_slice($tmp, $index+2);
                if ($this->_saveUriValue === true)
                {
                	for ($i =0; $i < count($params); $i++){
                		$this->_request->setParam('$'.($i+1), urldecode($params[$i]));
                	}
                }
                for ($i = 0; $i <= count($params); $i+=2)
                {	
                    if (isset($params[$i]) && !is_numeric($params[$i]))
                    {
                        $this->_request->setParam($params[$i], isset($params[$i+1]) ? urldecode($params[$i+1]) : '');
                    }
                }
            }

		}
		else
			$this->_setDefault();
	}
	
	/**
	 * 设置默认
	 * @return void
	 */
	private function _setDefault()
	{
		$this->_request->setModule('Default')->setController('index')->setAction('index');
	}
	
	
	/**
	 * 分析传统请求方式的参数
	 */
	private function _readOldRequestParams()
	{
		if ($this->_old_request_params_string !='')
		{
			$tmp = explode('&', $this->_old_request_params_string);
			foreach ($tmp as $t)
			{
				$_tmp = explode('=', $t);
				$this->_request->setParam($_tmp[0], isset($_tmp[1]) ? urldecode($_tmp[1]) : null);
			}
		}
	}
} 