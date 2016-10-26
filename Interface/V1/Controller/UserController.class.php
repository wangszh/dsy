<?php
/**
 * 用户相关API
 * 个人信息，观看记录，问答，打赏等
 * 此文件下所有操作均需要验证用户身份token
 * @author David
 *
 */
namespace V1\Controller;

use Common\Controller\ApiController;

class UserController extends ApiController
{
    public function index(){
        $this->myApiPrint('无效接口');
    }

    /**
     * 设置用户偏好
     */
    public function setUserLike()
    {
        $stu_id['stu_id'] = $this->userStuID; //获取到用户的ID
        $data['class_id'] = I('get.typeID',''); //获取到用户所选择分类的ID

        if (empty($data['class_id'])) $this->myApiPrint('请选择偏好',300);

        $va = M('student')->where($stu_id)->data($data)->save();
        if ($va === false) //更新出错
            $this->myApiPrint('用户偏好设置失败，请稍后再试',300);
        else
            $this->myApiPrint('success',200);
    }

    /**
     * 编辑个人资料信息
     */
    public function setUserInfo()
    {
        $stu_id['stu_id'] = $this->userStuID; //获取到用户的ID
        $head_img = I('post.head_img','');
        $nickName = I('post.nickname','');
        $phone = I('post.phone','');
        $sex = I('post.sex','');
        $profession = I('post.profession','');

        //如果用户头像数据不为空，则将APP端传递的图像数据流转化成头像文件
        //上传地址为 User/用户ID.gif
        if(!empty($head_img)) $data['stu_head_img'] = ($this->myStream2Img($head_img,'User','.gif',$stu_id['stu_id'])).'?'.time();
        $rules = array(
            array('nickname','2,20','昵称长度为2-20个字符',1,'length'),
            array('profession','2,20','职称长度为2-10个字符串',1,'length'),
            array('sex',array('男','女'),'请选择性别',1,'in'),
            array('phone','/1[34578]{1}\d{9}$/','请输入正确的手机号',1,'regex'),
        );
        $student = M('student');
        if (!$student->validate($rules)->create()) $this->myApiPrint($student->getError(),300);

        $data['stu_nickname'] = $nickName;
        $data['stu_phone'] = $phone;
        $data['stu_sex'] = $sex;
        $data['stu_profession'] = $profession;
        $info = $student->data($data)->where($stu_id)->save();
        if (false !== $info)
            $this->myApiPrint('修改完成！',200,$data);
        else
            $this->myApiPrint('修改用户资料失败，请稍后再试',300);
    }

