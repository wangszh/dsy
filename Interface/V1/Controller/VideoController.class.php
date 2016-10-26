<?php
/**
 * 视频相关API
 * 首页视频推荐、视频列表、筛选等
 * 此文件下所有操作均需要验证用户身份token
 * @author Samuel
 *
 */
namespace V1\Controller;

use Common\Controller\ApiController;

class VideoController extends ApiController
{
    public function index(){
        $this->myApiPrint('无效接口');
    }

    /**
     * 获取视频类型列表(幼儿、小学)
     */
    public function videoTypeList()
    {
        $video_list = M('video_class')->where('father_id=0')->field('class_id,class_name')->select();
        if ($video_list)
            $this->myApiPrint('success',200,$video_list);
        else if ($video_list === null)
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获取首页推荐视频
     */
    public function homeRecommendedVideo()
    {
        //此处通过账号信息获取用户偏好ID
        $stu_id['stu_id'] = $this->userStuID;
        $stu_like = M('student')->where($stu_id)->field('class_id')->getField('class_id');
        if (!$stu_like) // 偏好设置为空
            $this->myApiPrint('用户偏好设置为空',300);
        $stu_like = explode(',',$stu_like);
        for ($i=0; $i<count($stu_like); $i++)
        {
            $class_name = M('video_class')->where('class_id = '.$stu_like[$i])->getField('class_name');
            $va = M('video_class')->where('father_id = '.$stu_like[$i])->getField('class_id',true);
            $video = '';
            if ($va)
            {
                $where['class_id'] = array('in',$va);
                $video = M('video')->where($where)->field('vi_id,vi_title,vi_link,vi_play_count,vi_img')
                    ->limit(4)->order('vi_play_count desc')->select();
                if ($video === false)
                    $this->myApiPrint('系统繁忙，请稍后再试',300);
            }
            $array['class_type'] = $class_name;
            $array['class_id'] = implode(',',$va);
            $array['video'] = $video;
            $video_list[] = $array;
        }
        if ($video_list)
            $this->myApiPrint('success',200,$video_list);
        else if ($video_list === null)
            $this->myApiPrint('暂无视频数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 搜索视频
     */
    public function searchVideo()
    {
        // 获取用户输入信息
        $search_message = I('get.searchMessage','');
        $order_message = I('get.orderMessage',0);
        $pageNum = I('get.pageNum',1);

        // 判断用户输入是否为空
        if (trim($search_message) !== '') //输入不为空，执行查询
        {
            // 判断排序选项，并拼接SQL
            switch ($order_message)
            {
                case 0; // 智能排序
                    $orderRule = 'vi_play_count + vi_thumb_count*10 + vi_collect_count*20 + vi_eval_count*5 '; // 智能排序规则：播放数+点赞数*10+收藏数*20+评论数*5
                    break;
                case 1: // 最多评论
                    $orderRule = 'vi_eval_count ';
                    break;
                case 2: // 最新发布
                    $orderRule = 'vi_create_time ';
                    break;
                case 3: // 最多播放
                    $orderRule = 'vi_play_count ';
                    break;
                default:
                    $this->myApiPrint('请选择正确的排序选项',300);
            }

            //定义一个数组条件
            $map['dsy_video.vi_title'] = array('like','%'.$search_message.'%');
            $map['dsy_teacher.tea_name'] = array('like','%'.$search_message.'%');
            $map['_logic'] = 'or';

            // 通过匹配信息查询出相关视频
            $videoList = M('teacher')->join('right join dsy_video on dsy_teacher.tea_id = dsy_video.tea_id')->where($map)
                ->field('vi_id,vi_title,vi_img,vi_link,vi_play_count,vi_eval_count,vi_create_time,dsy_teacher.tea_id,dsy_teacher.tea_name,'.$orderRule.' as orderRule')
                ->order('orderRule desc')->page($pageNum,10)->select();
            if ($videoList)
                $this->myApiPrint('success',200,$videoList);
            else if (empty($videoList))
                $this->myApiPrint('暂无数据',202);
            else
                $this->myApiPrint('系统繁忙，请稍后再试',300);
        }
        else //输入为空，返回错误代码
        {
            $this->myApiPrint('请输入数据',300);
        }
    }

    /**
     * 获取视频类型子列表（小学全部、小学语文、小学数学...）
     */
    public function videoChildTypeList()
    {
        $videoType = I('get.videoType');
        $map['father_id'] = $videoType;
        $map['class_id'] = $videoType;
        $map['_logic'] = 'OR';
        $videoTypeList = M('video_class')->where($map)->field('class_id,class_name')->select();
        if ($videoTypeList)
            $this->myApiPrint('success',200,$videoTypeList);
        else if (empty($videoTypeList))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获取视频列表
     */
    public function videoList()
    {
        // 获取到排序选项和筛选选项
        $orderMessage = I('get.orderMessage',0); // 排序选项（缺省值为0，默认智能排序）
        $fieldMessage = I('get.fieldMessage','all'); // 筛选选项
        $pageNum = I('get.pageNumber',1);

        // 判断排序选项，并拼接SQL
        switch ($orderMessage)
        {
            case 0; // 智能排序
                $orderRule = 'vi_play_count + vi_thumb_count*10 + vi_collect_count*20 + vi_eval_count*5 '; // 智能排序规则：播放数+点赞数*10+收藏数*20+评论数*5
                break;
            case 1: // 最多评论
                $orderRule = 'vi_eval_count ';
                break;
            case 2: // 最新发布
                $orderRule = 'vi_create_time ';
                break;
            case 3: // 最多播放
                $orderRule = 'vi_play_count ';
                break;
            default:
                $this->myApiPrint('请选择正确的排序选项',300);
        }

        $field = 'vi_id, class_id, vi_title, vi_link,vi_img, vi_play_count, '.$orderRule.' as orderRule';
        // 判断筛选选项是否选择为全部
        if ($fieldMessage === 'all')
        {
            $videoList = M('video')->field($field)->order('orderRule DESC')->page($pageNum,10)->select();
        }
        else
        {
            // 判断是否选择为子类型的全部（小学全部）
            if (stripos($fieldMessage,','))
                $where['class_id'] = array('in',$fieldMessage);
            else
                $where['class_id'] = $fieldMessage;
            $videoList = M('video')->where($where)->field($field)->order('orderRule DESC')->page($pageNum,10)->select();
        }

        if ($videoList)
            $this->myApiPrint('success',200,$videoList);
        else if (empty($videoList))
            $this->myApiPrint('暂无视频数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 视频播放--获取视频基本信息（链接、标题等）
     */
    public function videoInformation()
    {
        $video_id = I('get.videoID',0);
        $stuID = $this->userStuID;

        $where['v.vi_id'] = $video_id;
        $video = M('video')->alias('v')
            ->join('LEFT JOIN dsy_student_agree a on a.vi_id = v.vi_id and a.stu_id = '.$stuID)
            ->join('LEFT JOIN dsy_student_collect c on c.vi_id = v.vi_id and c.stu_id = '.$stuID)
            ->where($where)->field('v.vi_id, v.vi_title,vi_img, v.vi_link, v.vi_play_count, v.vi_thumb_count, v.vi_collect_count, v.vi_eval_count,IF(a.vi_id IS NULL,0,1) as agree,IF(c.collect_id IS NULL,0,1) as collect ')
            ->find();
        if ($video)
            $this->myApiPrint('success',200,$video);
        else if ($video === null)
            $this->myApiPrint('找不到相关视频数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 相关视频列表（取同一类下面播放最多的10条）
     */
    public function relatedVideoList()
    {
        $video_id = I('get.videoID',0);
        $map['vi_id'] = $video_id;
        $father_id = M('video')->where($map)->getField('class_id');
        if (!$father_id)
            $this->myApiPrint('找不到相关视频数据',300);

        // 定义一个数组条件（同一类，但不包含该视频）
        $map2['vi_id'] = array('neq',$video_id);
        $map2['class_id'] = $father_id;
        $video_list = M('video')->where($map2)->field('vi_id, class_id, vi_title, vi_img, vi_link, vi_play_count')->order('vi_play_count desc')->limit(10)->select();

        if ($video_list)
            $this->myApiPrint('success',200,$video_list);
        else if (empty($video_list))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 视频的评价列表
     */
    public function videoEvalList()
    {
        // 拿到视频ID和传进来的页数
        $video_id = I('get.videoID',0);
        $page = I('get.pageNum',1);

        // 通过视频ID拿到5条评论
        $where['dsy_video_eval.vi_id'] = $video_id;
        $evalList = M('video_eval')->join(' LEFT JOIN dsy_student ON dsy_video_eval.stu_id = dsy_student.stu_id ')->where($where)
            ->field('eval_id, stu_nickname, stu_head_img, eval_content, eval_time, eval_number')->order('eval_time desc')->page($page,5)->select();

        // 拿到评价数量
        $evalNum = M('video_eval')->where($where)->count();

        $result['evalNum'] = $evalNum;
        $result['evalList'] = $evalList;
        if ($evalList)
            $this->myApiPrint('success',200,$result);
        else if (empty($evalList))
            $this->myApiPrint('暂无数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 获取视频简介信息
     */
    public function videoSynopsis()
    {
        $videoID = I('get.videoID',0);
        $map['vi_id'] = $videoID;

        $videoSynopsis = M('video')
            ->join('LEFT JOIN dsy_teacher ON dsy_video.tea_id = dsy_teacher.tea_id')
            ->join('LEFT JOIN dsy_video_class ON dsy_video.class_id = dsy_video_class.class_id')
            ->where($map)->field('dsy_video.vi_id, dsy_teacher.tea_id, dsy_video_class.class_id, vi_title, tea_name, class_name, vi_create_time, vi_des, tea_des,vi_img')
            ->find();

        if ($videoSynopsis)
            $this->myApiPrint('success',200,$videoSynopsis);
        else if ($videoSynopsis === null)
            $this->myApiPrint('找不到相关视频数据',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 发表视频评论
     */
    public function addVideoEval()
    {
        $videoID = I('get.videoID',0);
        $stuID = $this->userStuID;
        $evalContent = I('get.evalContent','');
        $evalNumber = I('get.evalNumber',0);
        $evalTime = date('Y-m-d H:i:s',time());

        $eval = M("video_eval");
        $data['eval_content'] = trim($evalContent);
        $data['eval_number'] = $evalNumber;
        $rules = array(
            array('eval_content','1,200','评论内容长度为1-200个字符',1,'length'),
            array('eval_number','1,5','评星分数为1-5',1,'between'),
        );
        if (!$eval->validate($rules)->create($data)) $this->myApiPrint($eval->getError());

        $map['vi_id'] = $videoID;
        $evalCount = M('video')->where($map)->getField('vi_eval_count');
        if ($evalCount === null)
            $this->myApiPrint('找不到相关视频数据');

        // 视频-评论表中写入数据
        $data['vi_id'] = $videoID;
        $data['stu_id'] = $stuID;
        $data['eval_time'] = $evalTime;
        $videoEval = $eval->data($data)->add();
        if ($videoEval === false)
            $this->myApiPrint('评论失败，请稍后再试', 300);

        // 视频表更新数据
        $video = M('video')->where($map)->setInc('vi_eval_count');
        if ($video === false) //更新出错
            $this->myApiPrint('数据更新失败，请稍后再试',300);

        $this->myApiPrint('success',200);
    }

    /**
     * 课程问题列表
     */
    public function videoFAQ()
    {
        // 拿到视频ID和传进来的页数
        $videoID = I('get.videoID',0);
        $pageNum = I('get.pageNum',1);

        $map['vi_id'] = $videoID;
        $video = M('video')->where($map)->field('vi_id')->find();
        if (!$video)
            $this->myApiPrint('该视频不存在');

        $where['q.vi_id'] = $videoID;
        $queList = M('question')->alias('q')
            ->join('LEFT JOIN dsy_student s ON q.stu_id = s.stu_id')
            ->where($where)->field('q.q_id,q.que_content,q.que_time,q.ans_count,s.stu_head_img,s.stu_nickname')
            ->order('q.que_time desc')->page($pageNum,5)->select();
        if ($queList)
            $this->myApiPrint('success',200,$queList);
        else if (empty($queList))
            $this->myApiPrint('该视频的提问列表为空',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 学员提问
     */
    public function stuQuestion()
    {
        $videoID = I('get.videoID',0); // 视频ID
        $stuID = $this->userStuID; // 学员ID
        $queContent = I('get.queContent',''); // 问题详情
        $queTime = date('Y-m-d H:i:s',time()); // 提问时间
        $queStatus = 0; // 问答状态（0--待回复）

        $question = M("question");
        $data['que_content'] = trim($queContent);
        $rules = array(array('que_content','1,30','问题长度为1-30个字符',1,'length'));
        if (!$question->validate($rules)->create($data)) $this->myApiPrint($question->getError());
        $map['vi_id'] = $videoID;
        $video = M('video')->where($map)->field('vi_id')->find();
        if (!$video)
            $this->myApiPrint('该视频不存在');

        // 视频-问答表中写入数据
        $data['vi_id'] = $videoID;
        $data['stu_id'] = $stuID;
        $data['que_time'] = $queTime;
        $data['que_status'] = $queStatus;
        $videoFAQ = $question->data($data)->add();
        if ($videoFAQ === false)
            $this->myApiPrint('提问失败，请稍后再试', 300);
        else
            $this->myApiPrint('success',200);
    }

    /**
     * 获得视频笔记
     */
    public function getVideoNote()
    {
        $videoID = I('get.videoID');
        $where['vi_id'] = $videoID;
        $videoNote = M('video')->where($where)->getField('vi_notes');
        if ($videoNote)
            $this->myApiPrint('success',200,$videoNote);
        else if ($videoNote === null)
            $this->myApiPrint('没找到笔记',300);
        else
            $this->myApiPrint('系统繁忙，请稍后再试', 300);
    }

    /**
     * 获得视频弹幕
     */
    public function barrageList()
    {
        $videoID = I('get.videoID',0);
        $where['vi_id'] = $videoID;
        $barrageList = M('barrage')->where($where)->field('bar_content,vi_time')->order('vi_time')->select();

        if ($barrageList)
            $this->myApiPrint('success',200,$barrageList);
        else if (empty($barrageList))
            $this->myApiPrint('暂无弹幕',202);
        else
            $this->myApiPrint('系统繁忙，请稍后再试',300);
    }

    /**
     * 发送弹幕
     */
    public function barrageAdd()
    {
        $stuID = $this->userStuID;
        $videoID = I('get.videoID',0);
        $barContent = I('get.barContent','');
        $videoTime = I('get.videoTime');

        $barrage = M("barrage");
        $data['bar_content'] = trim($barContent);
        $data['vi_time'] = $videoTime;
        $rules = array(
            array('bar_content','1,30','弹幕长度为1-30个字符',1,'length'),
            array('vi_time','number','弹幕发送时间不是数字')
        );
        if (!$barrage->validate($rules)->create($data)) $this->myApiPrint($barrage->getError());
        $where['vi_id'] = $videoID;
        $video = M('video')->where($where)->find();
        if (!$video)
            $this->myApiPrint('找不到相关视频',300);

        $data['vi_id'] = $videoID;
        $data['stu_id'] = $stuID;
        $barrage = $barrage->data($data)->add();
        if (!$barrage)
            $this->myApiPrint('弹幕发送失败',300);
        else
            $this->myApiPrint('success',200);
    }

}

