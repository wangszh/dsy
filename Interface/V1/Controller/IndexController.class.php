<?php
/**
 * 公开接口API
 * 城市，登录，注册，版本，更新等
 * 不需要验证用户身份token
 * @author David
 *
 */
namespace V1\Controller;

use Common\Controller\ApiController;

class IndexController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    public function index(){
        //var_dump($_SERVER['HTTP_version']);
//        $va = M('feedback')->select();
//        if($va) $this->myApiPrint('success'.I('post.aaa'),200,$va);
//        else $this->myApiPrint('don\'t find ');
        $va = M('pub_city')->where('city_id = 110100')->cache('cache_pub_city')->select();
        $this->myApiPrint('','',$va);
       // S('cache_pub_city',null);
    }

//    public function testEncode()
//    {
//        $str = I('get.str');
//        echo myEncode($str);
//    }
//
//    public function testDecode()
//    {
//        $str = I('get.str');
//        echo myDecode($str);
//    }

    public function Login()
    {
        $stu_user = I('post.stu_user');
        $pwd = I('post.pwd');
        //$from = I('post.from','android');
        $password = md5($pwd);
        //var_dump($pwd);
        $where['stu_password'] = $password;
        $where['stu_user'] = $stu_user;

        $owner = M('student');
        $resn = $owner->where($where)->field('stu_id,stu_nickname,stu_sex,stu_creat_time,stu_head_img,class_id,stu_phone,stu_city,stu_profession')->find();
        if (!$resn) {
            $this->myApiPrint('帐号密码错误',300);
        }
        else{
            if (!$resn['class_id'])
                $msg = 'first login';
            else
                $msg = 'success';
            $ins_data['from'] = $from;
            $ip = get_client_ip();
            $Ip = new \Org\Net\IpLocation();
            $area = $Ip->getlocation($ip);
            $ins_data ['last_login_time'] = date('Y-m-d H:i:s', time());
            $ins_data ['last_login_ip'] = $ip;
            $ins_data ['last_location'] = $area ['country'] . $area ['area'];
            $owner->where('stu_id=' . $resn ['stu_id'])->data($ins_data)->save();

            $strToken = $stu_user.'|'.$resn['stu_id'];
            $resn['token'] = myDes_encode($strToken,$stu_user);
            $this->myApiPrint($msg,200,$resn);
        }
    }

    public function Register()
    {
        $user['stu_user'] = I('post.stu_user');
        $va = $this->checkUserAccount($user['stu_user']);
        if ($va)
            $this->myApiPrint('该帐号已被注册');
        else {
            $student = M('student');
            $user['stu_password'] = md5(I('post.pwd'));
            $user['stu_head_img'] =  C('HAND_IMG_PATH');
            $user['stu_creat_time'] = date('Y-m-d H:i:s');
            $user['from'] = I('post.from','android');
            $info = $student->data($user)->add();
            if ($info > 0) {
                $this->myApiPrint('success',200,$info);
            } else {
                $this->myApiPrint('register error',300);
            }
        }
    }

    /**
     * 讲师登录
     */
    public function teaLogin()
    {
        $tea_user = I('post.tea_user');
        $pwd = I('post.pwd');
        $password = md5($pwd);

        $where['tea_password'] = $password;
        $where['tea_user'] = $tea_user;
        $owner = M('teacher');
        $tea = $owner->where($where)->field('tea_id,tea_name,tea_head_img')->find();
        if (!$tea)
            $this->myApiPrint('账号密码错误',300);
        $ip = get_client_ip();
        $Ip = new \Org\Net\IpLocation();
        $area = $Ip->getlocation($ip);
        $ins_data ['last_login_time'] = date('Y-m-d H:i:s', time());
        $ins_data ['last_login_ip'] = $ip;
        $ins_data ['last_location'] = $area ['country'] . $area ['area'];

        $owner->where('tea_id=' . $tea ['tea_id'])->data($ins_data)->save();

        $strToken = $tea_user.'|'.$tea['tea_id'];
        $tea['token'] = myDes_encode($strToken,$tea_user);
        $this->myApiPrint('success',200,$tea);
    }
}