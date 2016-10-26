<?php
return array(

    //数据库设置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'dsy', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'dsy_', // 数据库表前缀

    /* SESSION设置 */
    'SESSION_AUTO_START' => true, // 是否自动开启Session
    'SESSION_OPTIONS'    => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'       => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'     => 'DSY', // session 前缀

    'DEFAULT_MODULE'     => 'Admin', // 默认模块
    'DEFAULT_CONTROLLER' =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'      =>  'index', // 默认操作名称

    //模板替换变量，无用（如果启用了请再次备注）
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC_A__' => __ROOT__ . '/APP/Admin/View/Public/',
    ),
);