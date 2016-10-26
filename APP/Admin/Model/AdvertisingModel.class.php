<?php
/**
 * Date: 2015/1/19
 * Time: 14:55
 */

namespace Admin\Model;
use Think\Model;
class advertisingModel extends Model{
    protected $_validate = array(
        array('ad_image','require','请上传广告图片',1),
        array('start_time','require','请填写广告开始时间',1),
        array('end_time','require','请填写广告结束时间',1),
        array('start_time,end_time', 'checkDate', '广告结束时间不能小于广告开始时间！！', 1,'callback',3),

    );

    protected function checkDate($Ddata){
        if(strtotime($Ddata['end_time']) > strtotime($Ddata['start_time']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}