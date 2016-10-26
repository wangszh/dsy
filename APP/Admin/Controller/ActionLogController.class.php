<?php
/**
 *日志管理
 */

namespace Admin\Controller;

use Common\Controller\AdminController;

class ActionLogController extends AdminController {

    /**
     * 日志列表
     */
    public function index($n = 30)
    {

        //过滤筛选条件
        $where = array();
        switch($_GET['searchType'])
        {
            case'user_name':
                $where['user_name'] = array('like','%'.$_GET['searchkey']."%");
                break;
            case'action':
                $where['action'] = array('like','%'.$_GET['searchkey']."%");
                break;
            case'ip':
                $where['ip'] = array('like','%'.$_GET['searchkey']."%");
                break;
            default:
                break;
        }
        $where['del_status']=0;//正常状态
        //查询数据
        $model = M('system_action_log');
        $count = $model->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $this->list = $model->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->order('time desc')->select();
        $this->display();
    }

    /**
     * 日志逻辑删除
     */
    public function ActionLogDel($id)
    {
        $Dad = M('system_action_log');
        $data['del_status'] =1;
        $where['del_status'] =0;
        $where['ac_id'] = array('in',$id);
        if ($Dad->data($data)->where($where)->save()) {
            $this->addLog('id='.$id.'&del_status=1',1);// 记录操作日志
            $this->success('删除日志成功');
        } else {
            $this->addLog('id='.$id.'&del_status=1',1);// 记录操作日志
            $this->success('删除日志错误');
        }
    }

    /**
     * 日志物理删除
     * by chen
     */
    public function ActionLogDelInfo($id)
    {
        $Dad = M('system_action_log');
        if ($Dad->delete($id)) {
            $this->addLog('id='.$id,1);// 记录操作日志
            $this->success('删除日志成功', U('Admin/ActionLog/index'));
        } else {
            $this->addLog('id='.$id,0);// 记录操作日志
            $this->success('删除日志错误', U('Admin/ActionLog/index'));
        }
    }
}
