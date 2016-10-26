<?php
/**
 * 广告相关API
 * 广告展示等
 * 此文件下所有操作均需要验证用户身份token
 * @author Samuel
 *
 */
namespace V1\Controller;

use Common\Controller\ApiController;

class AdvController extends ApiController
{
    public function index(){
        $this->myApiPrint('无效接口');
    }

    /**
     * 首页广告展示
     */
    public function homeAdv()
    {
        // 定义一个数组条件
        $map['type'] = 'home'; //首页幻灯片类型
        $t = date('Y-m-d',time());
        $map['start_time'] = array('elt',$t);
        $map['end_time'] = array('egt',$t);

        $va = M('adv')->where($map)->field('adv_id,logo,title,des,url')->select();
        if ($va)
            $this->myApiPrint('success',200,$va);
        else if (empty($va))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }
}

