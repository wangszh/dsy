<?php

/**
 * 后台用户相关控制器
 * 系统管理员/物业/小区/店铺总店/店铺
 * 登录/添加账户/帐号管理
 * 系统对前端用户管理
 * @author CYH
 */
namespace Admin\Controller;
use Common\Controller\AdminController;
class UserController extends AdminController
{
    /**
     * 用户登录
     */
    public function Login()
    {
        if (IS_POST) {
            $data ['user_name'] = trim(I('post.username'));
            $password = md5(trim(I('post.password')));
            $find = M('system_user')->where($data)
                ->field('user_id,user_name,password,status,remark,last_login_time,province_id,city_id')
                ->find();

            //有数组并且密码不为错
            if ($find && ($password === $find ['password'])) {
                if ($find ['status'] == 1) {
                    // 登录成功，设置session
                    session('uid', $find ['user_id']);
                    session('remark', $find ['remark']);
                    session('userName', $find ['user_name']); // 帐号名
                    session('last_login_time', $find ['last_login_time']); // 上次登录时间
                    session('provinceId', $find ['province_id']); // 省
                    session('cityId', $find ['city_id']); // 市

                    //更新数据
                    $ip = get_client_ip(); // 本次登录IP，时间，登录位置
                    $Ip = new \Org\Net\IpLocation();
                    $area = $Ip->getlocation($ip); // 获取某个IP地址所在的位置
                    $upData ['last_login_time'] = date('Y-m-d H:i:s', time());
                    $upData ['last_login_ip'] = $ip;
                    $upData ['last_location'] = $area ['country'] . $area ['area'];
                    M('system_user')->where('user_id=' . $find ['user_id'])->data($upData)->save();
                    //跳转回到来源页面
                    $link = session('link');
                    if (!empty($link) || isset($link)) {
                        session('link', null);
                        $this->addLog('login_id='.$find ['user_id'].'&ip='.$ip,1);// 记录操作日志
                        $this->insert_log($find ['user_id'],$find ['user_name'],'',1,$ip,$upData ['last_location']);// 记录登录日志
                        $this->success('登录成功，返回来源页', U($link));
                    } else {
                        $this->insert_log($find ['user_id'],$find ['user_name'],'',1,$ip,$upData ['last_location']);// 记录登录日志
                        $this->redirect('Admin/Index/index',array(),0);
                    }
                } else {
                    $this->addLog('login_id='.$find ['user_id'].'&ip='.get_client_ip(),0);// 记录操作日志
                    $this->insert_log($find ['user_id'],$find ['user_name'],'',0,get_client_ip());// 记录登录日志
                    $this->error('帐号审核中！');
                }
            } else {
                $this->error('帐号密码错误！');
            }
        } else {
            $this->display();
        }
    }

    //登录跳转
    public function dispatch()
    {
        echo I('post.name');
        echo file_get_contents ( 'php://input' );
    }

    /**
     * 用户注销登录
     */
    public function Logout()
    {
        session(null);
        $this->success('退出成功。', U('Admin/User/Login'));
    }
}