<?php
/**
 *
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-12-11
 * -+-----------------------------------
 *
 * @desc 框架异常处理基础类
 * @author jingke
 */
class XF_Exception extends Exception
{

    public function __construct($message, $code = 500)
    {
        parent::__construct($message, (int) $code);
    }

    public function __toString()
    {
    	$env = XF_Config::getInstance()->getEnvironmental();
    	
    	if ($env == 'development')
    	{
    		if ($this->code == 404)
    		{
    			header('HTTP/1.1 404 Not Found');
    		}
    		else 
    		{
    			header('HTTP/1.1 500 Internal Server Error');
    		}
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>';
			echo '<title>Application Error</title>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
			echo '</head><body>';
	        echo '<pre>';
	        echo parent::__toString();
	        echo "\n\n\nRequest URL: ".XF_Controller_Request_Http::getInstance()->getRequestUrl()."\n";
	        echo "Params:\n";
	        print_r(XF_Controller_Request_Http::getInstance()->getParams());
	        echo "\nRewrites: \n";
	        $debug = XF_DataPool::getInstance()->get('DEBUG');
	        print_r($debug['Rewites']);
	        $_querys = XF_DataPool::getInstance()->get('Querys', array());
	        echo '<br/>Querys('.count($_querys).') Time:'.XF_DataPool::getInstance()->get('QueryTimeCount',0).'s<br/>' ;
	        print_r($_querys);
	        echo '<br/>ReadCache('.XF_DataPool::getInstance()->get('CacheTimeCount', 0).'s)<br/>';
			print_r(XF_DataPool::getInstance()->get('CacheTimeList', array()));
			echo '<br/>RunApplication:'.XF_DataPool::getInstance()->get('RunApplication').'s';
			echo '<br/>RunBootstrap:'.XF_DataPool::getInstance()->get('RunBootstrap').'s';
			echo '<br/>LoadFile:'.sprintf("%.5fs", XF_Application::getInstance()->loadFileTime());
	        echo '<br/>RunTime:'.sprintf('%.5f', microtime(true)-APP_START_TIME).'s';
	        echo '</pre>';
	        echo '</body></html>';
    	}
    	else 
    	{
    		$string = $title = '';
    		if ($this->code == 404)
	        {
	        	header('HTTP/1.1 404 Not Found'); 
	        	$title = 'Not Found';
	        	$string = '<h1 style="font-size:60px;">:( 404</h1>';
	        	$string.= '<p>您请求的网页不存在或已删除！<br/><br/><a href="/">返回</a></p>';
	        }
	        else
	        {
	        	header('HTTP/1.1 500 Internal Server Error'); 
	        	$title = 'Internal Server Error';
	        	$string = '<h1 style="font-size:60px;">:( 500</h1>';
	        	$string.= '<p>您请求的网页存在错误，请稍后再访问！<br/><br/><a href="/">返回</a></p>';
	        }
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>';
			echo '<title>'.$title.'</title>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
			echo '</head><body style="padding:10px;">';
	        echo $string;
	        echo '<p style="color:#999;font-size:14px;">URL：'.XF_Controller_Request_Http::getInstance()->getRequestUrl().'</p>';
	        echo '</body></html>';
    	}
    	
    	return '';
    }
}