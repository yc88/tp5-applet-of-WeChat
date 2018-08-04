<?php
/** 用户会员管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-18
 * Time: 9:07
 */

namespace app\admin\controller;
use app\admin\model\UserModel;

class Member extends  Base
{
    //会员管理首页
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['user_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new UserModel();
            $selectResult = $user->getIndexList($where, $offset, $limit);
            $status = config('user_status');
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['user_name'] = json_decode($vo['user_name']);
                $selectResult[$key]['user_login_time'] = date('Y-m-d H:i:s', $vo['user_login_time']);
                $selectResult[$key]['addtime'] = date('Y-m-d H:i:s', $vo['addtime']);
                $selectResult[$key]['user_status'] = $status[$vo['user_status']];
                $selectResult[$key]['user_source'] = ($vo['user_source'] =='wx') ? '微信小程序':(($vo['user_source'] == 'app') ? 'app':'web');
                $selectResult[$key]['operate'] = showOperate($this->makeMemberButton($vo['id']));
            }
            $return['total'] = $user->getListCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }
    /**
     *会员详情
     */
    public function memberdetail(){
        $id = input('param.');
        $userModel = new UserModel();
        $user = objToArray($userModel->getOne($id));
        $user['user_name'] = json_decode($user['user_name']);
        $this->assign([
            'user'=>$user
        ]);
        return $this->fetch();
    }
    /**
     *新增会员
     */
    public function addmember(){
        $this->error('暂不能操作');
         return $this->fetch();
    }
    /**
     *编辑会员
     */
    public function editmember(){
        $this->error('暂不能操作');
         return $this->fetch();
    }
    /**
     *删除会员
     */
    public function delmember(){
        $this->error('暂不能操作');
         return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeMemberButton($id)
    {
        return [
            '详情' => [
                'auth' => 'member/memberdetail',
                'href' => url('member/memberdetail', ['id' => $id]),
                'btnStyle' => 'danger',
                'icon' => 'fa fa-search-plus'
            ],
            '编辑' => [
                'auth' => 'member/editmember',
                'href' => url('member/editmember', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'member/delmember',
                'href' => "javascript:userDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
