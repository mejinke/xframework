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
 * @desc 控制器接口
 * @author jingke
 */
interface XF_Controller_Interface
{
	/**
	 * 执行Action
	 * @access public
	 * @return void
	 */
	public function doAction();
	
	/**
	 * 是否存在Action
	 * @access public
	 * @param string $action_name Action名称
	 * @return bool
	 */
	public function hasAction($action_name);

    /**
     * 获取参数
     * @access public
     * @param string $name
     * @return mixed
     */
    public function getParam($name);

    /**
	 * 获取参数，预期该参数的值为数字
	 * @access public
	 * @param string $key 参数名称
	 * @param number $default 如果该参数不存在，需要返回的值，默认为 0
	 */
	public function getParamNumber($key, $default = 0);
	
    /**
     * 获取所有的参数
     * @access public
     * @return array
     */
    public function getParams();
} 