<?php

namespace Admin\Controller;
use Common\Controller\AdminController;

class StudentController extends AdminController {

    /**
     * 学员列表
     */
    public function stuList($n = '15')
    {
        $searchKey = I('get.searchKey');
        if ($searchKey){
            $searchType = I('get.searchType',0);
            switch($searchType)
            {
                case 1:
                    $searchType= 'stu_nickname';
                    break;
                default:
                    $searchType = 'stu_user';
                    break;
            }
            $where[$searchType] = array('like','%'.$searchKey.'%');
        }

        $stuList = M('student');
        $count = $stuList->where($where)->count();

        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();
        $stuList = $stuList->alias('s')
            ->join('LEFT JOIN dsy_student_collect c ON c.stu_id = s.stu_id')
            ->field('s.stu_id,s.stu_user,s.class_id,s.stu_phone,s.stu_nickname,s.stu_profession,s.stu_head_img,s.stu_sex,s.stu_city,s.stu_creat_time,s.last_login_time,s.last_login_ip,s.last_location,s.from,COUNT(c.stu_id) as collect_count')
            ->where($where)->order('s.stu_creat_time desc')->group('s.stu_id')->limit($Page->firstRow.','.$Page->listRows)->select();
        $classID = M('student')->getField('stu_id,class_id',true);
        $videoClass = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select();
        $this->videoClass = $videoClass;
        $this->list = $stuList;
        $this->display();
    }

    /**
     * 学员注册
     */
    public function stuAdd()
    {
        if (IS_POST)
        {
            $stu = M('student');
            if ($stu->where(array('stu_user'=>I('post.stu_user')))->find())
                $this->error('该账号已注册');

            $_POST['stu_creat_time'] = date('Y-m-d H:i:s',time());
            $_POST['stu_password'] = md5(I('post.stu_password'));
            $_POST['stu_head_img'] = C("HAND_IMG_PATH");
            $_POST['from'] = 'android';
            if (!$stu->create($_POST,1))
                $this->error($stu->getError());

            $result = $stu->add();
            if($result){
                $this->addLog('stu_nickname='.I('post.nickname').'&stu_id='.$result,1);// 记录操作日志
                $this->success('学员注册成功', U('Admin/Student/stuList'));
            }else{
                $this->addLog('stu_nickname='.I('post.nickname').'&stu_id='.$result,0);// 记录操作日志
                $this->error('学员注册失败');
            }
        }
        else
        {
            $this->display();
        }
    }

    /**
     * 修改学员密码
     */
    public function stuChangepwd($stu_id)
    {
        if (IS_POST)
        {
            $oldPassword = I('post.old_password', '', 'trim');
            if (empty($oldPassword))
                $this->error('请输入旧密码');
            $newPassword = I('post.stu_password', '', 'trim');
            $new_PwdConfirm = I('post.new_password2', '', 'trim');
            if ($newPassword != $new_PwdConfirm)
                $this->error("两次密码不相同！");
            $stu = M('student');
            $where['stu_id'] = $stu_id;
            $stuPwd = $stu->where($where)->getField('stu_password');
            if (empty($stuPwd))
                $this->error('该学员不存在');
            if ($stuPwd !== md5($oldPassword))
                $this->error('原始密码错误');
            $_POST['stu_password'] = md5($newPassword);
            if (!$stu->create($_POST,2)){
                $this->error($stu->getError());
            }
            if (false!==$stu->where('stu_id = ' . $stu_id)->save()) {
                $this->addLog('stu_id='.$stu_id,1);// 记录操作日志
                $this->success("密码修改成功！", U("Admin/Student/stuList"));
            } else {
                $this->addLog('stu_id='.$stu_id,0);// 记录操作日志
                $this->error("密码修改失败！");
            }
        }
        else
        {
            $student = M('student');
            if ($stu_id)
            {
                $stu = $student->field('stu_id,stu_user,stu_nickname')->find($stu_id);
                $this->stu = $stu;
            }
            $this->display();
        }
    }

    /**
     * 修改学员信息
     */
    public function stuEdit($stu_id)
    {
        $student = M('student');
        if (IS_POST)
        {
            if (!$student->create($_POST,2))
                $this->error($student->getError());
            $result = $student->where('stu_id = '.$stu_id)->save();
            if($result !== false){
                $this->addLog('stu_nickname='.I('stu_nickname').'&stu_id='.$stu_id,1);// 记录操作日志
                $this->success('编辑学员成功', U('Admin/Student/stuList'));
            }else{
                $this->addLog('stu_nickname='.I('stu_nickname').'&stu_id='.$stu_id,0);// 记录操作日志
                $this->error('编辑学员失败');
            }
        }
        else
        {
            if ($stu_id)
            {
                $this->stu = $student->field('stu_id,stu_user,stu_nickname,stu_phone,stu_sex')->find($stu_id);
            }
            $this->display();
        }
    }

    /**
     * 删除学员
     */
    public function stuDel($stu_id)
    {
        $stu = M('student');
        $where['stu_id'] = $stu_id;
        if ($stu->where($where)->delete())
        {
            $this->addLog('stu_id='.$stu_id,1);// 记录操作日志
            $this->success('删除学员成功', U('Admin/Student/stuList'));
        } else {
            $this->addLog('stu_id='.$stu_id,0);// 记录操作日志
            $this->error('删除学员失败');
        }
    }

