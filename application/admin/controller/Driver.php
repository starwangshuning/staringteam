<?php

namespace app\admin\controller;


class Driver extends Base {

    public function index(){
        
        return $this->fetch('index');
    }


    public function add(){
    	return $this->fetch('add');
    }



}