    /**
     * 获取学员上次观看的视频
     */
    public function stuLastLearn()
    {
        //获取学员最近的观看记录
        $where['dsy_student_learn.stu_id'] = $this->userStuID;
        $video = M('video')
            ->join('LEFT JOIN dsy_student_learn ON dsy_video.vi_id = dsy_student_learn.vi_id ')
            ->where($where)->order('dsy_student_learn.lern_time DESC')
            ->field('dsy_video.vi_id,dsy_video.vi_title,dsy_video.vi_link')->find();
        if ($video)
            $this->myApiPrint('success',200,$video);
        else if ($video === null)
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 学员点赞
     */
    public function stuAgree()
    {
        $videoID= I('get.videoID',0); // 视频ID
        $where['vi_id'] = $videoID;
        $teaID = M('video')->where($where)->getField('tea_id');
        if(!$teaID) $this->myApiPrint('该视频不存在');

        $where['stu_id'] = $this->userStuID;
        $vaSA = M('student_agree')->where($where)->find();
        if ($vaSA)
        {
            //已点过赞
            $this->myApiPrint('视频点过赞了');
        }
        else
        {
            //该视频未赞过，写入数据
            $stuAgree = M('student_agree')->data($where)->add();
            if (!$stuAgree) $this->myApiPrint('点赞失败，请稍后再试');

            // 视频表更新数据
            $vaVi = M('video')->where('vi_id = '.$videoID)->setInc('vi_thumb_count');
            if (!$vaVi) $this->myApiPrint('系统繁忙，请稍后再试');

            // 讲师表更新数据
            $vaTea = M('teacher')->where('tea_id = '.$teaID)->setInc('tea_thumb_count');
            if (!$vaTea) $this->myApiPrint('系统繁忙，请稍后再试');

            $this->myApiPrint('success',200);
        }
    }

    /**
     * 学员收藏
     */
    public function stuCollect()
    {
        $videoID = I('get.videoID',0); // 视频ID
        $where['vi_id'] = $videoID;
        $mVi = M('video');
        $teaID = $mVi->where($where)->getField('tea_id');
        if(!$teaID) $this->myApiPrint('该视频不存在');

        $where['stu_id'] = $this->userStuID;
        $col_id = M('student_collect')->where($where)->getField('collect_id');

        if($col_id) {
            //收藏记录存在，执行删除该记录操作，并自减讲师收藏数，自减视频收藏数
            $vaCollect = M('student_collect')->where('collect_id = '.$col_id)->delete();
            if (!$vaCollect)
                $this->myApiPrint('取消收藏出现错误，请稍后再试');
            $vid_col['vi_collect_count'] = array('exp','vi_collect_count - 1');
            $tea_col['tea_collect_count'] = array('exp','tea_collect_count - 1');
            $msg = '取消成功';
        }
        else{
            //收藏记录不存在，新增收藏记录操作，并自加讲师收藏数，自加视频收藏数
            $insertColData['vi_id'] = $videoID;
            $insertColData['stu_id'] = $this->userStuID;
            $insertColData['collect_time'] = date('Y-m-d H:i:s',time());
            $vaCollect = M('student_collect')->data($insertColData)->add();
            if (!$vaCollect)
                $this->myApiPrint('收藏失败，请稍后再试');
            $vid_col['vi_collect_count'] = array('exp','vi_collect_count + 1');
            $tea_col['tea_collect_count'] = array('exp','tea_collect_count + 1');
            $msg = '收藏成功';
        }

        $vaVideo = $mVi->where('vi_id = '.$videoID)->data($vid_col)->save();
        if ($vaVideo === false) //更新出错
            $this->myApiPrint('数据更新失败，请稍后再试');
        $vaVideo = M('teacher')->where('tea_id = '.$teaID)->data($tea_col)->save();
        if ($vaVideo === false) //更新出错
            $this->myApiPrint('数据更新失败，请稍后再试');

        $this->myApiPrint('success',200,$msg);
    }

    /**
     * 获取学员个人信息
     */
    public function stuInformation()
    {
        $stuID = $this->userStuID;

        // 得到学员的基本资料
        $where['stu_id'] = $stuID;
        $student = M('student')->where($where)->field('stu_phone,stu_nickname,stu_head_img,stu_sex,stu_city,class_id,stu_profession')->find();

        // 得到学员的偏好
        $map['class_id'] = array('in',$student['class_id']);
        $className = M('video_class')->where($map)->getField('class_name',true);
        $student['class_name'] = implode('、',$className);

        if ($student)
            $this->myApiPrint('success',200,$student);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 个人中心--提问列表
     */
    public function stuHomeQuestion()
    {
        $stuID = $this->userStuID;
        $pageNum = I('get.pageNum',1);

        $where['stu_id'] = $stuID;
        $queList = M('question as q')
            ->join('LEFT JOIN dsy_answer as a ON q.q_id = a.q_id AND a.a_status = 0 ')// 阅读状态，0--未读
            ->join('LEFT JOIN dsy_video v ON v.vi_id = q.vi_id')
            ->where($where)->field('q.q_id,q.que_content,q.que_time,q.ans_count,v.vi_title,v.vi_img,COUNT(a.a_status) as notRead')
            ->group('q.q_id')->order('notRead desc,q.que_time desc')->page($pageNum,10)->select();

        if ($queList)
            $this->myApiPrint('success',200,$queList);
        else if (empty($queList))
            $this->myApiPrint('该学员暂未提问',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获取提问内容
     */
    public function queContent()
    {
        $queID = I('get.queID',0);

        $where['q_id'] = $queID;
        $queContent = M('question')->alias('q')
            ->join('LEFT JOIN dsy_student as s ON s.stu_id = q.stu_id')
            ->where($where)->field('q.q_id,q.que_content,q.que_time,s.stu_head_img,s.stu_nickname')->find();
        if ($queContent)
            $this->myApiPrint('success',200,$queContent);
        else if ($queContent === null)
            $this->myApiPrint('该提问不存在',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获取回答内容
     */
    public function ansContent()
    {
        $queID = I('get.queID',0);
        $pageNum = I('get.pageNum',1);

        $map['q_id'] = $queID;
        $que = M('question')->where($map)->getField('q_id');
        if (!$que)
            $this->myApiPrint('该提问不存在');

        // 找出该问题下的回答
        $where['a.q_id'] = $queID;
        $ansContent = M('answer')->alias('a')
            ->join('LEFT JOIN dsy_student as s1 ON a.a_stu_id = s1.stu_id') // 回答学员
            ->join('LEFT JOIN dsy_teacher as t ON a.a_tea_id = t.tea_id') // 回答讲师
            ->field('IFNULL(s1.stu_head_img,t.tea_head_img) as ans_head,IFNULL(s1.stu_nickname,t.tea_name) as ans_name,a.a_content,a.a_time,IF('.$this->userStuID.'=a.a_stu_id,1,0) as isMyAnswer,a_id,IF(a.a_tea_id IS NULL,0,1) as isTeaAnswer')
            ->where($where)->order('a.a_time desc')->page($pageNum,5)->select();

        if ($ansContent)
            $this->myApiPrint('success',200,array_reverse($ansContent));
        else if (empty($ansContent))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 修改回答状态为已读
     */
    public function changeAnsStatus()
    {
        $queID = I('get.queID',0);
        $where['q_id'] = $queID;
        $que = M('question')->where($where)->field('ans_count')->find();
        if (!$que) $this->myApiPrint('该提问不存在');
        elseif ($que['ans_count'] == 0) $this->myApiPrint('没有回复记录',202);

        $ans = M('answer')->where($where)->setField('a_status',1);
        if ($ans === false) $this->myApiPrint('已读状态设置出错了',300);
        else  $this->myApiPrint('success',200);

    }

    /**
     * 删除提问
     */
    public function deleteFAQ()
    {
        $queID = I('get.queID',0);
        $where['q_id'] = $queID;

        // 删除提问表数据
        $delQuestion = M('question')->where($where)->delete();
        if ($delQuestion === false)
            $this->myApiPrint('系统繁忙，请稍后再试',300);
        else if ($delQuestion === 0)
            $this->myApiPrint('找不到相关记录',300);

        // 删除回答表数据
        $delAnswer = M('answer')->where($where)->delete();
        if ($delAnswer === false)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        $this->myApiPrint('success',200);
    }

    /**
     * 收藏列表
     */
    public function collectList()
    {
        $where['c.stu_id'] = $this->userStuID;
        $pageNum = I('get.pageNum',1);

        $collectList = M('student_collect')->alias('c')
            ->join('LEFT JOIN dsy_video v ON c.vi_id = v.vi_id')
            ->join('LEFT JOIN dsy_teacher t ON v.tea_id = t.tea_id')
            ->field('v.vi_id,v.vi_img,v.vi_title,v.vi_des,v.vi_long,v.vi_play_count,t.tea_id,t.tea_name,t.tea_head_img,c.collect_time,c.collect_id')
            ->where($where)->order('c.collect_time desc')->page($pageNum,10)->select();

        if ($collectList)
            $this->myApiPrint('success',200,$collectList);
        else if (empty($collectList))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 删除收藏
     */
    public function deleteCollect()
    {
        $collectID = I('get.collectID',0);

        // 定义收藏ID的筛选条件
        if (stripos($collectID,','))
            $map['collect_id'] = array('in',$collectID);
        else
            $map['collect_id'] = $collectID;

        // 取得对应的视频ID
        $videoID = M('student_collect')->where($map)->getField('vi_id',true);
        if (!$videoID)
            $this->myApiPrint('找不到相关收藏记录');

        // 定义视频ID的筛选条件
        if (is_array($videoID))
            $vMap['vi_id'] = array('in',$videoID);
        else
            $vMap['vi_id'] = $videoID;

        // 取得对应的讲师ID
        $teaID = M('video')->where($vMap)->getField('tea_id',true);
        if (!$teaID)
            $this->myApiPrint('该视频不存在');

        // 收藏表删除数据
        $result = M('student_collect')->delete($collectID);
        if (!$result)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        // 视频表更新数据
        $video = M('video')->where($vMap)->setDec('vi_collect_count');
        if (!$video)
            $this->myApiPrint('系统繁忙，请稍后再试');

        //讲师表更新数据
        for ($i=0;$i<count($teaID);$i++)
        {
            $teacher = M('teacher')->where('tea_id = '.$teaID[$i])->setDec('tea_collect_count');
            if (!$teacher)
                $this->myApiPrint('系统繁忙，请稍后再试');
        }

        $this->myApiPrint('success',200);
    }

    /**
     * 播放视频
     */
    public function playVideo()
    {
        $videoID = I('get.videoID',0);
        $stuID = $this->userStuID;

        $where['vi_id'] = $videoID;
        $teaID = M('video')->where($where)->getField('tea_id');
        if (!$teaID)
            $this->myApiPrint('找不到相关视频记录');

        // 判断该学员是否首次播放该视频
        $where['stu_id'] = $stuID;
        $studentLearn = M('student_learn')->where($where)->field('lern_id')->find();
        if ($studentLearn === null) // 首次播放该视频
        {
            // 增加播放记录
            $data['vi_id'] = $videoID;
            $data['stu_id'] = $stuID;
            $data['lern_time'] = date('Y-m-d H:i:s',time());
            $studentLearn = M('student_learn')->data($data)->add();
            if ($studentLearn === false)
                $this->myApiPrint('系统繁忙，请稍后再试',300);
        }
        else
        {
            // 修改观看时间
            $data['lern_time'] = date('Y-m-d H:i:s',time());
            $studentLearn = M('student_learn')->where($where)->data($data)->save();
            if ($studentLearn === false)
                $this->myApiPrint('系统繁忙，请稍后再试',300);
        }

        // 更新视频表数据
        $video = M('video')->where('vi_id = '.$videoID)->setInc('vi_play_count');
        if (!$video)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        // 更新讲师表数据
        $teacher = M('teacher')->where('tea_id = '.$teaID)->setInc('tea_play_count');
        if (!$teacher)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        $this->myApiPrint('success',200);
    }

    /**
     * 退出视频播放
     */
    public function exitPlay()
    {
        $videoID = I('get.videoID',0);
        $learnPer = I('get.learnPer',0); // 退出时观看百分比
        $stuID = $this->userStuID;

        if(0 > $learnPer || 100 <$learnPer)
            $this->myApiPrint('观看百分比范围为0-100');

        $data['lern_percentage'] = $learnPer;
        $where['vi_id'] = $videoID;
        $where['stu_id'] = $stuID;
        $stuLearn = M('student_learn')->where($where)->data($data)->save();
        if ($stuLearn === false)
            $this->myApiPrint('系统繁忙，请稍后再试',300);
        else
            $this->myApiPrint('success',200);
    }

    /**
     * 获得观看记录列表
     */
    public function stuLearnList()
    {
        $stuID = $this->userStuID;
        $pageNum = I('get.pageNum',1);
        $where['l.stu_id'] = $stuID;
        $learnList=M('student_learn')->alias('l')
            ->join(' LEFT JOIN dsy_video v ON l.vi_id = v.vi_id ')
            ->where($where)->field('v.vi_title,v.vi_img,v.vi_long,l.lern_percentage,l.lern_time,l.lern_id,l.vi_id')
            ->order('l.lern_time desc')->page($pageNum,10)->select();
        if ($learnList)
            $this->myApiPrint('success',200,$learnList);
        else if (empty($learnList))
            $this->myApiPrint('暂无观看记录数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 删除观看记录
     */
    public function delStuLearn()
    {
        $learnID = I('get.learnID',0);

        if(empty($learnID)) $this->myApiPrint('请选择观看记录',300);

        if (stripos($learnID,','))
            $where['lern_id'] = array('in',$learnID);
        else
            $where['lern_id'] = $learnID;
        $list = M('student_learn')->where($where)->delete();
        if($list)
            $this->myApiPrint('success',200);
        else if ($list === 0)
            $this->myApiPrint('不存在相关观看记录');
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 学员回答提问
     */
    public function stuAnswer()
    {
        $q_id = I('get.q_id',0);
        $ansContent = I('get.content','');

        $answer = M('answer');
        $data['a_content'] = trim($ansContent);
        $rules = array(array('a_content','1,200','回答内容长度为1-200个字符',1,'length'));
        if (!$answer->validate($rules)->create($data)) $this->myApiPrint($answer->getError());

        $where['q_id'] = $q_id;
        $question = M('question')->where($where)->field('q_id')->find();
        if (!$question)
            $this->myApiPrint('找不到相应的问题',300);

        // 问题表回答数+1
        $question = M('question')->where($where)->setInc('ans_count');
        if (!$question)
            $this->myApiPrint('系统繁忙，请稍后再试',300);

        // 回答表写入数据
        $data['a_stu_id'] = $this->userStuID;
        $data['a_time'] = date('Y-m-d H:i:s',time());
        $data['a_status'] = 0;
        $data['q_id'] = $q_id;
        $answer = $answer->data($data)->add();
        if ($answer)
            $this->myApiPrint('success',200);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

}

