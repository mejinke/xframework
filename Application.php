<?php
define('APP_START_TIME', microtime(true));
/**
 *
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-12-15
 * -+-----------------------------------
 *
 * @desc 应用程序入口类
 * @author jingke
 */
class XF_Application
{
	
	/**
     * 当前实例
     * @var XF_Application
     */
    private static $_instance = null;
    
    /**
     * Bootstrap
     * @var XF_Application_Bootstrap
     */
    protected $_bootstrap;

    /**
     * 当前是否为命令行模式运行
     * @var bool
     */
    private $_is_command_line = FALSE;
    
    /**
     * 加载所有文件总时间
     * @var int
     */
    private $_load_file_time = 0;
    
 	private function __construct(){}
    private function __clone(){}
    
    /**
     * 获取实例
     * @access public
     * @param bool $isCommandLine 是否为命令行模式? 默认为 FALSE
     * @param bool $is_compile_model 是否编译框架代码再运行？默认为TRUE
     * @return XF_Application
     */ 
	public static function getInstance($isCommandLine = FALSE, $is_compile_model = TRUE)
    {
        if (self::$_instance === null)
        {
            self::$_instance = new self();
            self::$_instance->_init($isCommandLine, $is_compile_model);
        }
        return self::$_instance;
    }
    
    /**
     * 初始化
     * @access private
     * @return void
     */
    private function _init($isCommandLine, $is_compile_model)
    {
    	$this->_is_command_line = $isCommandLine;
    	if ($isCommandLine === FALSE)
    	{
	    	$this->_runTime($is_compile_model);
	    	//设定错误和异常处理
	        register_shutdown_function(array('XF_Application','fatalError'));
	        set_error_handler(array('XF_Application','appError'));
	        set_exception_handler(array('XF_Application','appException'));
	        XF_Loader_Autoloader::getInstance();
	        $this->_setPHPSettings(XF_Config::getInstance()->getPHPSettings());
    	}
    	else
    	{
    		$this->_runCommandLineTime($is_compile_model);
    		XF_Loader_Autoloader::getInstance();
    	}
    }

    /**
     * 脚本解析错误的处理方法
     * @param public
     * @return void
     */
	public static function fatalError()
	{
	if ($e = error_get_last()) 
	{
		$env = XF_Config::getInstance()->getEnvironmental();
		$req = XF_Controller_Request_Http::getInstance();
		$url = $req->getMethod().' "'.$req->getRequestUrl().'"';
	    switch($e['type'])
	    {
	      case E_ERROR:
	      case E_PARSE:
	      case E_CORE_ERROR:
	      case E_COMPILE_ERROR:
	      case E_USER_ERROR:
	        ob_end_clean();
	        $message = $url."\n".'ERROR: '.$e['message']. ' in '.$e['file'].' on line '.$e['line'];
	        if ($env == 'development')
	        {
	         	exit($message);
	        }
	        else
	        {
	        	XF_Functions::writeErrLog($message);
	          	XF_Controller_Plugin_Manage::getInstance()->exception(XF_Controller_Request_Http::getInstance(), new XF_Exception($e['message']));
	        }
	        break;
	    }
	  }
	}
    
	/**
	 * 普通错误，警告
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @throws XF_Exception
	 */
	public static function appError($errno, $errstr, $errfile, $errline)
	{
		$env = XF_Config::getInstance()->getEnvironmental();
		$req = XF_Controller_Request_Http::getInstance();
		$url = $req->getMethod().' "'.$req->getRequestUrl().'"';
		switch ($errno) 
		{
			case E_ERROR:
		    case E_PARSE:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_USER_ERROR:
			    $message = $url."\n".'ERROR: '.$errstr. ' in '.$errfile.' on line '.$errline;
		        if ($env == 'development')
		        {
		          	exit($message);
		        }
		        else
		        {
		        	XF_Functions::writeErrLog($message);
		          	XF_Controller_Plugin_Manage::getInstance()->exception(XF_Controller_Request_Http::getInstance(), new XF_Exception($e['message']));
		        }
			    break;
		    /*case E_STRICT:
		    case E_USER_WARNING:
		    case E_USER_NOTICE:
		    default:
			    $message = 'ERROR: ['.$errno.']'.$errstr. ' in '.$errfile.' on line '.$errline;
				XF_Functions::writeErrLog($message);
		        if ($env == 'development')
		        {
		          echo '<br/>'.$message;
		        }
			    break;*/
		}
	}
    
    /**
     * 全局异常处理
     * @param Exception $e
     * @throws Exception
     * @throws XF_Exception
     */
	public static function appException(Exception $e)
    {
    	$req = XF_Controller_Request_Http::getInstance();
    	$message = $req->getMethod().' "'.$req->getRequestUrl().'" '.$e->getMessage()."\n";
    	XF_Functions::writeErrLog($message.$e->getTraceAsString());
		echo $e;
    }
    
     /**
     * 是否命令行状态
     * @access public
     * @return bool
     */
    public function isCommandLine()
    {
    	return $this->_is_command_line;	
    }
    
    /**
     * 获取Bootstrap
     * @access public
     * @return XF_Application_Bootstrap
     */
    public function getBootstrap()
    {
        if (NULL === $this->_bootstrap)
        {
            $this->_bootstrap = new XF_Application_Bootstrap();
        }
        return $this->_bootstrap;
    }

