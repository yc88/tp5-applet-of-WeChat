<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
//    报名首页
    public function index()
    {
        $request = Request::instance();
        $title = '首页';



        $this->assign([
                'title'=>$title,
                'action'=>$request->action()
            ]);
        return $this->fetch();
    }

//    我要报名
    public function register(){
        $request = Request::instance();
        $title = '我要报名';

        $this->assign([
            'title'=>$title,
            'action'=>$request->action()
        ]);
        return $this->fetch();
    }

//    上传作品
    public function toapply(){
        $request = Request::instance();
        $title = '上传作品';

        $this->assign([
            'title'=>$title,
            'action'=>$request->action()
        ]);
        return $this->fetch();
    }
}
