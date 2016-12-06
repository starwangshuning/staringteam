<?php

namespace app\admin\controller;


class Order extends Base {

    public function index(){
        return $this->fetch('index');
    }
}