<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define( 'XF_PATH' , realpath(APPLICATION_PATH . '/../XF') ) ;
define( 'TEMP_PATH' , APPLICATION_PATH . '/../temp' ) ;

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$start = microtime_float();
require XF_PATH.'/Application.php';
$application = new XF_Application();
$application->setBootstrap('Bootstrap')->run();



echo "<pre>";
echo "\n\nExceptionMessage: ".XF_DataPool::getInstance()->getHash('DEBUG', 'ExceptionMessage')."\n";
echo "\nParams:\n";
print_r(XF_Controller_Request_Http::getInstance()->getParams());
echo "\nRewrites: \n";
print_r(XF_DataPool::getInstance()->getHash('DEBUG', 'Rewites'));
$_Querys = XF_DataPool::getInstance()->get('Querys');
echo '<br/>Querys('.count($_Querys).') Time:'.XF_DataPool::getInstance()->get('QueryTimeCount').'<br/>' ;
print_r($_Querys);
echo "</pre>";
