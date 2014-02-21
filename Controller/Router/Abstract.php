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
 * @desc 框架路由器抽象类
 * @author jingke
 */
abstract class XF_Controller_Router_Abstract implements XF_Controller_Router_Interface
{
	/**
	 * 参数列表
	 * @var array
	 */
	protected $_invoke_params = array();
	
	/**
	 * 是否保存URI的各个值
	 * @var bool
	 */
	protected $_save_uri_value = false;
	
	/**
	 * 路由重写规则对象列表
	 * @var array
	 */
	protected $_rewrites = array();

	/**
	 * 路由规则调用顺序索引
	 * @var array
	 */
	protected $_rewrite_index = array();
	
	/** 
	 * 禁用默认路由的Action列表
	 * @var array
	 */
	protected $_disabled_default_routes;
	
	/**
	 * 添加一个参数
	 * @param string $name
	 * @param mixed $value
	 * @return XF_Controller_Router_Abstract
	 */
	public function setParam($name, $value)
    {
        $name = (string) $name;
        $this->_invoke_params[$name] = $value;
        return $this;
    }

    /**
     * 批量设置参数
     * @param array $params
     * @return XF_Controller_Router_Abstract
     */
    public function setParams(array $params)
    {
        $this->_invoke_params = array_merge($this->_invoke_params, $params);
        return $this;
    }

    /**
     * 获取参数
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if(isset($this->_invoke_params[$name])) 
            return $this->_invoke_params[$name];
        
        return null;
    }

    /**
     * 获取所有的参数
     * @return array
     */
    public function getParams()
    {
        return $this->_invoke_params;
    }

    /**
     *　清除参数
     * @param null|string|array 参数键名或键名数组,如果等于null将删除所有参数
     * @return XF_Controller_Router_Abstract
     */
    public function clearParams($name = null)
    {
        if (null === $name)
            $this->_invoke_params = array();
        elseif (is_string($name) && isset($this->_invoke_params[$name]))
            unset($this->_invoke_params[$name]);
        elseif (is_array($name)) 
        {
            foreach ($name as $key) 
            {
                if (is_string($key) && isset($this->_invoke_params[$key])) 
                {
                    unset($this->_invoke_params[$key]);
                }
            }
        }
        return $this;
    }
    
    
    /**
     * 设置保存URI值的开关状态
     * @param bool $state
     * @return XF_Controller_Router_Abstract
     */
    public function saveUriValue($state)
    {
    	$this->_save_uri_value = $state;
    	return $this;
    }
    
    /**
     * 禁用Action的默认路由【将无法通过默认路由规则访问】
     * @param string $module 模块名
     * @param string $controller 控制器名
     * @param string $action 动作名
     * @return XF_Controller_Router_Abstract
     */
    public function disabledDefaultRouter($module, $controller, $action)
    {
    	$this->_disabled_default_routes[strtolower($module.$controller.$action)] = TRUE;
    	return $this;
    }
    
    /**
     * 添加路由器重写规则
     * @param string $name 名称
     * @param XF_Controller_Router_Rewrite_Interface $rewrite
     * @param int $index 执行优先级，数值越大将最先被执行
     * @return XF_Controller_Router_Abstract
     */
    public function addRewrite($name, XF_Controller_Router_Rewrite_Abstract $rewrite, $index = NULL)
    {
    	$name = (string) $name;
    	if ($index === NULL)
    	{
    		if (count($this->_rewrite_index) === 0)
    			$this->_rewrite_index[0] = $name;
    		else 
    		{
    			$array = array_keys($this->_rewrite_index);
    			$index = end($array);
    			$this->_rewrite_index[$index+1] = $name;
    		}
    	}
    	else 
    	{
            XF_Functions::arrayInsert($this->_rewrite_index, $index, $name);
    	}
        $this->_rewrites[$name] = $rewrite;
        
        if ($rewrite->isDisableDefaultRoute())
        {
        	$m = $rewrite->getMca();
        	$this->disabledDefaultRouter($m['module'], $m['controller'], $m['action']);
        }
        return $this;
    }
    
    /**
     *　清除路由规则
     * @param null|string|array 参数键名或键名数组,如果等于null将删除所有路由规则
     * @return XF_Controller_Router_Abstract
     */
    public function clearRewrites($name = null)
    {
    	if (null === $name)
    	{
    		$this->_rewrites = $this->_rewrite_index = array();
    	}
        elseif (is_string($name) && isset($this->_rewrites[$name]))
        {
        	XF_Functions::arrayDeleteFromValue($this->_rewrite_index, $name);
            unset($this->_rewrites[$name]);
        }
        elseif (is_array($name)) 
        {
            foreach ($name as $_name)
            {
                if (is_string($_name) && isset($this->_rewrites[$_name]))
                {
                    XF_Functions::arrayDeleteFromValue($this->_rewrite_index, $_name);
                    unset($this->_rewrites[$_name]);
                }
            }
        }
        return $this;
    }
	
}