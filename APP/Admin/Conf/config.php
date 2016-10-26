<?php
return array(
	//'配置项'=>'配置值'
    'HAND_IMG_PATH'   =>  '/Public/pic_hand_img.png',  // 讲师默认头像
    'IMG_REPLACE_STRING'   =>  '/DSY/',  //路径多余前缀

    'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true

    'CONTROL_MENU'  =>   "导航：<a href='index.php?m=Admin&c=Index&a=index'>控制台</a>",

    /*URL_MODEL*/
    'URL_MODEL' => 0,
    'URL_ROUTER_ON' => false,

    'TICKET_FAIL_TIME' => 7, //券的失效时间  7天【作用与后台赠送时间控制】。在接口中请去Api模块的config设置

    //权限验证设置
    'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 2, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'dsy_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'dsy_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'dsy_auth_rule', //权限规则表
        'AUTH_USER' => 'dsy_system_user'//用户信息表
    ),
    //超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array(1,2,3)
    'ADMINISTRATOR'=>array(1),

    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
);