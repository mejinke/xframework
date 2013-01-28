<?php
class Application_Plugin_Test extends XF_Controller_Plugin_Abstract
{
	 

    public function exception404(XF_Controller_Request_Abstract $request)
    {
    	throw new XF_Controller_Exception('4014 Not found!', 404);
    }
    

    public function exception(XF_Controller_Request_Abstract $request, XF_Exception $e)
    {
    	throw $e;
    }
}