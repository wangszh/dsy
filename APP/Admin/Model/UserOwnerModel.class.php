<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/14
 * Time: 16:48
 */

namespace Admin\Model;
use Think\Model;
class UserOwnerModel extends Model{
    protected $_auto = array(
        array('password', '', self::MODEL_UPDATE, 'ignore'),
        array('password', 'md5', self::MODEL_BOTH, 'function'),
        array('password', NULL, self::MODEL_UPDATE, 'ignore'),
        array('add_time','getTime',1,'callback'),

    );
    //时间回调
    protected function getTime(){
        return date("Y-m-d H:i:s");
    }
}