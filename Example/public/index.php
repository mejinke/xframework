<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define( 'XF_PATH' , realpath(APPLICATION_PATH . '/../../'));
define( 'TEMP_PATH' , APPLICATION_PATH . '/../temp');
require XF_PATH.'/Application.php';
$application = XF_Application::getInstance(false,false);
$application->getBootstrap()->run();
