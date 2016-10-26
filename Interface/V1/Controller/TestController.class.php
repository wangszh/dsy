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
use Think\Controller\RestController;

class TestController extends RestController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    public function index(){
        $my_array['id'] = "DogHorse";
        $my_array['name'] = "DogHorse";
        $my_array['id1'] = "DogHorse1";
        $my_array['name1'] = "DogHorse1";

        while(list($changpin,$jiage)=each($my_array))
        {
            echo "$changpin=>$jiage".'<br>';
        }
//        $va = M('feedback')->select();
//        if($va) $this->myApiPrint('success',200,$va);
//        else $this->myApiPrint('don\'t find ');
       // $this->response($this->_method,'json');
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, 'http://192.168.54.88/DSY/Interface/');
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_NOBODY, true);
//        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, 'aaa=111');
//        $return_str = curl_exec($curl);
//        curl_close($curl);
//        echo $return_str;
    }
}