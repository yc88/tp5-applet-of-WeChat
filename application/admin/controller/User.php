<?php
/** 后端用户管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-14
 * Time: 9:48
 */
namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\admin\model\UserAdmin;

class User extends  Base
{
    //管理员管理
    public function index(){
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['user_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new UserAdmin();
            $selectResult = $user->getWhereList($where, $offset, $limit);
            $status = config('user_status');

            // 拼装参数
            foreach($selectResult as $key=>$vo){

                $selectResult[$key]['last_login_time'] = date('Y-m-d H:i:s', $vo['last_login_time']);
                $selectResult[$key]['status'] = $status[$vo['status']];

                if( 1 == $vo['id'] ){
                    $selectResult[$key]['operate'] = '';
                    continue;
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加用户
    public function userAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['password'] = encryptSelf($param['password']);
            $user = new UserAdmin();
            $flag = $user->insertUser($param);
            if($flag[0] == 0){
                putMsg(0,$flag[1]);
            }
            putMsg(1,$flag[1]);
        }

        $role = new RoleModel();
        $roleList =$role->getList();
         array_shift($roleList);
        $this->assign([
            'role' =>$roleList,
            'status' => config('user_status')
        ]);
        return $this->fetch();
    }

    // 编辑用户
    public function userEdit()
    {
        $user = new UserAdmin();

        if(request()->isPost()){

            $param = input('post.');
            if(empty($param['password'])){
                unset($param['password']);
            }else{
                $param['password'] = encryptSelf($param['password']);
            }
            $flag = $user->editUser($param);
            if($flag[0] == 0){
                putMsg(0,$flag[1]);
            }
            putMsg(1,$flag[1]);
        }

        $id = input('param.id');
        $role = new RoleModel();
        $roleList =$role->getList();
        array_shift($roleList);
        $this->assign([
            'user' => $user->getOneUser($id),
            'status' => config('user_status'),
            'role' => $roleList
        ]);
        return $this->fetch();
    }

    // 删除用户
    public function userDel()
    {
        $id = input('param.id');

        $role = new UserAdmin();
        $flag = $role->delUser($id);
        if($flag[0] == 0){
            putMsg(0,$flag[1]);
        }
        putMsg(1,$flag[1]);
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'user/useredit',
                'href' => url('user/userEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'user/userdel',
                'href' => "javascript:userDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    /**修改用户密码
     * @return mixed
     */
    public function editpassword(){
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            if (empty($param)) {
                putMsg(0,'系统错误');
            }
            if ($param['new_password'] !== $param['re_new_password']) {
                putMsg(0,'两次输入的密码不相同');
            }

            $user_model = new UserAdmin();
            $user_data = $user_model->getOneUser(session('id'));
            if (is_null($user_data)) {
                putMsg(0,'系统错误');
            }
            if ($user_data['password'] !== encryptSelf($param['old_password'])) {
                putMsg(0,'原始密码错误');
            }
            if ($user_data['password'] === encryptSelf($param['new_password'])) {
               putMsg(0, '新密码不能和旧密码相同');
            }
            $param['password'] = encryptSelf($param['new_password']);
            $data = array('password'=>$param['password']);
            $flag = $user_model->updateStatus($data, session('id'));
            if($flag[0] == 0){
                putMsg(0,$flag[1]);
            }
            putMsg(1,$flag[1]);
        }
        return $this->fetch();
    }

    /**修改用户信息
     * @return mixed
     */
    public function edituserinfo(){
        $id = input('param.id');
        $userAdminModel = new UserAdmin();
        $user = $userAdminModel->getOneUser($id);
            if(request()->isAjax()){
                $param = $this->request->param();
                if (empty($param)) {
                    putMsg(0,'系统错误');
                }
                $old_img =session('head');
                $head = '';
                $arr = array();
                if($param['head'] && $old_img != $param['head'] ){
                    $head = extendImage($param['head'],'adminUserImg',array(array('width'=>300,'height'=>150)));
                    $arr['head'] =$head;
                }
                $arr['real_name'] = $param['real_name'];
                $arr['id'] = $param['id'];
                $result = $userAdminModel->editUser($arr);
                if($result[0] == 0){
                    putMsg(0,'操作失败');
                }
                if($head){
                    session('head',$arr['head']);
                    removeUploadImage(array(
                        array($old_img,300,150)
                    ));
                }
                putMsg(1,$result[1]);
            }
        $this->assign([
            'user'=>$user
        ]);
        return $this->fetch();
    }
}