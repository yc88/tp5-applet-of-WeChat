<?php
/**会员登录管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 13:42
 */
namespace app\admin\controller;

use app\admin\model\UserAdmin;
use think\Controller;
use org\Verify;

class Login extends  Controller
{
    /**登录首页
     * @return mixed
     */
    public function index(){
        return $this->fetch('/login');
    }

    /**登录进行操作
     * @return \think\response\Json
     */
    public function login_go(){
        $userName = input("param.user_name");
        $password = input("param.password");
        $code = input("param.code");
        //场景验证 验证用户名 密码 验证不能为空
        $result = $this->validate(compact('userName', 'password', "code"), 'AdminUserValidate');
        if(true !== $result){
           putMsg(0,$result);
        }
        //验证的正确性进行验证
        $verify = new Verify();
        if (!$verify->check($code)) {
            putMsg(0,'验证码错误');
        }
        $userAdminModel =new UserAdmin();
        $userBool = $userAdminModel->checkedName($userName);
        if(!$userBool){
            putMsg(0,'管理员不存在');
        }
        if(encryptSelf($password)!= $userBool['password']){
            putMsg(0,'密码错误');
        }
        if(1 != $userBool['status']){
            putMsg(0,'该账号被禁用');
        }
        session('username', $userBool['user_name']);
        session('id', $userBool['id']);
        session('head', $userBool['head']);
        session('role', $userBool['role_name']);  // 角色名
        session('role_id', $userBool['role_id']);
        session('rule', $userBool['rule']);
        // 更新管理员状态
        $param = [
            'login_times' => $userBool['login_times'] + 1,
            'last_login_ip' => request()->ip(),
            'last_login_time' => time()
        ];
        $updateResult = $userAdminModel->updateAdminStatus($param,$userBool['id']);
        if(!$updateResult){
            putMsg(0,'系统错误'.$userAdminModel->getLastSql());
        }
        putMsg(1,url('index/index'));
    }

    /**
     * 验证码
     */
    public function check_verify(){
        $verify = new Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->length = 4;
        $verify->codeSet = '0123456789';
        $verify->useNoise = false;
        $verify->fontSize = 14;
        return $verify->entry();
    }

    // 退出操作
    public function loginOut()
    {
        cache(session('role_id'),null);
        session(null);
        $base = new Base();
        $base->cache_role_del();
        $this->redirect(url('index'));
    }


}