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
 * @desc Request请求相关操作抽象类
 * @author jingke
 */
abstract class XF_Controller_Request_Abstract
{
	
	/**
	 * 模块请求参数名称
	 * @var string
	 */
	protected $_module_key = '__module';
	
	/**
	 * 当前请求的模块
	 * @var string
	 */
	protected $_module;
	
	/**
	 * 控制器请求的参数名称
	 * @var string
	 */
	protected $_controller_key = '__controller';
	
	/**
	 * 请求的控制器
	 * @var string
	 */
	protected $_controller;
	
	/**
	 * Action动作请求的参数名称
	 * @var string
	 */
	protected $_action_key = '__action';
	
	/**
	 * Action动作名称
	 * @var string
	 */
	protected $_action;

	/**
	 * 所有的请求参数列表
	 * @var array 
	 */
	protected $_params = array();

    /**
     * POST请求参数列表
     * @var array
     */
    protected $_post_params = null;

	/**
	 * 获取模块名称
	 * @access public
	 * @return string
	 */
	public function getModule()
	{
		if ($this->_module == NULL)
			$this->_module = $this->getParam($this->_module_key);
		return $this->_module;
	}
	
	/**
	 * 设置Module
	 * @access public
	 * @param string $module_name
	 * @return XF_Controller_Request_Abstract
	 */
	public function setModule($module_name)
	{
		$this->_module = strtolower($module_name);
		$this->setParam($this->_module_key, $this->_module);
		return $this;
	}
	
	/**
	 * 获取当前请求的控制器
	 * @access public
	 * @return string
	 */
	public function getController()
	{
		if ($this->_controller == NULL)
		{
			$this->_controller = $this->getParam($this->_controller_key);
		}
		return $this->_controller;
	}
	
	/**
	 * 设置控制器
	 * @access public
	 * @param string $controller_name
	 * @return XF_Controller_Request_Abstract
	 */
	public function setController($controller_name)
	{
        $this->_controller = $controller_name;
		$this->setParam($this->_controller_key, $controller_name);
		return $this;
	}
	
	/**
	 * 获取动作Action
	 * @access public
	 * @return string
	 */
	public function getAction()
	{
		if ($this->_action == NULL)
		{
			$this->_action = $this->getParam($this->_action_key);
		}
		return $this->_action;
	}
	
	/**
	 * 设置动作Action
	 * @access public
	 * @param string $action_name
	 * @return XF_Controller_Request_Abstract
	 */
	public function setAction($action_name)
	{
        $this->_action = $action_name;
		$this->setParam($this->_action_key, $action_name);
		return $this;
	}
	
	/**
	 * 获取指定的参数，不存在则返回 $default
	 * @access public
	 * @param string $key
	 * @param mixed $default
     * @return mixed
	 */
	public function getParam($key, $default = null)
	{
		$key = (string) $key;
		if (isset($this->_params[$key]))
			return $this->_params[$key];
		return $default;
	}
	
	/**
	 * 获取所有参数资料
	 * @access public
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * 获取所有非系统的参数资料
	 * @access public
	 * @param bool $getPostParams　是否同时获取POST参数
	 * @return array
	 */
	public function getCustomParams($getPostParams = true)
	{
		$params = $this->_params;
		unset($params[$this->_module_key]);
		unset($params[$this->_controller_key]);
		unset($params[$this->_action_key]);
		
		if ($getPostParams == false && is_array($this->_post_params))
		{
			foreach ($this->_post_params as $k => $v)
			{
				if (isset($params[$k]))
					unset($params[$k]);
			}
		}
		return $params;
	}
	
	/**
	 * 设置一个请求参数，如果设置的值为NULL,并且存在此参数key时，将删除该参数.
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param bool $filtration 是否过滤内容 默认为true
	 * @return XF_Controller_Request_Abstract
	 */
	public function setParam($key, $value, $filtration = true)
	{
		if (null === $value && isset($this->_params[$key]))
			unset($this->_params[$key]);
		elseif (null !== $value) 
            $this->_params[$key] = $filtration ? XF_String::clearJs($value) : $value;
        return $this;
	}
	
	/**
	 * 批量设置参数.如果设置的值为NULL,并且存在此参数key时，将删除该参数.
	 * @access public
	 * @param array $array
	 * @return XF_Controller_Request_Abstract
	 */
	public function setParams(array $array)
	{
		$this->_params = $this->_params + (array) $array;

        foreach ($this->_params as $key => $value) 
        {
            if (null === $value) 
                unset($this->_params[$key]);
        }
        return $this;
	}
	
	/**
	 * 清除所有参数
	 * @access public
	 * @return XF_Controller_Request_Abstract
	 */
	public function clearParams()
    {
        $this->_params = array();
        return $this;
    }
    
    
    /**
     * 清除参数
     * @access public
     * @param string $name 参数名称
     * @return XF_Controller_Request_Abstract
     */
	public function clearParam($name)
    {
    	if (isset($this->_params[$name]))
    		unset($this->_params[$name]);
        return $this;
    }
}