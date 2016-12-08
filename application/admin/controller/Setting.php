<?php

namespace app\admin\controller;

use think\Request;
use app\common\util\ApiUtil;
use app\common\model\SettingMileage as SettingMileageModel;
use app\common\model\SettingPeriod as SettingPeriodModel;
class Setting extends Base {


    public $settingMileageModel;
    public $settingPeriodModel;

    public function __construct(){
        parent::__construct();
        $this->settingMileageModel = new SettingMileageModel();
        $this->settingPeriodModel = new SettingPeriodModel();
    }




    public function index(){
        $mileageData = $this->settingMileageModel->getAllData();
        $periodData  = $this->settingPeriodModel->getAllData();

        $this->assign('periodData',$periodData);
        $this->assign('mileageData',$mileageData);
        return $this->fetch('index');
    }


    public function updatePeriod(Request $request){
        if ($request->isPost()) {
            $data = $request->param();
            foreach ($data as $key=>$value) {
                $d = explode('-',$key);//$key = period-1-price_special
                $this->settingPeriodModel->where('id',$d[1])->setField($d[2], $value);
            }
            return ApiUtil::resultMessage('修改成功',true,1);
        }else{
            return ApiUtil::resultMessage('非法操作',false,-1);
        }
    }

    public function updateMileage(Request $request){
        if ($request->isPost()) {
            $id          = $request->param('id');
            $updateArr['price_unit']  = $request->param('price_unit');
            $updateArr['price_minus'] = $request->param('price_minus');
            $updateArr['price_add']   = $request->param('price_add');

            $this->settingMileageModel->where('id', $id)->update($updateArr);
            return ApiUtil::resultMessage('修改成功',true,1);
        }else{
            return ApiUtil::resultMessage('非法操作',false,-1);
        }
    }

}