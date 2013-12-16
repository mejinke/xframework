<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-12-21
 * -+-----------------------------------
 *
 * @desc 框架运行配置文件管理类
 * @author jingke
 */
class XF_Config
{
    /**
     * 当前实例
     * @var XF_Config
     */
    private static $_instance;

    /**
     * 当前网站标识
     * @var string
     */
    private $_app_key;
    
    /**
     * 网站根域名
     * @var string
     */
    private $_domain;
    
    /**
     * 其它配置文件资料
     * @var array
     */
    private $_configs = array();
    
    /**
     * 当前环境
     * @var string
     */
    private $_use_environmental;

    /**
     * php设置参数列表
     * @var array | null
     */
    private $_php_settings;
    
    /**
     * php设置参数列表
     * @var bool
     */
    private $_use_default_router;

    /**
     * 模板风格
     * @var string
     */
    private $_view_style;

    /**
     * 是否记录debug信息
     * @var bool
     */
    private $_save_debug = false;

    /**
     * 其他应用程序路径
     * @var array
     */
    private $_include_applications;
    
    /**
     * memcache缓存服务器列表
     * @var array
     */
    private $_memcaches;

    /**
     * 是否开启数据库服务器主从模式
     * @var bool
     */
    private $_db_open_slave;

    /**
     * 数据库主服务器列表
     * @var array
     */
    private $_db_server_masters;

    /**
     * 数据库从服务器列表
     * @var array
     */
    private $_db_server_slaves;

    /**
     * 数据库名称列表
     * @var string
     */
    private $_db_names;

    private function __construct(){}
    private function __clone(){}

    /**
     * 获取当前配置文件类实例
     * @access public
     * @return XF_Config
     */
    public static function getInstance()
    {
        if (self::$_instance === null)
        {
            self::$_instance = new self();
            self::$_instance->_load();
        }

        return self::$_instance;
    }

    /**
     * 获取网站标识
     * @access public
     * @return string
     */
    public function getAppKey()
    {
    	return $this->_app_key;
    }
    
    /**
     * 网站根域名
     * @access public
     * @return string
     */
    public function getDomain()
    {
    	return $this->_domain;
    }
    
    /**
     * 当前运行环境
     * @access public
     * @return string
     */
    public function getEnvironmental()
    {
    	return $this->_use_environmental;	
    }
    
    /**
     * 获取php设置
     * @access public
     * @return array|null
     */
    public function getPHPSettings()
    {
        return $this->_php_settings;
    }
    
    /**
     * 是否使用程序自带的路由规则
     * @access public
     * @return bool
     */
    public function isUseDefaultRouter()
    {
    	return $this->_use_default_router !== true ? false : true;
    }

    /**
     * 获取模式默认风格
     * @access public
     * @return string
     */
    public function getViewStyle()
    {
        return $this->_view_style;
    }

    /**
     * 获取是否记录debug的开关设置
     * @access public
     * @return bool
     */
    public function getSaveDebug()
    {
        return $this->_save_debug;
    }

    /**
     * 获取包含的其它应用程序路径列表
     * @access public
     * @return array
     */
    public function getIncludeApplicationPaths()
    {
        return $this->_include_applications;
    }
    
    /**
     * 获取所有的memcache缓存服务器列表
     * @access public
     * @return array
     */
    public function getMemcaches()
    {
    	return $this->_memcaches;
    }

    /**
     * 获取是否开启数据库服务器主从模式
     * @access public
     * @return bool
     */
    public function getDBOpenSlave()
    {
        return $this->_db_open_slave;
    }

    /**
     * 获取主数据库服务器列表
     * @access public
     * @return array
     */
    public function getDBServerMasters()
    {
        return $this->_db_server_masters;
    }

    /**
     * 获取从数据库服务器列表
     * @access public
     * @return array
     */
    public function getDBServerSlaves()
    {
        return $this->_db_server_slaves;
    }

    /**
     * 获取数据库名称列表
     * @access public
     * @return string
     */
    public function getDBNames()
    {
        return $this->_db_names;
    }
	
    /**
     * 加载其它的配置文件
     * @access public
     * @param string $configName
     * @return XF_Config
     */
   	public function load($configName)
   	{
   		if (isset($this->_configs[$configName])) return $this;
   		
   		$file = APPLICATION_PATH.'/configs/'.$configName.'.php';
   		if (!is_file($file)) throw new XF_Application_Exception('配置文件不存在：'.$configName);
   		$var = require $file;
   		if (is_array($var)) 
   		{
   			$this->_configs[$configName] = (object)$var;
   		}
   		else 
   		{
   			throw new XF_Application_Exception('配置文件格式不正确：'.$configName);
   		}
   		return $this;
   	}
   	
