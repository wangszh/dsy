<?php

namespace Admin\Controller;
use Common\Controller\AdminController;

class TeacherController extends AdminController {

    /**
     * 讲师列表
     */
    public function teaList($n = '15')
    {
        $searchKey = I('get.searchKey');
        if ($searchKey){
            $searchType = I('get.searchType',0);
            switch($searchType)
            {
                case 1:
                    $searchType= 'tea_name';
                    break;
                default:
                    $searchType = 'tea_user';
                    break;
            }
            $where[$searchType] = array('like','%'.$searchKey.'%');
        }

        $teaList = M('teacher');
        $count = $teaList->where($where)->count();

        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();
        $this->list = $teaList->alias('t')
            ->join('LEFT JOIN dsy_video v ON t.tea_id = v.tea_id')
            ->field('t.tea_id,t.tea_user,t.tea_name,t.tea_play_count,t.tea_thumb_count,t.tea_collect_count,t.tea_des,t.tea_head_img,t.tea_create_time,t.last_login_time,t.last_login_ip,t.last_location,count(v.tea_id) as video_count')
            ->where($where)->group('t.tea_id')->order('tea_create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->display();
    }

    /**
     * 添加讲师
     */
    public function teaAdd()
    {
        if (IS_POST)
        {
            $_POST['tea_create_time'] = date('Y-m-d H:i:s',time());
            $_POST['tea_password'] = md5(I('post.tea_password'));
            $_POST['tea_head_img'] = C("HAND_IMG_PATH");
            $tea = M('teacher');
            if (!$tea->create($_POST,1))
                $this->error($tea->getError());

            if ($tea->where(array('tea_user'=>I('post.tea_user')))->find())
                $this->error('该账号已注册');

            $teaId = $tea->add();
            if($teaId){
                $this->addLog('teaName='.I('tea_name').'&id='.$teaId,1);// 记录操作日志
                $this->success('添加讲师成功', U('Admin/Teacher/teaList'));
            }else{
                $this->addLog('teaName='.I('tea_name').'&id='.$teaId,0);// 记录操作日志
                $this->error('添加讲师失败');
            }
        }
        else
            $this->display();
    }

    /**
     * 编辑讲师
     */
    public function teaEdit($tea_id)
    {
        if (IS_POST)
        {
            $tea = M('teacher');
            if (!$tea->create($_POST,2))
                $this->error($tea->getError());
            $_POST['tea_head_img'] =str_ireplace(C("IMG_REPLACE_STRING"),"/",$_POST['tea_head_img']);
            $teaId = $tea->where('tea_id = '.$tea_id)->save();
            if($teaId !== false){
                $this->addLog('teaName='.I('tea_name').'&id='.$tea_id,1);// 记录操作日志
                $this->success('编辑讲师成功', U('Admin/Teacher/teaList'));
            }else{
                $this->addLog('teaName='.I('tea_name').'&id='.$tea_id,0);// 记录操作日志
                $this->error('编辑讲师失败');
            }
        }
        else
        {
            $teacher = M('teacher');
            $this->tea = $teacher->field('tea_id,tea_user,tea_name,tea_head_img,tea_des')->find($tea_id);
            $this->display();
        }
    }

    /**
     * 修改讲师密码
     */
    public function teaChangepwd($tea_id)
    {
        if (IS_POST)
        {
            $oldPassword = I('post.old_password', '', 'trim');
            if (empty($oldPassword))
                $this->error('请输入旧密码');
            $newPassword = I('post.tea_password', '', 'trim');
            $new_PwdConfirm = I('post.new_password2', '', 'trim');
            if ($newPassword != $new_PwdConfirm)
                $this->error("两次密码不相同！");
            $tea = M('teacher');
            $where['tea_id'] = $tea_id;
            $teaPwd = $tea->where($where)->getField('tea_password');
            if (empty($teaPwd))
                $this->error('该讲师不存在');
            if ($teaPwd !== md5($oldPassword))
                $this->error('登录密码错误');
            $_POST['tea_password'] = md5($newPassword);
            if (!$tea->create($_POST,2)){
                $this->error($tea->getError());
            }
            if (false!==$tea->where('tea_id = ' . $tea_id)->save()) {
                $this->addLog('tea_id='.$tea_id,1);// 记录操作日志
                $this->success("密码修改成功！", U("Admin/Teacher/teaList"));
            } else {
                $this->addLog('tea_id='.$tea_id,0);// 记录操作日志
                $this->error("密码修改失败！");
            }
        }
        else
        {
            $teacher = M('teacher');
            if ($tea_id) {
                $tea = $teacher->field('tea_id,tea_user,tea_name')->find($tea_id);
                $this->tea = $tea;
            }
            $this->display();
        }
    }

    /**
     * 删除讲师
     */
    public function teaDel($tea_id)
    {
        $tea = M('teacher');
        $where['tea_id'] = $tea_id;
        if ($tea->where($where)->delete())
        {
            $this->addLog('tea_id='.$tea_id,1);// 记录操作日志
            $this->success('删除讲师成功', U('Admin/Teacher/teaList'));
        } else {
            $this->addLog('tea_id='.$tea_id,0);// 记录操作日志
            $this->error('删除讲师失败');
        }
    }

    /**
     * 讲师重置密码
     */
    public function teaResetPwd($tea_id)
    {
        $where['tea_id'] = $tea_id;
        $data['tea_password'] = md5('123456');
        $teacher = M('teacher')->where($where)->data($data)->save();

        if ($teacher !== false)
        {
            $this->addLog('tea_id='.$tea_id,1);
            $this->success('重置密码成功');
        }
        else
        {
            $this->addLog('tea_id='.$tea_id);
            $this->error('重置密码失败');
        }
    }

} 