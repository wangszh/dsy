<?php
/**
 * 系统管理帐号
 * User: Administrator
 * Date: 2015/1/14
 * Time: 16:48
 */

namespace Admin\Model;
use Think\Model;
class SystemUserModel extends Model{
    protected $_auto = array(
        array('password', '', self::MODEL_UPDATE, 'ignore'),
        array('password', 'md5', self::MODEL_BOTH, 'function'),
        array('password', NULL, self::MODEL_UPDATE, 'ignore'),
        array('password', 'require', '密码不能为空！', 0, 'regex', 1),
        array('password2', 'password', '两次输入的密码不一样！', 0, 'confirm'),

    );
}