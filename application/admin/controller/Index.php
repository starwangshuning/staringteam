<?php

namespace app\admin\controller;


class Index extends Base {
	/**
	 * 九宫格中转页
	 */
    public function index(){
        return $this->fetch('index');
    }

    /**
     * 订单列表页
     */
    public function orderIndex(){
    	return $this->fetch('orderIndex');
    }

    /**
     * 订单详情页
     */
    public function orderDetail(){
    	return $this->fetch('orderDetail');
    }

    /**
     * 设置页面
     */
    public function setting(){
    	return $this->fetch('settingIndex');
    }



}