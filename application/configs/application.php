<?php
return array(
    //公用设置
    'common' => array(

        //PHP相关设置
        'php_settings' => array(
            'date.timezone' => 'PRC',
        ),

        //包含其他应用程序路径
        'include_application_paths' => array(

        ),

        //是否记录debug信息
        'save_debug' => false,

        //模板样式
        'default_view_type' => 'default',

        'database' => array(

            //是否开启主从模式('增、删、改'操作将在master服务器中执行,'查询'将随机在master和slaves服务器中执行)
            'open_slave' => false,

            //服务器列表
            'servers' => array(

                //主服务器列表
                'masters' => array(
                    array(
                        'host' => '127.0.0.1',
                        'port' => '3306',
                        'user' => 'root',
                        'pass' => '123456'
                    )
                ),

                //从服务器列表
                'slaves' => array(

                )
            ),

            //数据库列表
            'dbnames' => array(
                'main' => 'user'
            )
        )

    ),

    //产品正式生产环境
    'production' => array(
        'php_settings' => array(
            'display_errors' => 'Off',
            'display_startup_errors' => '0',
        ),

    ),

    //本地开发、测试环境
    'development' => array(
        'php_settings' => array(
            'display_startup_errors' => '1',
            'display_errors' => 'On',
        ),

        //是否记录debug信息
        'save_debug' => true,
    ),

    //当前运行"环境"
    'use_environmental' => 'development',


);