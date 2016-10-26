<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/14
 * Time: 16:48
 */

namespace Admin\Model;
use Think\Model;
class storeModel extends Model{
    protected $_validate = array(
        array('store_user','','帐号名称已经存在',0,'unique',1),
        array('store_password','checkPwd','密码格式不正确',0,'function'), // 密码格式可以用chenkPwd方法自定义
        array('store_password2','store_password','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致
        array('store_logo','require','请设置LOGO图片',1),
        array('store_name','require','请填写车行名称',1),
        array('level_name','require','请选择车行级别',1),
        array('store_brand','require','请填写车行品牌',1),
        array('store_tel','require','请填写车行的联系电话',1),
//        array('seller_card','require','请输入车行负责人的身份证号码',1),
        array('province_id','require','请输入车行的所在省份',1),
        array('city_id','require','请输入车行的所在城市',1),
        array('area_id','require','请输入车行的所在区域',1),
        array('store_address','require','请输入车行的详细地址',1),


    );
    protected $_auto = array(
        array('store_password', '', self::MODEL_UPDATE, 'ignore'),
        array('store_password', 'md5', self::MODEL_BOTH, 'function'),
        array('store_password', NULL, self::MODEL_UPDATE, 'ignore'),

    );
    function checkPwd($spassword){
        if(!preg_match('/^[\w\W]{6,16}$/', $spassword)){
            return false;
        }else{
            return true;
        }
    }
}