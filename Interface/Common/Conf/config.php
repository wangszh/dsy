<?php
return array(

    //数据库设置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'dsy', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '123456', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'dsy_', // 数据库表前缀

    /* SESSION设置 */
    'SESSION_AUTO_START' => false, // 是否自动开启Session
    'SESSION_OPTIONS'    => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'       => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'     => 'DSY', // session 前缀

    'DEFAULT_MODULE'     => 'V1', // 默认模块
    'DEFAULT_CONTROLLER' =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'      =>  'index', // 默认操作名称

    /*URL_MODEL*/
    'URL_MODEL'          => 2,
    'URL_HTML_SUFFIX'   => '',
    'URL_PATHINFO_DEPR' => '/',
    'URL_ROUTER_ON'      => true,

    /*加密方式*/
    'DATA_CRYPT_TYPE'  => 'DES',

    /*接口域名*/
    'API_SITE_PREFIX'  => 'http://192.168.54.88/DSY',

    /*默认头像文件路径*/
    'HAND_IMG_PATH'   =>  '/Public/pic_hand_img.png',

    /*加密KEY*/
    'PASS_KEY'    => 'IAMYOURFATHER',

    /*支付方式*/
    'PAY_STATUS'   => array(
        'CYH_WAIT_PAY'  => 'CYH_WAIT_PAY', //等待支付
        'CYH_PAY_W_UP'  => 'CYH_PAY_W_UP', //APP端回调支付成功，等待支付宝回调响应
        'CYH_PAY_OK'    => 'CYH_PAY_OK', //支付完成
    ),
    'PAY_TYPE'   => array(
        'ALIPAY' => '支付宝',
        'SELF'    => '余额支付',
        'WEIXIN'  => '微信支付',
    ),
);