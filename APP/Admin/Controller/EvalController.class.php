<?php
/**
 * 评论管理
 */

namespace Admin\Controller;
use Common\Controller\AdminController;

class EvalController extends AdminController {

    /**
     * 评论列表
     */
    public function evalList($vi_id)
    {
        $where['v.vi_id'] = $vi_id;
        $count = M('video_eval')->alias('v')->where($where)->count();
        $Page = new \Think\Page($count,15);
        $this->Page = $Page->show();

        $this->list = M('video_eval')->alias('e')
            ->join('LEFT JOIN dsy_video v ON e.vi_id = v.vi_id')
            ->join('LEFT JOIN dsy_student s ON s.stu_id = e.stu_id')
            ->field('s.stu_nickname,e.eval_content,e.eval_time,e.eval_number,v.vi_title')
            ->where($where)->order('e.eval_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->display();
    }

} 