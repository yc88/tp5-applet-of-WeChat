<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-25
 * Time: 15:00
 */
namespace app\wx\controller;

use app\wx\model\CourseModel;
use app\wx\model\OrderModel;
use app\wx\model\QuestionModel;
use think\Db;

class User extends Common
{
    public function _initialize()
    {
        $uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : null;
        if (!$uid) {
            putMsg(0, '系统错误');
        }
        $GLOBALS['uid'] = $uid;
    }

    /**
     * 我的课程 我的订单
     */
    public function my_courses_order()
    {
//        接口注意带 page 获取更多
        $uid = $GLOBALS['uid'];
        if (!$uid) {
            putMsg(0, '系统错误');
        }
        $orderModel = new OrderModel();
        $courseModel = new CourseModel();
        $data = $orderModel->getAllList(array('buy_id' => $uid, 'is_del' => 0,'order_status'=>2), 10, 'id,course_id,order_no,price,payment_type,oktime,money_residue');
        foreach ($data['list'] as $k => $v) {
            $data['list'][$k]['oktime'] = date('Y-m-d H:i:s', $v['oktime']);
            $data['list'][$k]['course_name'] = $courseModel->getOneData($v['course_id'], 'courser_name')['courser_name'];
            $data['list'][$k]['is_hidden'] = $v['payment_type'] == 1 ? true : false;
        }
        putMsg(1, $data['list']);
    }

    /**
     * 我的提问数据列表
     */
    public function my_question()
    {
        $uid = $GLOBALS['uid'];
        if (!$uid) {
            putMsg(0, '系统错误');
        }
        $questionModel = new QuestionModel();
//        我的问答 没有回复
        $no_answer = $questionModel->getAllList(array('uid' => $uid, 'is_answer' => 1, 'is_show' => 1),10, 'id,question_detail,question_time');
        foreach ($no_answer['list'] as $k1 => $v1) {
            $no_answer['list'][$k1]['question_time'] = get_day_time($v1['question_time']);
        }
//我的 问题 已经回答
        $answer = $questionModel->getAllList(array('uid' => $uid, 'is_answer' => 2, 'is_show' => 1), 10,'id,question_detail,question_time,answer_detail,answer_time');
foreach ($answer['list'] as $k => $v) {
    $answer['list'][$k]['question_time'] = get_day_time($v['question_time']);
    $answer['list'][$k]['answer_time'] = get_day_time($v['answer_time']);
}
        $arr = array(
            'no_answer' => $no_answer['list'],
            'answer' => $answer['list']
        );
        putMsg(1, $arr);
    }

    /**
     * 关于我们资讯
     */
    public function about_us(){
        $uid = $GLOBALS['uid'];
        if (!$uid) {
            putMsg(0, '系统错误');
        }
        $info_cate = 1;
        $info = Db::table('fl_info')->where(array('info_cate'=>$info_cate,'is_show'=>1))->field('id,info_name,info_img,info_detail')->order('id ASC ')->select();
        foreach($info as $k =>$v){
            $info[$k]['info_detail'] = strip_tags($v['info_detail']);
            $info[$k]['info_img'] = getImgName($v['info_img'],1200,600);
        }
        putMsg(1,$info);
    }
}