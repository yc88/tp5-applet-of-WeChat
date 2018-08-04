<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-18
 * Time: 10:28
 */
namespace app\admin\model;
use think\Db;

class OrderModel extends  CommonModel
{
    protected  $name = 'user_order';

    /**订单组装获取
     * @param null $id
     * @param string $order
     * @return mixed
     */
    public function getOrderExcel($id = null,$order  = 'id ASC, oktime ASC'){
        if(strpos($id,",")){ //多个数据导出
            $result = $this->where(array('id'=>array('in',$id)))->order($order)->select();
            $result = objToArray($result);
            for($i = 0; $i < count($result); $i++){
                $courseModel = new CourseModel();
                $residueModel = new ResidueModel();
                $course = $courseModel->getOne(array('id'=>$result[$i]['course_id']));
                $result[$i]['classify_name'] = Db::table('fl_course_cate')->where(array('id'=>$course['classify_id']))->field('id,classify_name')->find()['classify_name'];
                $user = Db::table('fl_user')->where('id',$result[$i]['buy_id'])->field('id,user_name,real_name')->find();
                $result[$i]['buy_id'] =$user['real_name'];
                $result[$i]['course_id'] = $course['courser_name'];
                $result[$i]['oktime'] =date("Y年m月d日 H:i:s",$result[$i]['oktime']); //付款时间
                $result[$i]['addtime'] =date("Y年m月d日 H:i:s",$result[$i]['addtime']); //付款时间
                $result[$i]['order_status'] = $result[$i]['order_status'] == 1 ? '未付款' : '已付款';
                $result[$i]['pay_type'] = $result[$i]['pay_type'] == 1 ? '微信支付' : '支付宝支付';
                $result[$i]['payment_type']  = $result[$i]['payment_type'] == 1 ? '全款':'定金';
                $result[$i]['is_del']  = $result[$i]['is_del'] == 1 ? '已删除':'未删除';
                $is_full_pay = $residueModel->cheked_is_full_pay($result[$i]['order_no']);
                $result[$i]['parting'] = $is_full_pay ? '' : '';
                if($is_full_pay !== false){
                    $result[$i]['residus_id'] = $is_full_pay['id'];
                    $result[$i]['residus_buy_id'] = $user['real_name'];
                    $result[$i]['residus_course_id'] = $course['courser_name'];
                    $result[$i]['residus_order_no'] = $is_full_pay['order_no'];
                    $result[$i]['residus_price'] = $is_full_pay['price'];
                    $result[$i]['residus_total_money'] = $is_full_pay['total_money'];
                    $result[$i]['residus_user_order_no'] = $is_full_pay['user_order_no'];
                    $result[$i]['residus_oktime'] = date("Y年m月d日 H:i:s",$is_full_pay['oktime']); //付款时间;
                    $result[$i]['residus_status'] = $is_full_pay['status'] == 1 ? '未付款' : '已付款';
                    $result[$i]['residus_pay_type'] = (($is_full_pay['pay_type'] == 1) ? '微信支付' : ($is_full_pay['pay_type'] == 2 ? '支付宝支付':'线下支付'));
                    $result[$i]['residus_pay_type_no'] = $is_full_pay['pay_type_no'];
                    $result[$i]['residus_edit_author'] = '';
                    if($is_full_pay['pay_type'] == 3){
                        $result[$i]['residus_edit_author'] = Db::table('fl_user_admin')->where('id',$is_full_pay['edit_author'])->field('id,user_name')->find()['user_name'];
                    }
                }else{
                    $result[$i]['residus_id'] = '';
                    $result[$i]['residus_buy_id'] ='';
                    $result[$i]['residus_course_id'] ='';
                    $result[$i]['residus_order_no'] ='';
                    $result[$i]['residus_price'] ='';
                    $result[$i]['residus_total_money'] = '';
                    $result[$i]['residus_user_order_no'] = '';
                    $result[$i]['residus_oktime'] =''; //付款时间;
                    $result[$i]['residus_status'] = '';
                    $result[$i]['residus_pay_type'] ='';
                    $result[$i]['residus_pay_type_no'] ='';
                    $result[$i]['residus_edit_author'] = '';
                    $result[$i]['residus_edit_author'] = '';
                }
            }
        }else{ //单数据的导出
            $result = $this->where(array('id'=>$id))->find();
            $courseModel = new CourseModel();
            $residueModel = new ResidueModel();
            $course = $courseModel->getOne(array('id'=>$result['course_id']));
            $result['classify_name'] = Db::table('fl_course_cate')->where(array('id'=>$course['classify_id']))->field('id,classify_name')->find()['classify_name'];
            $user = Db::table('fl_user')->where('id',$result['buy_id'])->field('id,user_name,real_name')->find();
            $result['buy_id'] =$user['real_name'];
            $result['course_id'] = $course['courser_name'];
            $result['oktime'] =date("Y年m月d日 H:i:s",$result['oktime']); //付款时间
            $result['addtime'] =date("Y年m月d日 H:i:s",$result['addtime']); //付款时间
            $result['order_status'] = $result['order_status'] == 1 ? '未付款' : '已付款';
            $result['pay_type'] = $result['pay_type'] == 1 ? '微信支付' : '支付宝支付';
            $result['payment_type']  = $result['payment_type'] == 1 ? '全款':'定金';
            $result['is_del']  = $result['is_del'] == 1 ? '已删除':'未删除';
            $is_full_pay = $residueModel->cheked_is_full_pay($result['order_no']);
            $result['parting'] = $is_full_pay ? '||' : '';
            if($is_full_pay !== false){
                $result['residus_id'] = $is_full_pay['id'];
                $result['residus_buy_id'] =$user['real_name'];
                $result['residus_course_id'] = $course['courser_name'];
                $result['residus_order_no'] = $is_full_pay['order_no'];
                $result['residus_price'] = $is_full_pay['price'];
                $result['residus_total_money'] = $is_full_pay['total_money'];
                $result['residus_user_order_no'] = $is_full_pay['user_order_no'];
                $result['residus_oktime'] = date("Y年m月d日 H:i:s",$is_full_pay['oktime']); //付款时间;
                $result['residus_status'] = $is_full_pay['status'] == 1 ? '未付款' : '已付款';;
                $result['residus_pay_type'] = ($is_full_pay['pay_type'] == 1 ? '微信支付' : ($is_full_pay['pay_type'] == 2 ? '支付宝支付':'线下支付'));
                $result['residus_pay_type_no'] = $is_full_pay['pay_type_no'];
                $result['residus_edit_author'] = '';
                if($is_full_pay['pay_type'] == 3){
                    $result['residus_edit_author'] = Db::table('fl_user_admin')->where('id',$is_full_pay['edit_author'])->field('id,user_name')->find()['user_name'];
                }
            }
        }
        return objToArray($result);
    }

}