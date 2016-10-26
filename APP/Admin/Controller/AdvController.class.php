<?php
/**
 * 广告管理
 */

namespace Admin\Controller;
use Common\Controller\AdminController;

class AdvController extends AdminController {

    /**
     * 广告列表
     */
    public function advList($n = '15')
    {
        $advType = I('get.advType','all');
        $advTitle = I('get.advTitle');
        $startTime = I('get.startTime');
        $endTime = I('get.endTime');

        //过滤筛选条件
        $where=array();
        switch($advType)
        {
            case'home':
                $where['type']='home';
                break;
            case'start':
                $where['type']='start';
                break;
            default:
                break;
        }
        if ($advTitle)
            $where['title']=array('like','%'.$advTitle.'%');
        if ($endTime && $startTime)
            $where['end_time']=array('between',array($startTime,$endTime));

        $adv = M('Adv');
        $count = $adv->where($where)->count();
        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();
        $this->list = $adv->where($where)->field('adv_id,logo,title,des,type,start_time,end_time,url')->order('start_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->display();
    }

    /**
     * 添加广告
     */
    public function advAdd()
    {
        if (IS_POST)
        {
            $ds = explode(' 到 ',$_POST['inputDate']);
            if(count($ds) != 2) $this->error('[参数错误] 请选择有效日期！');
            $_POST['start_time'] = $ds[0];
            $_POST['end_time'] = $ds[1];

            $adv=M("adv");
            if (!$adv->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($adv->getError());
            }
            $_POST['logo'] =str_ireplace(C("IMG_REPLACE_STRING"),"/",$_POST['logo']);
            $lastId=$adv->add($_POST);
            if ($lastId) {
                $this->addLog('title='.$_POST['title'].'&adv_id='.$lastId,1);// 记录操作日志
                $this->success('添加广告成功', U('Admin/Adv/advList'));
            }else{
                $this->addLog('title='.$_POST['title'].'&adv_id='.$lastId,0);// 记录操作日志
                $this->error('添加广告失败');
            }
        }
        else
        {
            $this->display();
        }
    }

    /**
     * 编辑广告
     */
    public function advEdit($adv_id='0')
    {
        if (IS_POST) {
            $ds = explode(' 到 ',$_POST['inputDate']);
            if(count($ds) != 2) $this->error('[参数错误] 请选择有效日期！');
            $_POST['start_time'] = $ds[0];
            $_POST['end_time'] = $ds[1];

            $adv=M("adv");
            if (!$adv->create($_POST,2)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($adv->getError());
            }
            $_POST['logo'] =str_ireplace(C("IMG_REPLACE_STRING"),"/",$_POST['logo']);
            $lastId=$adv->where('adv_id = '.$adv_id)->save($_POST);
            if ($lastId !== false) {
                $this->addLog('title='.$_POST['title'].'&adv_id='.$lastId,1);// 记录操作日志
                $this->success('编辑广告成功', U('Admin/Adv/advList'));
            }else{
                $this->addLog('title='.$_POST['title'].'&adv_id='.$lastId,0);// 记录操作日志
                $this->error('编辑广告失败');
            }
        }
        else
        {
            $m=M('adv');
            $this->info=$m->field('adv_id,title,url,des,start_time,end_time,type,logo')->find($adv_id);
            $this->display();
        }
    }

    /**
     * 删除广告
     */
    public function advDel($adv_id='0')
    {
        $adv = M('adv');
        $where['adv_id'] = $adv_id;
        if ($adv->where($where)->delete())
        {
            $this->addLog('adv_id='.$adv_id,1);// 记录操作日志
            $this->success('删除广告成功', U('Admin/Adv/advList'));
        }
        else
        {
            $this->addLog('adv_id='.$adv_id,0);// 记录操作日志
            $this->error('删除广告失败');
        }
    }

} 