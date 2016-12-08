<?php
/**
 * Created by PhpStorm.
 * User: shuning
 * Date: 2016/12/8
 * Time: 下午4:15
 */

namespace app\common\model;

use think\model;
class SettingMileage extends Model{
    // 设置完整的数据表（包含前缀）
    protected $table = 'car_setting_mileage';

    // 设置数据表（不含前缀）
    protected $name = 'setting_mileage';




    public function getAllData(){
        $where['deleted'] = 0;
        return $this->where($where)->select();
    }
}
