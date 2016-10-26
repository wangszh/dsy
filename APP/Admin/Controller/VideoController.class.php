<?php
/**
 * 视频管理
 */

namespace Admin\Controller;
use Common\Controller\AdminController;

class VideoController extends AdminController {

    /**
     * 视频类型列表
     */
    public function videoTypeList($n = '15')
    {
        $className = I('get.className'); // 搜索条件
        if ($className)
            $where['v1.class_name'] = array('like','%'.$className.'%');

        $videoType = M('video_class')->alias('v1');
        $count = $videoType->where($where)->count();
        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();
        $this->list = $videoType->alias('v1')
            ->join('left join dsy_video_class v2 on v1.father_id = v2.class_id')->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)->field('v1.class_id as class_id,v1.class_name as videoClass,v2.class_name as fatherClass')
            ->order('fatherClass')->select();
        $this->display();
    }

    /**
     * 新增视频类型
     */
    public function videoTypeAdd()
    {
        if (IS_POST)
        {
            $videoType = M('video_class');
            if (!$videoType->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($videoType->getError());
            }
            $lastId=$videoType->add($_POST);
            if ($lastId) {
                $this->addLog('class_name='.$_POST['class_name'].'&class_id='.$lastId,1);// 记录操作日志
                $this->success('新增视频类型成功', U('Admin/Video/videoTypeList'));
            }else{
                $this->addLog('class_name='.$_POST['class_name'].'&class_id='.$lastId,0);// 记录操作日志
                $this->error('新增视频类型失败');
            }
        }
        else
        {
            $this->typeList = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select();
            $this->display();
        }
    }

    /**
     * 修改视频类型信息
     */
    public function videoTypeEdit($class_id='0')
    {
        if (IS_POST)
        {
            $videoClass = M('video_class');
            if (!$videoClass->create($_POST,2)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($videoClass->getError());
            }
            $lastId=$videoClass->where('class_id = '.$class_id)->save($_POST);
            if ($lastId !== false) {
                $this->addLog('class_name='.$_POST['class_name'].'&class_id='.$lastId,1);// 记录操作日志
                $this->success('编辑视频类型成功', U('Admin/Video/videoTypeList'));
            }else{
                $this->addLog('class_name='.$_POST['class_name'].'&class_id='.$lastId,0);// 记录操作日志
                $this->error('编辑视频类型失败');
            }
        }
        else
        {
            $this->typeList = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select();
            $this->classType = M('video_class')->field('class_id,class_name,father_id')->find($class_id);
            $this->display();
        }
    }

    /**
     * 删除视频类型
     */
    public function videoTypeDel($class_id='0')
    {
        $videoClass = M('video_class');
        $where['class_id'] = $class_id;
        $father_id = $videoClass->where($where)->getField('father_id');
        // 判断是否为子类型
        if ($father_id)
        {
            $count = M('video')->where($where)->count();
            if ($count){
                $this->addLog('class_id='.$class_id,0);// 记录操作日志
                $this->error('当前视频类型下面还有'.$count.'个视频。不可以删除！');
            }else{
                if ($videoClass->delete($class_id)) {
                    $this->addLog('class_id='.$class_id,1);// 记录操作日志
                    $this->success('视频类型删除成功', U('Admin/Video/videoTypeList'));
                } else {
                    $this->addLog('class_id='.$class_id,0);// 记录操作日志
                    $this->error('视频类型删除失败');
                }
            }
        }
        else
        {
            $count = M('video_class')->where('father_id = '.$class_id)->count();
            if ($count){
                $this->addLog('class_id='.$class_id,0);// 记录操作日志
                $this->error('当前视频类型下面还有'.$count.'个子类型。不可以删除！');
            }else{
                if ($videoClass->delete($class_id)) {
                    $this->addLog('class_id='.$class_id,1);// 记录操作日志
                    $this->success('视频类型删除成功', U('Admin/Video/videoTypeList'));
                } else {
                    $this->addLog('class_id='.$class_id,0);// 记录操作日志
                    $this->error('视频类型删除失败');
                }
            }
        }
    }

    /**
     * 视频列表
     */
    public function videoList($n='15')
    {
        $startTime = I('get.startTime');
        $endTime = I('get.endTime');
        if ($endTime && $startTime)
            $where['v.vi_create_time']=array('between',array($startTime,$endTime));

        $searchKey = I('get.searchKey');
        if ($searchKey){
            $searchType = I('get.searchType',0);
            switch($searchType)
            {
                case 1:
                    $searchType= 't.tea_name';
                    break;
                default:
                    $searchType = 'v.vi_title';
                    break;
            }
            $where[$searchType] = array('like','%'.$searchKey.'%');
        }

        $teaID = I('get.tea_id');
        if ($teaID)
            $where['v.tea_id'] = $teaID;
        $videoList = M('video');
        $count = $videoList->alias('v')
            ->join('left join dsy_teacher t on v.tea_id = t.tea_id')
            ->where($where)->count();
        $Page = new \Think\Page($count,$n);
        $this->Page = $Page->show();
        $this->list = $videoList->alias('v')
            ->join('left join dsy_video_class c on v.class_id = c.class_id')
            ->join('left join dsy_teacher t on v.tea_id = t.tea_id')
            ->field('v.vi_id,c.class_name as cn,t.tea_name,v.vi_title,v.vi_des,v.vi_img,v.vi_create_time,v.vi_play_count,v.vi_thumb_count,v.vi_eval_count,v.vi_collect_count')
            ->where($where)->order('v.vi_create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->display();
    }

    /**
     * 添加视频
     */
    public function videoAdd()
    {
        if (IS_POST)
        {
            $_POST['vi_create_time'] = date('Y-m-d H:i:s',time());
            $video = M('video');
            if (!$video->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($video->getError());
            }
            $_POST['vi_img'] =str_ireplace(C("IMG_REPLACE_STRING"),"/",$_POST['vi_img']);
            $lastId = $video->add($_POST);
            if ($lastId) {
                $this->addLog('vi_title='.$_POST['vi_title'].'&vi_id='.$lastId,1);// 记录操作日志
                $this->success('新增视频成功', U('Admin/Video/videoList'));
            }else{
                $this->addLog('vi_title='.$_POST['vi_title'].'&vi_id='.$lastId,0);// 记录操作日志
                $this->error('新增视频失败');
            }
        }
        else
        {
            $this->typeList = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select(); // 视频类型列表
            $this->display();
        }
    }

    /**
     * 级联返回的视频类型
     */
    public function childTypeList()
    {
        $father_id = I('post.father_id');
        $where['father_id'] = $father_id;
        $result = M('video_class')->where($where)->field('class_id,class_name')->select();
        $this->ajaxReturn($result,"JSON");
    }

    /**
     * 讲师姓名列表
     */
    public function teaList()
    {
        $teaName = I('get.name','','trim');
        if(!$teaName) $this->ajaxReturn('',"JSON");
        $where['tea_name'] = array('like','%'.$teaName.'%');
        $teaList = M('teacher')->where($where)->field('tea_id,tea_name')->select();
        if(!$teaList){ $teaList[0]['tea_id'] = 0;$teaList[0]['tea_name'] = "not find match $teaName";}
        $this->ajaxReturn($teaList,"JSON");
    }

    /**
     * 编辑视频信息
     */
    public function videoEdit($vi_id = '0')
    {
        if (IS_POST)
        {
            $video = M('video');
            if (!$video->create($_POST,2)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($video->getError());
            }
            $_POST['vi_img'] =str_ireplace(C("IMG_REPLACE_STRING"),"/",$_POST['vi_img']);
            $lastId = $video->save($_POST);
            if ($lastId !== false) {
                $this->addLog('vi_title='.$_POST['vi_title'].'&vi_id='.$lastId,1);// 记录操作日志
                $this->success('编辑视频成功', U('Admin/Video/videoList'));
            }else{
                $this->addLog('vi_title='.$_POST['vi_title'].'&vi_id='.$lastId,0);// 记录操作日志
                $this->error('编辑视频失败');
            }
        }
        else
        {
            $this->typeList = M('video_class')->where('father_id = 0')->field('class_id,class_name')->select(); // 视频类型列表
            $this->video = M('video')->alias('v')
                ->join('LEFT JOIN dsy_video_class c ON v.class_id = c.class_id')
                ->join('LEFT JOIN dsy_teacher t ON v.tea_id = t.tea_id')
                ->field('v.vi_id,v.class_id,v.tea_id,v.vi_title,v.vi_link,v.vi_long,v.vi_img,v.vi_des,v.vi_notes,c.father_id,t.tea_name')->find($vi_id);
            $this->display();
        }
    }

    /**
     * 删除视频信息
     */
    public function videoDel($vi_id=0)
    {
        $where['vi_id'] = $vi_id;
        $video = M('video')->where($where)->getField('vi_id,tea_id,vi_collect_count,vi_play_count,vi_thumb_count');
        if($video === null)
            $this->error('找不到相关视频');
        if ($vi_id && M('video')->delete($vi_id)) {
            // 评价表删除数据
            if (M('video_eval')->where($where)->delete() === false)
                $this->error('删除视频评论失败');

            //删除回答
            $questionID = M('question')->where($where)->getField('q_id',true);
            if($questionID)
            {
                $map['q_id'] = array('in',$questionID);
                if(M('answer')->where($map)->delete() === false)
                    $this->error('删除回答失败');

                //删除提问
                if(M('question')->delete(implode(',',$questionID)) === false)
                    $this->error('删除提问失败');
            }

            //删除观看记录
            if(M('student_learn')->where($where)->delete() === false)
                $this->error('删除观看记录失败');

            //删除收藏
            if(M('student_collect')->where($where)->delete() === false)
                $this->error('删除收藏失败');

            //删除点赞
            if(M('student_agree')->where($where)->delete() === false)
                $this->error('删除点赞失败');

            //删除弹幕
            if(M('barrage')->where($where)->delete() === false)
                $this->error('删除弹幕失败');

            //讲师表更新数据
            $tea = M('teacher');
            $tea->where('tea_id = '.$video[$vi_id]['tea_id'])->setDec('tea_collect_count',$video[$vi_id]['vi_collect_count']);
            $tea->where('tea_id = '.$video[$vi_id]['tea_id'])->setDec('tea_play_count',$video[$vi_id]['vi_play_count']);
            $tea->where('tea_id = '.$video[$vi_id]['tea_id'])->setDec('tea_thumb_count',$video[$vi_id]['vi_thumb_count']);

            $this->success('删除视频成功');
        }
        else
            $this->error('删除视频失败');
    }

    /**
     * 查看更多
     */
    public function videoDet($vi_id='0')
    {
        $vi = M('video');
        $where['vi_id'] = $vi_id;
        $this->video = $vi->where($where)->field('vi_id,vi_des,vi_create_time,vi_notes,vi_title,vi_link,vi_img,vi_long,vi_play_count,vi_thumb_count,vi_collect_count,vi_eval_count')->find();
        if ($this->video)
            $this->display();
        else
            $this->error('找不到相关视频信息');
    }

    /**
     * 视频评论
     */
    public function videoEval($vi_id)
    {
        $eval = M('video_eval');
        $where['e.vi_id'] = $vi_id;

        $count = $eval->alias('e')->where($where)->count();
        $Page = new \Think\Page($count,5);
        $data['page'] = $Page->show();

        $data['data'] = $eval->alias('e')
            ->join('LEFT JOIN dsy_student s ON s.stu_id = e.stu_id')
            ->where($where)->field('s.stu_nickname,s.stu_head_img,s.stu_id,e.eval_content,e.eval_time,e.eval_number')
            ->order('e.eval_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->ajaxReturn($data);
    }

} 