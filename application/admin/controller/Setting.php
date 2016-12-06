<?php

namespace app\admin\controller;


class Setting extends Base {

    public function index(){
        
        return $this->fetch('index');
    }

}