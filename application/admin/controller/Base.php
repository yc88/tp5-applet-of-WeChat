<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 13:43 公共继承controller
 */
namespace app\admin\controller;
use app\admin\model\Role;
use app\admin\model\RoleModel;
use \think\Controller;
class Base extends  Controller
{

    //控制器初始化
    public function _initialize(){
        if(empty(session('id')) || empty(session('username'))){
            if(request()->isAjax()){
                putMsg(0,'登录超时');
            }
            $this->redirect(url('login/index'));
        }
        //查看角色缓存信息
        $this->cache_role();
        // 获取相关的控制器 方法 用于检测
        $control = lcfirst(request()->controller());
        $action = lcfirst(request()->action());
        if(empty(authCheck($control . '/' . $action))){
            if(request()->isAjax()){
               putMsg(0,'您没有权限');
            }
            $this->error('403 您没有权限');
        }

        $this->assign([
                'head'     => session('head'),
                'username' => session('username'),
                'rolename' => session('role'),
                 'u_id'=>session('id'),
            ]);
    }
        /**
         * 缓存角色信息
         */
        public function cache_role(){
            $cacheRoleId = cache(session('role_id'));
            if(is_null($cacheRoleId) || empty($cacheRoleId)){
                    $roleModel = new RoleModel();
                $info = $roleModel->getInfoRole(session('role_id'));
               cache(session('role_id'),$info['action']);
            }
        }

        /**
         * 清除角色缓存数据
         */
    public function cache_role_del(){
        $roleModel = new RoleModel();
        $result = $roleModel->getList();
        foreach ($result as $value) {
            cache($value['id'], null);
        }
    }
}