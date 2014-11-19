<?php
return array(
	
	"app_key" => 'main',

	"domain" => 'xf22.com', //网站根域名

    //公用设置
    'common' => array(

        //PHP相关设置
        'php_settings' => array(
            'date.timezone' => 'PRC',
        ),
		
        //使用程序默认的路由器
        'use_default_router' => true,
        
        //包含其他应用程序路径
        'include_application_paths' => array(

        ),

        //是否记录debug信息
        'save_debug' => false,

        //模板样式
        'view_style' => 'default',

        'database' => array(

            //是否开启主从模式('增、删、改'操作将在master服务器中执行,'查询'将随机在master和slaves服务器中执行)
            'open_slave' => false,

            //服务器列表
            'server' => array(

                //主服务器列表
                 'masters' => array(
        			 array(
                        'host' => '218.245.4.63',
                        'port' => '3306',
                        'user' => 'cheduoshao',
                        'pass' => 'vTZczAdcvB'
                    ),
                    /*
             		 array(
                  	   'host' => '192.168.1.13',
                        'port' => '3306',
                        'user' => 'root',
                        'pass' => 'ShangDu2905'
                    )   
                    */                 
                ),

                //从服务器列表
                'slaves' => array(

                )
            ),

            //数据库列表
            'dbnames' => array(
                'main' => 'cheduoshao_main',
            	'auto' => 'cheduoshao_auto',
            	'price' => 'cheduoshao_price',
            	'used' => 'cheduoshao_used',
            	'exponent' => 'cheduoshao_exponent',
            )
        )

    ),

    //产品正式生产环境
    'production' => array(
        'php_settings' => array(
            'display_errors' => 'Off',
            'display_startup_errors' => '0',
        ),
        //memcache缓存服务器列表
        'memcache' => array(
        	'192.168.1.13:11211',
        ),
        
        //框架编译模式
        'compile_model' => true,

    ),

    //本地开发、测试环境
    'development' => array(
        'php_settings' => array(
    		'display_errors' => 'On',
            'display_startup_errors' => '1',
        ),
		
        //memcache缓存服务器列表
        'memcache' => array(
        	'192.168.1.13:11211',
        ),
        
        //是否记录debug信息
        'save_debug' => true,
        
         //框架编译模式
        'compile_model' => false,
    ),

    //当前运行"环境"
    'use_environmental' => 'development',


);