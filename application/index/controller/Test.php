<?php
namespace app\index\controller;

use think\Controller;

class Test extends  Controller
{


    /**测试 打印
     * @return mixed
     */
public function index(){

    return $this->fetch();
}
}