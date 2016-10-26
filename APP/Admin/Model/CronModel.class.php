<?php

// +----------------------------------------------------------------------
// | 计划任务
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

class CronModel extends Model {

    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('subject', 'require', '计划任务名称不能为空！', 1, 'regex', 3),
        array('loop_type', 'require', '计划任务类型不能为空！', 1, 'regex', 3),
        array('cron_file', 'require', '计划任务执行文件不能为空！', 1, 'regex', 3),
    );
    //自动完成
    protected $_auto = array(
        //array(填充字段,填充内容,填充条件,附加规则)
        array("created_time", "time", 1, "function"), //创建时间自动填充
    );

    /**
     * 获取该月天数
     * @param type $month 月份
     * @param type $isLeapYear 是否为闰年
     * @return int
     */
    public function _getMouthDays($month, $isLeapYear) {
        if (in_array($month, array('1', '3', '5', '7', '8', '10', '12'))) {
            $days = 31;
        } elseif ($month != 2) {
            $days = 30;
        } else {
            if ($isLeapYear) {
                $days = 29;
            } else {
                $days = 28;
            }
        }
        return $days;
    }

    //用于模板输出
    public function _getLoopType($select = '') {
        $array = array('month' => '每月', 'week' => '每周', 'day' => '每日', 'hour' => '每小时', 'now' => '每隔');
        return $select ? $array[$select] : $array;
    }

    //输出中文星期几
    public function _capitalWeek($select = 0) {
        $array = array('日', '一', '二', '三', '四', '五', '六');
        return $array[$select];
    }

    //可用计划任务执行文件
    public function _getCronFileList() {
        $dir = APP_PATH . "Cron/";
        $Dirs = new \Common\Org\Dir($dir);
        $fileList = $Dirs->toArray();
        $CronFileList = array();
        foreach ((array) $fileList AS $k => $file) {
            if (strpos($file['filename'], "Cyh") !== 0) {
                unset($fileList[$k]);
            } else {
                $CronFileList[] = $file['filename'];
            }
        }
        return $CronFileList;
    }

}