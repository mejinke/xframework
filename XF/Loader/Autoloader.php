<?php
require_once XF_PATH.'/Loader/Exception.php';
/**
 *
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-12-29
 * -+-----------------------------------
 *
 * @desc 自动类加载
 * @author jingke
 */
class XF_Loader_Autoloader
{
    /**
     * 当前实例
     * @var XF_Loader_Autoloader
     */
    private static $_instance = null;

    private function __construct(){}
    private function __clone(){}

    /**
     * 获取Autoloader　实例
     * @return XF_Loader_Autoloader
     */
    public static function getInstance()
    {
        if (self::$_instance == NULL)
        {
            self::$_instance = new self();
            //注册自动加载
            spl_autoload_register(array(self::$_instance, 'autoload'));
        }
        return self::$_instance;
    }

    /**
     * 动态加载将要调用的类文件
     * @access public
     * @param string $class
     * @return mixed
     */
    public function autoload($class)
    {
        if ($file = $this->_getFileName($class))
        {
            if (is_file($file))
                require $file;
        }
    }

    /**
     * 获取文件位置
     * @access public
     * @param string $class
     * @return mixed
     */
    protected  function _getFileName($class)
    {
        $class = $this->_validateFolderName($class);
        $filename = NULL;
        $tmp = explode('_', $class);
        //当前加载的类，类型 Application_Model_Test  Application_Model_Table_Test
        $classType = 'xf';

        switch($tmp[0])
        {
            case 'XF':
                unset($tmp[0]);
                $filename = XF_PATH.'/'.implode($tmp, '/').'.php';
                break;
            case 'Application':
                $classType = 'application';
                $php = strtolower(end($tmp));
                $filename = APPLICATION_PATH.'/../'.str_replace($php.'.php',end($tmp).'.php', strtolower(implode($tmp, '/')).'.php');
                break;
            case 'Layout':
            	$classType = 'layout';
                $filename = APPLICATION_PATH.'/layouts/'.$tmp[1].'.php';
                break;
        }
      	if ($filename == null)
      	{
	    	if (strpos($tmp[1], 'Controller'))
			{
				$classType = 'controller';
				$filename = APPLICATION_PATH.'/modules/'.strtolower($tmp[0]).'/controllers/'.$tmp[1].'.php';
			}
			elseif (count($tmp) == 1 && strpos($tmp[0], 'Controller'))
			{
				$classType = 'controller';
				$filename = APPLICATION_PATH.'/controllers/'.$tmp[0].'.php';
			}
			else 
			{
				$classType = 'other';
				$php = strtolower(end($tmp));
	
				$appName = strtolower(substr($tmp[0], 0,1));
				$appName = substr($tmp[0], 1);
				
				$filename = str_replace($php.'.php',end($tmp).'.php', strtolower(implode($tmp, '/')).'.php');
				$filename = str_replace(strtolower($appName),$appName, $filename);
				$filename = APPLICATION_PATH.'/modules/'.$filename;
			}
      	}


        //如果不是加载的框架文件，且当前应用程序中不存在指定的类文件时，尝试加载其它应用程序中的类文件 2012-11-07
        //配置文件中配置:include_application_paths
        if ($classType != 'xf' && !is_file($filename))
        {
            $applicationPaths = XF_Config::getInstance()->getIncludeApplicationPaths();
            if (is_array($applicationPaths))
            {
                foreach ($applicationPaths as $appPath)
                {
                    $filename = str_replace(APPLICATION_PATH, realpath($appPath).'/application', $filename);
                    if (is_file($filename))
                        break;
                }
            }
        }
        return $filename;
    }

    /**
     * 获取正确的目录名称数组
     * @param array $var
     * @return string
     */
    protected function _validateFolderName($var)
    {
        $_vars = array(
            'Model' => 'models',
            'Table' => 'table',
            'Layout' => 'layouts',
        	'Plugin' => 'plugins'
        );
        $tmp = explode('_', $var);
        $count = count($tmp);
        if ($tmp[0] != 'XF' && $count >= 3)
        {
            for ($i=1; $i<$count; $i++)
            {
                $tmp[$i] = str_replace(array_keys($_vars), array_values($_vars), $tmp[$i]);
            }
        }

        return implode('_', $tmp);
    }

}