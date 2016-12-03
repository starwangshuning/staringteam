<?php

namespace app\index\controller;


class Order extends Base{
    public function index(){


        return $this->fetch('index');
    }
}