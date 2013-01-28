<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jinke
 * Date: 12-12-23
 * Time: 下午12:11
 * To change this template use File | Settings | File Templates.
 */
class Bootstrap extends XF_Application_Bootstrap
{

    public function inits()
    {
    	XF_Controller_Front::getInstance()->registerPlugin(new Application_Plugin_Test());
       XF_Controller_Front::getInstance()->getRouter()->addRewrite('test', 
       	new XF_Controller_Router_Rewrite('/^\/test$/', 
       		array(
       			'module' => 'default',
       			'controller' => 'index',
       			'action' => 'a'
       		)
       	)
       );
    }
}