    /**
     * 重置密码
     */
    public function stuResetpwd($stu_id)
    {
        $where['stu_id'] = $stu_id;
        $data['stu_password'] = md5('123456');
        $student = M('student')->where($where)->data($data)->save();

        if ($student !== false)
        {
            $this->addLog('stu_id = '.$stu_id.', reset pwd.',1);
            $this->success('重置密码成功');
        }
        else
        {
            $this->addLog('stu_id = '.$stu_id.', reset pwd.',0);
            $this->error('重置密码失败');
        }
    }

    /**
     * 学员收藏列表
     */
    public function stuCollect($stu_id,$n='15')
    {
        $collect = M('student_collect');
        $where['c.stu_id'] = $stu_id;
        $count = $collect->alias('c')->where($where)->count();
        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();

        $this->list = $collect->alias('c')
            ->join('LEFT JOIN dsy_video as v ON v.vi_id = c.vi_id')
            ->join('LEFT JOIN dsy_teacher as t ON t.tea_id = v.tea_id')
            ->join('LEFT JOIN dsy_video_class vc ON v.class_id = vc.class_id')
            ->where($where)->field('c.collect_id,c.collect_time,v.vi_id,v.vi_title,v.vi_img,v.vi_des,t.tea_name,vc.class_name')
            ->order('c.collect_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->display();
    }

    /**
     * 取消收藏
     */
    public function collectDel($co_id)
    {
        $collect = M('student_collect');
        $where['c.collect_id'] = $co_id;
        $vi_id = $collect->alias('c')->where($where)->getField('vi_id');
        $stuID = $collect->alias('c')->where($where)->getField('stu_id');
        $tea_id = $collect->alias('c')
            ->join('LEFT JOIN dsy_video v ON v.vi_id = c.vi_id')
            ->join('LEFT JOIN dsy_teacher t ON t.tea_id = v.tea_id')
            ->where($where)->getField('t.tea_id');
        if(!($vi_id && $tea_id))
            $this->error('收藏id有误');

        if ($collect->where($where)->delete($co_id))
        {
            M('video')->where('vi_id = '.$vi_id)->setDec('vi_collect_count');
            M('teacher')->where('tea_id = '.$tea_id)->setDec('tea_collect_count');

            $this->addLog('collect_id='.$co_id,1);// 记录操作日志
            $this->success('取消收藏成功', U('Admin/Student/stuCollect?stu_id='.$stuID));
        } else {
            $this->addLog('collect_id='.$co_id,0);// 记录操作日志
            $this->error('取消收藏失败');
        }
    }

    /**
     * 观看记录列表
     */
    public function stuLearn($stu_id,$n='15')
    {
        $stuLearn = M('student_learn');
        $where['l.stu_id'] = $stu_id;
        $count = $stuLearn->alias('l')->where($where)->count();
        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();

        $this->list = $stuLearn->alias('l')
            ->join('LEFT JOIN dsy_video v ON v.vi_id = l.vi_id')
            ->join('LEFT JOIN dsy_teacher t ON v.tea_id = t.tea_id')
            ->join('LEFT JOIN dsy_video_class c ON c.class_id = v.class_id')
            ->where($where)->field('l.lern_id,l.lern_percentage,l.lern_time,t.tea_name,v.vi_id,v.vi_title,v.vi_img,v.vi_des,c.class_name')
            ->order('l.lern_time desc')->select();

        $this->display();
    }

    /**
     * 删除记录
     */
    public function learnDel($learn_id)
    {
        $stuLearn = M('student_learn');
        if (stripos($learn_id,','))
            $where['lern_id'] = array('in',$learn_id);
        else
            $where['lern_id'] = $learn_id;
        $stuID = $stuLearn->where($where)->getField('stu_id');

        if ($stuLearn->where($where)->delete())
        {
            $this->addLog('student_learn_id = '.$learn_id,1);
            $this->success('删除成功',U('Admin/Student/stuLearn?stu_id='.$stuID));
        }
        else
        {
            $this->addLog('student_learn_id = '.$learn_id,0);
            $this->error('删除失败');
        }
    }

    /**
     * 学员偏好
     */
    public function stuLike($stu_id)
    {
        if (IS_POST)
        {
            $arr = $_POST['class_id'];
            if (empty($arr))
                $this->error('学员偏好不能为空');
            $classID = implode(',',$arr);

            $where['stu_id'] = $stu_id;
            $data['class_id'] = $classID;
            $student = M('student')->where($where)->data($data)->save();
            if ($student !== false)
            {
                $this->addLog('stu_id = '.$stu_id,1);
                $this->success('设置偏好成功',U('Admin/Student/stuList'));
            }
            else
            {
                $this->addLog('stu_id = '.$stu_id,0);
                $this->error('设置偏好失败');
            }
        }
        else
        {
            $this->stu = M('student')->where('stu_id = '.$stu_id)->field('stu_id,stu_nickname,class_id')->find();
            $videoClass = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select();
            $this->videoClass = $videoClass;
            $this->display();
        }
    }

} 