<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-25
 * Time: 8:51
 */
namespace app\wx\controller;
use app\wx\model\QuestionModel;
use app\wx\model\UserModel;
use think\Controller;
use think\Db;

class Question extends  Controller
{
    public function  _initialize(){
       $GLOBALS['uid'] = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : null;
    }
    /**
     * 问题列表页面
     */
    public function list_index(){
         $uid  = $GLOBALS['uid'];
        $questionModel = new QuestionModel();
        $u_question = array();
        if($uid) {
            $userModel = new UserModel();
            $user = $userModel->getOneData(array('id' => $uid));
            if (!$user) {
                putMsg(0, '系统错误' . __LINE__);
            }
//        问答首页头部我的最近 问答
            $u_question = $questionModel
                ->getUpTime(array(
                    'uid' => $uid, 'is_show' => 1, 'is_answer' => 2,
                    'question_time' => $questionModel
                        ->where(array(
                            'uid' => $uid,
                            'is_show' => 1, 'is_answer' => 2))
                        ->max('question_time')
                ), 'question_detail,answer_detail');
        }
// 其他人的相关问答
            $where = array(
                'is_show' => 1,
                'is_answer' => 2
            );
//        接口注意带page
        $otherQuestion = $questionModel->getQuestionAll($where,20,'id,question_detail,like,question_time');
        //per_page 当前页面显示条数 current_page 显示第几页
        foreach($otherQuestion['list'] as $k => $v){
            $otherQuestion['list'][$k]['time_diff'] = get_day_time($v['question_time']);
        }
//      组装数组
        $questionArr = array(
            'my_question'=>$u_question,
            'other_question'=>$otherQuestion['list']
        );
        putMsg(1,$questionArr);
    }

    /**
     * 用户提问表单提交
     */
    public function get_question(){
        $uid  =$GLOBALS['uid'];
        if(!$uid){
            putMsg(0,'系统错误，请登录');
        }
        $userModel = new UserModel();
        $user = $userModel->getOneData(array('id'=>$uid));
        if(!$user){
            putMsg(0,'系统错误'.__LINE__);
        }
        $detail = isset($_REQUEST['detail_question']) ? $_REQUEST['detail_question'] : null;
        if(!$detail){
            putMsg(0,'请描述您的问题');
        }
        $questionModel = new QuestionModel();
        $count = $questionModel
            ->where(array('uid'=>$uid,'is_answer'=>1))
            ->whereTime('question_time', 'today')
            ->count();
        if($count > 5){
            putMsg(0,'您今天提出问题超过五条没有回答，请耐性等待');
        }
        $data = array(
            'question_detail' => $detail,
            'uid'=>$uid,
            'question_time'=>time(),
        );
        $result = $questionModel->addData($data);
        if($result[0] == 0){
            putMsg(0,$result[1].__LINE__);
        }
        putMsg(1,'提问成功，两天内会回复您');
    }

    /**
     * 问题详情页面数据
     */
    public function question_detail(){
        $uid = $GLOBALS['uid'];
        $q_id = isset($_REQUEST['q_id']) ? intval($_REQUEST['q_id']) : null;
        if(!$q_id){
            putMsg(0,'系统错误'.__LINE__);
        }
        $questionModel = new QuestionModel();
        $oneData = $questionModel->getOneData(array('id'=>$q_id,'is_show'=>1),'id,question_detail,uid,question_time,answer_detail,answer_time,like');
        if(!$oneData){
            putMsg(0,'系统错误'.__LINE__);
        }
        $oneData['my_is_like'] = (Db::table('fl_like_question')->where(array('uid'=>$uid,'question_id'=>$q_id))->count()) ? 1 : 0;
        $oneData['question_time'] = get_day_time($oneData['question_time']);
        $oneData['answer_time'] =get_day_time($oneData['answer_time']);
        putMsg(1,$oneData);
    }

    /**
     * 用户问题点赞
     */
    public function  question_like(){
        $uid = $GLOBALS['uid'];
        $q_id = isset($_POST['q_id']) ? intval($_POST['q_id']) : null;
        if(!$uid || !$q_id){
            putMsg(0,'系统错误'.__LINE__);
        }
        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $user = $userModel->getOneData(array('id'=>$uid));
        if(!$user){
            putMsg(0,'系统错误'.__LINE__);
        }
        $res = $questionModel->getOneData(array('id'=>$q_id,'is_show'=>1));
        if(!$res){
            putMsg(0,'未知错误');
        }
       $is_like = Db::table('fl_like_question')->where(array('uid'=>$uid,'question_id'=>$q_id))->count();
        if($is_like){
            putMsg(0,'对不起您已经喜欢过啦');
        }
        $data = array(
            'uid'=>$uid,
            'question_id'=>$q_id,
            'like_time'=>time()
        );
        $result = Db::table('fl_like_question')->insert($data);
            if(!$result){
                putMsg(0,'系统错误'.__LINE__);
            }
        $data1 = array(
            'like'=>$res['like']+1
        );
        Db::table('fl_question')->where('id',$q_id)->update($data1);
        putMsg(1,'谢谢亲！');
    }


}