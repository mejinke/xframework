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
 * @desc 框架路由器
 * @author jingke
 */
interface XF_Controller_Router_Interface
{
	/**
	 * 启动路由
	 * @return void
	 */
	public function run();
	
	/**
	 * 添加一个参数
	 * @param string $name
	 * @param mixed $value
	 * @return XF_Controller_Router_Interface
	 */
	public function setParam($name, $value);

    /**
     * 批量设置参数
     * @param array $params
     * @return XF_Controller_Router_Interface
     */
    public function setParams(array $params);

    /**
     * 获取参数
     * @param string $name
     * @return mixed
     */
    public function getParam($name);

    /**
     * 获取所有的参数
     * @return array
     */
    public function getParams();

    /**
     *　清除参数
     * @param null|string|array 参数键名或键名数组,如果等于null将删除所有参数
     * @return XF_Controller_Router_Interface
     */
    public function clearParams($name = null);
} 