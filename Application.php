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
    
 	private function __construct(){}
    private function __clone(){}
    
    /**
     * 获取实例
     * @access public
     * @param bool $isCommandLine 是否为命令行模式? 默认为 FALSE
     * @return XF_Application
     */ 
	public static function getInstance($isCommandLine = FALSE)
    {
        if (self::$_instance === null)
        {
            self::$_instance = new self();
            self::$_instance->_init($isCommandLine);
        }
        return self::$_instance;
    }
    
    /**
     * 初始化
     * @access private
     * @return void
     */
    private function _init($isCommandLine)
    {
    	$this->_is_command_line = $isCommandLine;
    	if ($isCommandLine === FALSE)
    	{
	    	$this->_runTime();
	    	//设定错误和异常处理
	        register_shutdown_function(array('XF_Application','fatalError'));
	        set_error_handler(array('XF_Application','appError'));
	        set_exception_handler(array('XF_Application','appException'));
	        XF_Loader_Autoloader::getInstance();
	        $this->_setPHPSettings(XF_Config::getInstance()->getPHPSettings());
    	}
    	else
    	{
    		$this->_runCommandLineTime();
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
	    switch($e['type'])
	    {
	      case E_ERROR:
	      case E_PARSE:
	      case E_CORE_ERROR:
	      case E_COMPILE_ERROR:
	      case E_USER_ERROR:
	        ob_end_clean();
	        $message = 'ERROR: '.$e['message']. ' in '.$e['file'].' on line '.$e['line'];
	        XF_Functions::writeErrLog($message);
	        if ($env == 'development')
	        {
	          exit($message);
	        }
	        else
	        {
	          throw new XF_Exception($message);
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
		switch ($errno) 
		{
			case E_ERROR:
		    case E_PARSE:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_USER_ERROR:
			    $message = 'ERROR: '.$errstr. ' in '.$errfile.' on line '.$errline;
		  		XF_Functions::writeErrLog($message);
		        if ($env == 'development')
		        {
		          exit($message);
		        }
		        else
		        {
		          throw new XF_Exception($message);
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
    	$env = XF_Config::getInstance()->getEnvironmental();
    	XF_Functions::writeErrLog($e->getTraceAsString());
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
     * @return void
     */
    private function _runTime()
    {
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
				'Db/Config.php',
				'Db/Drive/Interface.php',
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
     * @return void
     */
    private function _runCommandLineTime()
    {
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
				'Db/Drive/Interface.php',
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
     * 启动框架
     * @return void
     */
    public function run()
    {
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