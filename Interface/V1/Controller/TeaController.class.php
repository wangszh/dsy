<?php
/**
 * 讲师相关API
 *
 */
namespace V1\Controller;

use Common\Controller\ApiController;

class TeaController extends ApiController
{
    public function index(){
        $this->myApiPrint('无效接口');
    }

    /**
     * 讲师个人信息
     */
    public function teaInformation()
    {
        $where['t.tea_id'] = $this->userTeaID;
        $teacher = M('teacher')->alias('t')
            ->join('LEFT JOIN dsy_video v ON t.tea_id = v.tea_id')
            ->join('LEFT JOIN dsy_student_give g ON g.vi_id = v.vi_id')
            ->field('t.tea_user,t.tea_name,t.tea_head_img,t.tea_play_count,t.tea_thumb_count,t.tea_collect_count,COUNT(v.vi_id) as videoNum,SUM(g.give_money) as money')
            ->where($where)->find();
        if ($teacher)
            $this->myApiPrint('success',200,$teacher);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 讲师发布的视频
     */
    public function teaVideo()
    {
        $video = M('video');
        $pageNum = I('get.pageNum',1);
        $teaID = $this->userTeaID;
        $videoList = $video->alias('v')
            ->join('LEFT JOIN dsy_teacher t ON t.tea_id = v.tea_id')
            ->join('LEFt JOIN dsy_question q ON q.vi_id = v.vi_id and q.que_status = 0')
            ->where('v.tea_id = '.$teaID)->field('v.vi_id,v.vi_title,v.vi_create_time,v.vi_img,v.vi_play_count,v.vi_long,count(q.vi_id) as q_num')
            ->group('v.vi_id')->order('v.vi_create_time desc')->page($pageNum,10)->select();

        if ($videoList)
            $this->myApiPrint('success',200,$videoList);
        else if (empty($videoList))
            $this->myApiPrint('发布视频列表为空',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 讲师需要回答问题列表
     */
    public function questionList()
    {
        $videoID = I('get.videoID',0);
        $pageNum = I('get.pageNum',1);
        $where['v.vi_id'] = $videoID;
        $video = M('video')->alias('v')->where($where)->field('v.vi_title')->find();
        if (!$video)
            $this->myApiPrint('找不到相关的视频');

        $where['q.que_status'] = array('neq',99);
        $list = M('question')->alias('q')
            ->join('LEFT JOIN dsy_student s ON s.stu_id = q.stu_id')
            ->join('LEFT JOIN dsy_video v ON v.vi_id = q.vi_id')
            ->where($where)->field('s.stu_nickname,s.stu_head_img,q.que_content,q.que_time,q.q_id,q.que_status')
            ->order('q.que_time desc')->page($pageNum,10)->select();
        if ($list)
            $this->myApiPrint('success',200,$list);
        else if (empty($list))
            $this->myApiPrint('需要回答的问题列表为空',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 讲师标记问题为已读
     */
    public function readQuestion()
    {
        $questionID = I('get.questionID');
        $where['q_id'] = $questionID;

        $question = M('question')->where($where)->where('que_status = 0')->setField('que_status',1);
        if (!$question)
            $this->myApiPrint('标记已读失败，请稍后再试',300);
        else
            $this->myApiPrint('success',200);
    }

    /**
     * 讲师删除问题
     */
    public function delQuestion()
    {
        $questionID = I('get.questionID');
        $where['q_id'] = $questionID;

        $question = M('question')->where($where)->setField('que_status',99);
        if (!$question)
            $this->myApiPrint('删除提问失败',300);
        else
            $this->myApiPrint('success',200);
    }

    /**
     * 讲师回答问题
     */
    public function teaAnswer()
    {
        $content = I('get.content');
        if ($content === "")
            $this->myApiPrint('回答内容不能为空',300);

        $questionID = I('get.questionID');
        $where['q_id'] = $questionID;
        $question = M('question')->where($where)->field('q_id')->find();
        if (!$question)
            $this->myApiPrint('找不到相应的问题',300);

        // 问题表回答数+1
        $question = M('question')->where($where)->setInc('ans_count');
        if (!$question)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        // 回答表写入数据
        $data['q_id'] = $questionID;
        $data['a_tea_id'] = $this->userTeaID;
        $data['a_content'] = $content;date('Y-m-d H:i:s',time());
        $data['a_time'] = date('Y-m-d H:i:s',time());
        $data['a_status'] = 0;
        $answer = M('answer')->data($data)->add();
        if ($answer)
            $this->myApiPrint('success',200);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获得回答列表
     */
    public function answerList()
    {
        $queID = I('get.queID',0);
        $map['q_id'] = $queID;
        $que = M('question')->where($map)->getField('q_id');
        if (!$que)
            $this->myApiPrint('该提问不存在');

        // 找出该问题下的回答
        $where['a.q_id'] = $queID;
        $answerList = M('answer')->alias('a')
            ->join('LEFT JOIN dsy_student as s1 ON a.a_stu_id = s1.stu_id') // 回答学员
            ->join('LEFT JOIN dsy_teacher as t ON a.a_tea_id = t.tea_id') // 回答讲师
            ->field('IFNULL(s1.stu_head_img,t.tea_head_img) as ans_head,IFNULL(s1.stu_nickname,t.tea_name) as ans_name,a.a_content,a.a_time,IF('.$this->userTeaID.'=a.a_tea_id,"yes","no") as isMyAnswer')
            ->where($where)->order('a.a_time desc')->select();

        if ($answerList)
            $this->myApiPrint('success',200,$answerList);
        else if (empty($answerList))
            $this->myApiPrint('该问题暂无回答',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 讲师赏金明细列表
     */
    public function moneyList()
    {
        $where['g.tea_id'] = $this->userTeaID;
        $moneyList = M('student_give')->alias('g')
            ->join('LEFT JOIN dsy_video v ON g.vi_id = v.vi_id')
            ->join('LEFT JOIN dsy_student s ON g.stu_id = s.stu_id')
            ->where($where)->field('v.vi_id,v.vi_title,v.vi_long,v.vi_img,s.stu_nickname,s.stu_head_img,g.give_money,g.give_time,g.give_id')->select();

        if ($moneyList)
            $this->myApiPrint('success',200,$moneyList);
        else if (empty($moneyList))
            $this->myApiPrint('暂无打赏记录',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

}

