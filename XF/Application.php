<?php
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
     * Bootstrap
     * @var XF_Application_Bootstrap
     */
    protected $_bootstrap;

    public function __construct()
    {
        require_once XF_PATH.'/Loader/Autoloader.php';
        XF_Loader_Autoloader::getInstance();

        $this->_setPHPSettings(XF_Config::getInstance()->getPHPSettings());
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
            $this->_bootstrap = new XF_Application_Bootstrap($this);
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

        $file = APPLICATION_PATH.'/bootstraps/'.$bootstrapClassName.'.php';
        if(is_file($file))
        {
            require $file;
            if(!class_exists($bootstrapClassName, false))
                throw new XF_Application_Exception('Bootstrap 不存在', 500);

            $this->_bootstrap = new $bootstrapClassName($this);

            if (!$this->_bootstrap instanceof XF_Application_Bootstrap)
                throw new XF_Application_Exception('Bootstrap 不是一个有效的对象', 500);

        }
        else
            throw new XF_Application_Exception('Bootstrap 不存在', 500);

        return $this;
    }

    /**
     * 启动框架
     */
    public function run()
    {
        $this->getBootstrap()->run();
    }

    /**
     * 设置php环境
     * @access private
     * @param array $var
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
