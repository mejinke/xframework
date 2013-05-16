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
	protected $_invokeParams = array();
	
	/**
	 * 是否保存URI的各个值
	 * @var bool
	 */
	protected $_saveUriValue = false;
	
	/**
	 * 路由重写规则对象列表
	 * @var array
	 */
	protected $_rewrites = array();
	

	/**
	 * 路由规则调用顺序索引
	 * @var array
	 */
	protected $_rewriteIndex = array();
	
	/**
	 * 添加一个参数
	 * @param string $name
	 * @param mixed $value
	 * @return XF_Controller_Router_Abstract
	 */
	public function setParam($name, $value)
    {
        $name = (string) $name;
        $this->_invokeParams[$name] = $value;
        return $this;
    }

    /**
     * 批量设置参数
     * @param array $params
     * @return XF_Controller_Router_Abstract
     */
    public function setParams(array $params)
    {
        $this->_invokeParams = array_merge($this->_invokeParams, $params);
        return $this;
    }

    /**
     * 获取参数
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if(isset($this->_invokeParams[$name])) 
            return $this->_invokeParams[$name];
        
        return null;
    }

    /**
     * 获取所有的参数
     * @return array
     */
    public function getParams()
    {
        return $this->_invokeParams;
    }

    /**
     *　清除参数
     * @param null|string|array 参数键名或键名数组,如果等于null将删除所有参数
     * @return XF_Controller_Router_Abstract
     */
    public function clearParams($name = null)
    {
        if (null === $name)
            $this->_invokeParams = array();
        elseif (is_string($name) && isset($this->_invokeParams[$name]))
            unset($this->_invokeParams[$name]);
        elseif (is_array($name)) 
        {
            foreach ($name as $key) 
            {
                if (is_string($key) && isset($this->_invokeParams[$key])) 
                {
                    unset($this->_invokeParams[$key]);
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
    	$this->_saveUriValue = $state;
    	return $this;
    }
    
    
    /**
     * 添加路由器重写规则
     * @param string $name 名称
     * @param XF_Controller_Router_Rewrite_Interface $rewrite
     * @param int $index 执行优先级，数值越大将最先被执行
     * @return XF_Controller_Router_Abstract
     */
    public function addRewrite($name, XF_Controller_Router_Rewrite_Interface $rewrite, $index = NULL)
    {
    	$name = (string) $name;
    	if ($index === NULL)
    	{
    		if (count($this->_rewriteIndex) === 0)
    			$this->_rewriteIndex[0] = $name;
    		else 
    		{
    			$array = array_keys($this->_rewriteIndex);
    			$index = end($array);
    			$this->_rewriteIndex[$index+1] = $name;
    		}
    	}
    	else 
    	{
            XF_Functions::arrayInsert($this->_rewriteIndex, $index, $name);
    	}
        $this->_rewrites[$name] = $rewrite;
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
            $this->_rewrites = $this->_rewriteIndex = array();


        elseif (is_string($name) && isset($this->_rewrites[$name]))
        {
        	XF_Functions::arrayDeleteFromValue($this->_rewriteIndex, $name);
            unset($this->_rewrites[$name]);
        }
        elseif (is_array($name)) 
        {
            foreach ($name as $_name)
            {
                if (is_string($_name) && isset($this->_rewrites[$_name]))
                {
                    XF_Functions::arrayDeleteFromValue($this->_rewriteIndex, $_name);
                    unset($this->_rewrites[$_name]);
                }
            }
        }
        return $this;
    }
	
}