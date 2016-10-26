<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-10-10
 * Time: 下午2:42
 */

namespace Common\Common;


class Tools
{
    /**
     * 获取开放的省的信息
     */
    public static function ProInfo()
    {
        $model = M("province");
        $data = $model->field('province_id,province')->where(array('open' => 1))->select();
        return $data;
    }


    /**
     * 根据省的id获取城市的信息
     * @param $proId省id
     * @return mixed 城市信息
     */
    public static function CityInfo($proId)
    {
        $model = M("pub_city");
        if (isset($proId)) {
            $data = $model->field('city_id,city,code')->where(array('father' => $proId, 'open' => 1))->select();
        } else {
            $data = $model->field('city_id,city,code')->where(array('open' => 1))->select();
        }

        return $data;
    }

    /**
     * 根据城市Id获取区域的信息
     * @param $cityId 城市Id
     * @return mixed 区域信息
     */
    public static function AreaInfo($cityId)
    {
        $model = M('area');
        if (isset($cityId)) {
            $data = $model->field('area_id,area')->where(array('father' => $cityId))->select();
        } else {
            $data = $model->field('area_id,area')->select();
        }

        return $data;
    }

    /**
     * 检查敏感词
     * @param $str 需要检查的非空字符串
     * @return boolean 如果有敏感词则返回false，否则返回true
     */
    public static function CheckChar($str='')
    {
        if(empty($str)) return false;
        $ccs = @file_get_contents('check_char.txt');
        $arr = explode(',',strtolower($ccs));
        $str = strtolower($str);
        foreach($arr as $k=>$v)
        {
            if(strpos($str, $v) !== false) return false;
        }
        return true;
    }
} 