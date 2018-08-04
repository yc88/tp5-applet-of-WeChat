<?php
/** 咨询模块
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-17
 * Time: 11:02
 */
namespace app\admin\controller;
use app\admin\model\QuestionModel;
use app\admin\model\UserAdmin;
use app\admin\model\UserModel;

class Question extends  Base
{
    //咨询列表首页
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['question_detail'] = ['like', '%' . trim($param['searchText']) . '%'];
            }
            $questionModel = new QuestionModel();
            $userModel = new UserModel();
            $selectResult = $questionModel->getIndexList($where, $offset, $limit);
            // todo 提问者用户名获取
            foreach($selectResult as $key=>$vo){
                $vo['uid'] =json_decode($userModel->getOne($vo['uid'])['user_name']);
                $vo['question_time'] = date('Y-m-d H:i:s',$vo['question_time']);
                $vo['is_answer'] = $vo['is_answer'] == 1 ? '否' : '是';
                $vo['is_show'] = $vo['is_show'] == 1 ? '是' : '否';
                $selectResult[$key]['operate'] = showOperate($this->makeQuestionButtonAll($vo['id'],'question/questionback','question/delquestion'));
            }
            $return['total'] = $questionModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        return $this->fetch();
    }
    //咨询回复
    public function questionBack(){
        $id = input('param.id');
        $questionModel = new QuestionModel();
        $userModel = new UserModel();
        $userAdminModel = new UserAdmin();
        if(request()->isAjax()){
            $data = input('param.');
            $arr['id'] = $data['id'];
            $arr['is_show'] = $data['is_show'];
            if(isset($data['answer_detail'])){
                $arr['answer_detail'] = $data['answer_detail'];
            }
            $result = $questionModel->getOne($arr['id']);
            if(isset($data['answer_detail']) && $result['is_answer'] == 2){
                putMsg(0,'系统错误');
            }
            if(isset($arr['answer_detail'])){
                $arr['answer_author'] = session('id');
                $arr['answer_time'] = time();
                $arr['is_answer'] = 2;
            }

            $result=$questionModel->ediDataOne($arr,url('question/index/'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
//            if(isset($data['answer_detail']) && isset($arr['answer_detail']) && $result[0] == 1){
//                //短信回复通知
//            }
            putMsg(1,'操作成功');
        }
        if($id){
            $data = $questionModel->getOne($id);
            $data['question_name'] =json_decode($userModel->getOne($data['uid'])['user_name']);
            $data['answer_author'] = $userAdminModel->getOneUser($data['answer_author'])['user_name'];
            $this->assign([
                'data'=>$data
            ]);
        }
        return $this->fetch();
    }

    //咨询 删除
    public function delquestion(){
        $id = input('param.id');
//        putMsg(0,'暂时不能删除');
        if(!$id){
            putMsg(0,'系统错误');
        }
        $questionModel = new QuestionModel();
        $result = $questionModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,'删除成功');
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
private  function makeQuestionButtonAll($id,$editUrl,$delUrl)
    {
        return [
            '回复' => [
                'auth' => $editUrl,
                'href' => url($editUrl, ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => $delUrl,
                'href' => "javascript:roleDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}