    /**
     * 设置Bootstrap
     * @access public
     * @param string $bootstrapClassName bootstrap Class name.
     * @throws XF_Application_Exception
     * @return XF_Application
     */
    public function setBootstrap($bootstrapClassName = null)
    {
        if (NULL === $bootstrapClassName)
            return $this;

        $file = APPLICATION_PATH.'/bootstraps/'.ucfirst($bootstrapClassName).'.php';
        if(is_file($file))
        {
            require $file;
            $bootstrapClassName = $bootstrapClassName.'Bootstrap';
            if(!class_exists($bootstrapClassName, false))
                throw new XF_Application_Exception($bootstrapClassName.' 不存在');

            $this->_bootstrap = new $bootstrapClassName($this);

            if (!$this->_bootstrap instanceof XF_Application_Bootstrap)
                throw new XF_Application_Exception($bootstrapClassName.' 不是一个有效的对象');

        }
        else
            throw new XF_Application_Exception('Bootstrap 不存在 '.$file);

        return $this;
    }

    /**
     * 加载框架运行环境
     * @param bool $is_compile_model 是否编译框架代码再运行？默认为TRUE
     * @return void
     */
    private function _runTime($is_compile_model = TRUE)
    {
    	if ($is_compile_model !== TRUE)
    	{
    		require XF_PATH.'/Loader/Autoloader.php';
    		return;
    	}
    	$file = TEMP_PATH.'/RunTime.php';
    	if (!is_file($file))
    	{
	    	$cores = array(
	    		'Loader/Autoloader.php',
		    	'DataPool.php',
				'Functions.php',
				'Config.php',
				'Application/Bootstrap.php',
				'Controller/Front.php',
	    		'Controller/Router/Interface.php',
	    		'Controller/Router/Abstract.php',
				'Controller/Router.php',
				'Controller/Request/Abstract.php',
				'Controller/Plugin/Manage.php',
				'Controller/Request/Http.php',
				'Controller/Plugin/Abstract.php',
				'View.php',
	    		'File.php',
				'String.php',
				'Controller/Abstract.php',
				'Controller/Interface.php',
	    		'Cache/Interface.php',
	    		'Cache/Abstract.php',
				'Cache/SECache.php',
	    		'Db/Table/Abstract.php',
				'Db/Tool.php',
				'Db/Table/ValidateRule.php',
				'Db/Table/Select/Interface.php',
	    		'Db/Table/Select/Abstract.php',
				'Db/Table/Select/Mysql.php',
				'Db/Config/Interface.php',
	    		'Db/Drive/Abstract.php',
				'Db/Drive/Mysql.php',
				'View/Helper.php',
				'View/Helper/Header/Title.php',
				'View/Helper/Header/Link.php',
				'View/Helper/Header/Meta.php',
				'View/Helper/Header/Stylesheet.php',
				'View/Helper/Header/Script.php',
	    	);
	    	
	    	$phpcode = '<?php';
	    	foreach ($cores as $c)
	    	{
	    		$code = php_strip_whitespace(XF_PATH.'/'.$c);
	    		$phpcode .= str_replace('<?php', '', $code);
	    	}
	    	file_put_contents($file, $phpcode);
    	}
    	
    	require $file;
    }
    
	/**
     * 加载命令行下的框架运行环境
     * @param bool $is_compile_model 是否编译框架代码再运行？默认为TRUE
     * @return void
     */
    private function _runCommandLineTime($is_compile_model = TRUE)
    {
    	
    	if ($is_compile_model === TRUE)
    	{
    		require XF_PATH.'/Loader/Autoloader.php';
    		return;
    	}
    	$file = TEMP_PATH.'/~RunTime.php';
    	if (!is_file($file))
    	{
	    	$cores = array(
	    		'Loader/Autoloader.php',
		    	'DataPool.php',
				'Functions.php',
				'Config.php',
	    		'File.php',
				'String.php',
	    		'Cache/Interface.php',
	    		'Cache/Abstract.php',
				'Cache/SECache.php',
	    		'Db/Table/Abstract.php',
				'Db/Tool.php',
				'Db/Table/ValidateRule.php',
				'Db/Table/Select/Interface.php',
	    		'Db/Table/Select/Abstract.php',
				'Db/Table/Select/Mysql.php',
				'Db/Config/Interface.php',
				'Db/Config.php',
	    		'Db/Drive/Abstract.php',
				'Db/Drive/Mysql.php'
	    	);
	    	
	    	$phpcode = '<?php';
	    	foreach ($cores as $c)
	    	{
	    		$code = php_strip_whitespace(XF_PATH.'/'.$c);
	    		$phpcode .= str_replace('<?php', '', $code);
	    	}
	    	file_put_contents($file, $phpcode);
    	}
    	
    	require $file;
    }
    
    /**
     * 添加文件加载时间
     * @param int $number
     * @return void
     */
    public function addLoadFileTime($number)
    {
    	$this->_load_file_time += floatval($number);
    }
    
    /**
     * 获取文件加载的总时间
     * @return float
     */
    public function loadFileTime()
    {
    	return $this->_load_file_time;
    }
    
    /**
     * 启动框架
     * @return void
     */
    public function run()
    {
    	XF_DataPool::getInstance()->add('RunApplication', sprintf("%.6f", microtime(true) - APP_START_TIME));
    	//如果是命令行模式则直接返回
    	if ($this->_is_command_line === TRUE) return;
        $this->getBootstrap()->run();
    }

    /**
     * 设置php环境
     * @access private
     * @param array $var
     * @return void
     */
    private function _setPHPSettings($var)
    {
        if(is_array($var))
        {
            foreach($var as $key => $val)
            {
                ini_set($key, $val);
            }
        }
    }
}