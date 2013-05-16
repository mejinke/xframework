<?php
class XF_Exception extends Exception
{

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, (int) $code);
    }

    public function __toString()
    {
        $content = '';
        ob_start();
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<title>Application Error</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head><body>';
        echo "<pre>";
        echo parent::__toString()."\n\nParams:\n";
        print_r(XF_Controller_Request_Http::getInstance()->getParams());
        echo "\nRewrites: \n";
        $debug = XF_DataPool::getInstance()->get('DEBUG');
        print_r($debug['Rewites']);
        $_Querys = XF_DataPool::getInstance()->get('Querys');
        echo '<br/>Querys('.count($_Querys).') Time:'.XF_DataPool::getInstance()->get('QueryTimeCount').'<br/>' ;
        print_r($_Querys);
        echo "</pre>";
        echo '</body></html>';
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        return '';
    }

}