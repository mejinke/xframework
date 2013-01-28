<?php
/**
 * 框架运行配置文件管理类
 * User: jinke
 * Date: 12-12-21
 * Time: 下午9:32
 * To change this template use File | Settings | File Templates.
 */
class XF_Config
{
    /**
     * 当前实例
     * @var XF_Config
     */
    private static $_instance;

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
     * 模板风格
     * @var string
     */
    private $_view_type;

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
     * @return XF_Config
     */
    public static function getInstance()
    {
        if (self::$_instance === null)
        {
            self::$_instance = new self();
            self::$_instance->loadConfig();
        }

        return self::$_instance;
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
     * 获取模式默认风格
     * @access public
     * @return string
     */
    public function getViewType()
    {
        return $this->_view_type;
    }

    /**
     * 获取是否记录debug的开关设置
     * @return bool
     */
    public function getSaveDebug()
    {
        return $this->_save_debug;
    }

    /**
     * 获取包含的其它应用程序路径列表
     * @return array
     */
    public function getIncludeApplicationPaths()
    {
        return $this->_include_applications;
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
     * 加载配置文件
     * @access private
     * @throws XF_Exception
     */
    private function loadConfig()
    {
        if(!is_file(APPLICATION_PATH.'/configs/application.php'))
            throw new XF_Exception('框架配置文件不存在!');

        $var = require APPLICATION_PATH.'/configs/application.php';

        //获取当前运行环境,默认为线上生产环境
        $this->_use_environmental = isset($var['use_environmental']) ? $var['use_environmental'] : 'production';

        if(isset($var['common']['php_settings']))
            $this->_php_settings = $var['common']['php_settings'];

        //线上生产环境
        if($this->_use_environmental === 'production' && isset($var['production']['php_settings']))
        {
            foreach($var['production']['php_settings'] as $key => $val)
            {
                $this->_php_settings[$key] = $val;
            }
        }

        //本地开发环境
        if($this->_use_environmental === 'development' && isset($var['development']['php_settings']))
        {
            foreach($var['development']['php_settings'] as $key => $val)
            {
                $this->_php_settings[$key] = $val;
            }
        }

        //模板风格默认为：default
        $this->_view_type = isset($var['default_view_type']) ? $var['default_view_type'] : 'default';

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
        $this->_save_debug = isset($config['common']['save_debug']) ? $config['common']['save_debug'] : false;
        $this->_save_debug = isset($config[$this->_use_environmental]['save_debug']) ? $config[$this->_use_environmental]['save_debug'] : $this->_save_debug;
    }


    /**
     * 设置包含的应用程序路径列表
     * @access private
     * @param array $config 配置文件内容数组
     */
    private function _setIncludeApplicationPath($config)
    {
        $this->_include_applications = isset($var['common']['include_application_paths']) ? $var['common']['include_application_paths'] : null;
        $this->_include_applications = isset($var[$this->_use_environmental]['include_application_paths']) ? $var['common']['include_application_paths'] : $this->_include_applications;
    }

    /**
     * 设置数据库相关配置
     * @access private
     * @param array $config 配置文件内容数组
     */
    private function _setDatabasesConfig($config)
    {
        //设置是否开启主从模式
        $this->_db_open_slave = isset($config['common']['database']['open_slave']) ? $config['common']['database']['open_slave'] : false;
        $this->_db_open_slave = isset($config[$this->_use_environmental]['database']['open_slave']) ? $config[$this->_use_environmental]['database']['open_slave'] : $this->_db_open_slave;

        //主服务器列表
        $this->_db_server_masters = isset($config['common']['database']['servers']['masters']) ? $config['common']['database']['servers']['masters'] :  null;
        $this->_db_server_masters = isset($config[$this->_use_environmental]['database']['servers']['masters']) ? $config[$this->_use_environmental]['database']['servers']['masters'] :  $this->_db_server_masters;

        //从服务器列表
        $this->_db_server_slaves = isset($config['common']['database']['servers']['slaves']) ? $config['common']['database']['servers']['slaves'] :  null;
        $this->_db_server_slaves = isset($config[$this->_use_environmental]['database']['servers']['slaves']) ? $config[$this->_use_environmental]['database']['servers']['slaves'] :  $this->_db_server_slaves;

        $this->_db_names = isset($config['common']['database']['dbnames']) ? $config['common']['database']['dbnames'] : null;
        $this->_db_names = isset($config[$this->_use_environmental]['database']['dbnames']) ? $config[$this->_use_environmental]['database']['dbnames'] : $this->_db_names;
    }
}
