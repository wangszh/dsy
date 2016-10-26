<?php
/**
 * 问答管理
 */

namespace Admin\Controller;
use Common\Controller\AdminController;

class FAQController extends AdminController {

    /**
     * 问题列表
     */
    public function questionList($vi_id)
    {
        $where['v.vi_id'] = $vi_id;
        $count = M('question')->alias('v')->where($where)->count();
        $Page = new \Think\Page($count,2);
        $data['page'] = $Page->show();

        $data['data'] = M('question')->alias('q')
        ->join('LEFT JOIN dsy_video v ON q.vi_id = v.vi_id')
        ->join('LEFT JOIN dsy_student s ON s.stu_id = q.stu_id')
        ->field('q.q_id,q.que_content,q.que_time,q.ans_count,v.vi_title,s.stu_nickname')
        ->where($where)->order('q.que_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->ajaxReturn($data);
    }

    /**
     * 查看回答
     */
    public function answerList($q_id)
    {
        $answer = M('answer');
        $where['q_id'] = $q_id;
        $data = $answer->alias('a')
            ->join('LEFT JOIN dsy_student s ON s.stu_id = a.a_stu_id')
            ->join('LEFT JOIN dsy_teacher t ON t.tea_id = a.a_tea_id')
            ->field('s.stu_nickname,t.tea_name,s.stu_head_img,t.tea_head_img,a.a_content,a.a_time,a_stu_id,a_tea_id')
            ->where($where)->select();
        $this->ajaxReturn($data);
    }

    /**
     * 删除问题
     */
    public function questionDel($q_id)
    {
        $question = M('question');
        $where['q_id'] = $q_id;
        $vi_id = M('question')->where($where)->getField('vi_id');
        if ($question->where($where)->delete())
        {
            M('answer')->where($where)->delete();
            $this->addLog('$q_id='.$q_id,1);// 记录操作日志
            $this->success('删除问题成功', U('Admin/FAQ/questionList?vi_id='.$vi_id));
        }
        else
        {
            $this->addLog('$q_id='.$q_id,1);// 记录操作日志
            $this->error('删除问题失败');
        }
    }

    /**
     * 删除回答
     */
    public function answerDel($a_id)
    {
        $answer = M('answer');
        $question = M('question');
        $where['a_id'] = $a_id;
        $q_id = $answer->where($where)->getField('q_id');
        if (!$q_id)
            $this->error('找不到相关回答');
        if ($answer->where($where)->delete())
        {
            $question->where('q_id = '.$q_id)->setDec('ans_count');
            $this->addLog('a_id = '.$a_id,1);
            $this->success('删除回答成功',U('Admin/FAQ/answerList?&q_id='.$q_id));
        }
        else
        {
            $this->addLog('a_id = '.$a_id,0);
            $this->error('删除问题失败');
        }
    }

} 