   	/**
   	 * 获取其它配置文件内容
   	 * @access public
   	 * @param string $name
   	 * @return mixed
   	 */
   	public function __get($name)
   	{
   		return isset($this->_configs[$name]) ? $this->_configs[$name] : NULL;
   	}
   	
    /**
     * 加载全局配置文件
     * @access private
     * @throws XF_Exception
     */
    private function _load()
    {
    	$file = APPLICATION_PATH.'/configs/application.php';
        if(!is_file($file))
            throw new XF_Exception('框架配置文件不存在!', 1000);

        $var = require $file;
		
        $this->_app_key = !empty($var['app_key']) && is_string($var['app_key']) ? $var['app_key'] : 'default'; 
        
        $this->_domain = !empty($var['domain']) && is_string($var['domain']) ? $var['domain'] : NULL; 
        
        //获取当前运行环境,默认为线上生产环境
        $this->_use_environmental = isset($var['use_environmental']) ? $var['use_environmental'] : 'production';
	
        //获取公用的php设置
        if(isset($var['common']['php_settings']))
            $this->_php_settings = $var['common']['php_settings'];

        //当前环境的php设置
        if(is_array($var[$this->_use_environmental]['php_settings']))
        {
            foreach($var[$this->_use_environmental]['php_settings'] as $key => $val)
            {
                $this->_php_settings[$key] = $val;
            }
        }

        //当前环境的模板风格，默认为"default"
        $this->_initConfigArguments($var, '_view_style', 'view_style', 'default');
        
        //使用程序默认的路由器
        $this->_initConfigArguments($var, '_use_default_router', 'use_default_router', true);
        
        //使用程序默认的路由器
        $this->_initConfigArguments($var, '_memcaches', 'memcache', array());
        
        $this->_setDatabasesConfig($var);
        $this->_setIncludeApplicationPath($var);
        $this->_setSaveDebug($var);
    }

    /**
     * 设置debug开关状态
     * @access private
     * @param array $config 配置文件内容数组
     */
    private function _setSaveDebug($config)
    {	
    	$this->_initConfigArguments($config, '_save_debug', 'save_debug');
    }
	
    /**
     * 设置包含的应用程序路径列表
     * @access private
     * @param array $config 配置文件内容数组
     */
    private function _setIncludeApplicationPath($config)
    {
    	$this->_initConfigArguments($config, '_include_applications', 'include_application_paths');
    }

    /**
     * 设置数据库相关配置
     * @access private
     * @param array $config 配置文件内容数组
     */
    private function _setDatabasesConfig($config)
    {
    	//设置是否开启主从模式
    	$this->_initConfigArguments($config, '_db_open_slave', 'database.open_slave');
    	//主服务器列表
    	$this->_initConfigArguments($config, '_db_server_masters', 'database.server.masters');
        //从服务器列表
        $this->_initConfigArguments($config, '_db_server_slaves', 'database.server.slaves');
        //数据库列表
        $this->_initConfigArguments($config, '_db_names', 'database.dbnames');
    }
    
    /**
     * 通用参数初始化
     * @access private
     * @param array $config 配置文件内容
     * @param string $variableName 当前config对象变量名
     * @param string $alias 配置文件参数名位置 
     * @param mixed $default 默认值
     */
    private function _initConfigArguments($config, $variableName, $alias, $default = null)
    {
    	$envVariable = array('count' => 0, 'obj' => $config);
    	$commVariable = array('count' => 0, 'obj' => $config);
    	$tmp = explode('.', $alias);
    	foreach ($tmp as $k => $s)
    	{
    		if ($k == 0)
    		{
    			if (isset($envVariable['obj'][$this->_use_environmental][$s]))
    			{
    				$envVariable['obj'] = $envVariable['obj'][$this->_use_environmental][$s];
    				$envVariable['count'] = 1;
    			}
    				
    			if (isset($commVariable['obj']['common'][$s]))
    			{
    				$commVariable['obj'] = $commVariable['obj']['common'][$s];
    				$commVariable['count'] = $k+1;
    			}	
    		}
    		else 
    		{
    			if (isset($envVariable['obj'][$s]))
    			{
    				$envVariable['obj'] = $envVariable['obj'][$s];
    				$envVariable['count'] = $k+1;
    			}
    				
    			if (isset($commVariable['obj'][$s]))
    			{
    				$commVariable['obj'] = $commVariable['obj'][$s];
    				$commVariable['count'] = $k+1;
    			}
    		}
    	}
    	
    	if ($envVariable['count'] == count($tmp))
    		$this->{$variableName} = $envVariable['obj'];
    	elseif ($commVariable['count'] == count($tmp))
    		$this->{$variableName} = $commVariable['obj'];
    	else
    		$this->{$variableName} = $default;	
    }
}
