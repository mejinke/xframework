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
 * @desc 插件基础类
 * @author jingke
 */
abstract class XF_Controller_Plugin_Abstract
{
	 /**
     * @var XF_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * 设置请求对象
     * @access public
     * @param XF_Controller_Request_Abstract $request
     * @return XF_Controller_Plugin_Abstract
     */
    public function setRequest(XF_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * 获取请求对象
     * @access public
     * @return XF_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * 路由开始执行时调用
     * @access public
     * @param XF_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(XF_Controller_Request_Abstract $request)
    {}

    /**
     * 路由完成时调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(XF_Controller_Request_Abstract $request)
    {}

    /**
     * 转发控制器之前调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(XF_Controller_Request_Abstract $request)
    {}

    /**
     * 转发控制器之后调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(XF_Controller_Request_Abstract $request)
    {}
    
	/**
     * Action执行之前调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function preAction(XF_Controller_Request_Abstract $request)
    {}

    /**
     * Action执行之后调用[渲染模板之前]
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     */
    public function postAction(XF_Controller_Request_Abstract $request)
    {}
    
     /**
     * 模板渲染完成之后[输出之前]
     * @access public
     * @param string $html 将要输出到前台的内容
     * @return void
     */
    public function postRender(&$html)
    {}
    
    /**
     *  模板渲染完成并完成输出之后
     * @access public
     * @return void
     */
    public function postOutput()
    {}
    
	/**
     * 404时调用
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @return void
     * @throws XF_Controller_Exception
     */
    public function exception404(XF_Controller_Request_Abstract $request)
    {
    	throw new XF_Controller_Exception('404 Not found!', 404);
    }
    
	/**
     * 监听全局发现的异常
     * @access public
     * @param  XF_Controller_Request_Abstract $request
     * @param  XF_Exception $e
     * @return void
     * @throws XF_Exception
     */
    public function exception(XF_Controller_Request_Abstract $request, XF_Exception $e)
    {
    	throw $e;
    }
}