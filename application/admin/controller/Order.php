<?php
/**订单管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-18
 * Time: 9:45
 */
namespace app\admin\controller;
use app\admin\model\CourseCateModel;
use app\admin\model\CourseModel;
use app\admin\model\OrderModel;
use app\admin\model\UserModel;
use think\Db;
use think\Request;

class Order extends Base
{
    /**
     * 订单管理首页 已经付款的订单
     */
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['order_no'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['phone'])) {
                $where['tel'] = ['like', '%' . $param['phone'] . '%'];
            }
            if (!empty($param['begin_oktime'])) {
                $where['oktime'] = ['gt',  strtotime($param['begin_oktime'])];
            }
            if (!empty($param['end_oktime'])) {
                $where['oktime'] = ['lt',  strtotime($param['end_oktime'])];
            }
            if (!empty($param['begin_oktime']) && !empty($param['end_oktime'])) {
                if($param['begin_oktime'] > $param['end_oktime']){
                    $where['oktime'] = array();
                }
                $where['oktime'] = array('between',[strtotime($param['begin_oktime']),strtotime($param['end_oktime'])]) ;
            }
            if (!empty($param['order_status'])) {
                $where['order_status'] = ['eq', intval($param['order_status'])];
            }
            if (!empty($param['payment_type'])) {
                $where['payment_type'] = ['eq', intval($param['payment_type'])];
            }
            $where['is_del'] = 0;
            $where['order_status'] =2;
            $user_order = new OrderModel();
            $user = new UserModel();
            $selectResult = $user_order->getIndexList($where, $offset, $limit);
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['user_name'] =json_decode($user->getUserName($vo['buy_id'],'user_name')['user_name']);
                $selectResult[$key]['user_phone'] = $user->getUserName($vo['buy_id'],'user_phone')['user_phone'];
                $selectResult[$key]['oktime'] = date('Y-m-d H:i:s', $vo['oktime']);
                $selectResult[$key]['payment_type'] = ($vo['payment_type'] == 1) ? '全额':'定金';
                $selectResult[$key]['order_status'] = ($vo['order_status'] == 1) ? "<button  class='btn btn-outline btn-danger'>未付款</button>":"<button  class='btn btn-outline btn-primary'>已付款</button>";
                $selectResult[$key]['input'] = "<input type='checkbox' value='{$vo['id']}' name='status_ok' onclick='son_checked()'>"; //excel educe
                $selectResult[$key]['operate'] = showOperate($this->makeOrderButton($vo['id']));
            }
            $return['total'] = $user_order->getListCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
      return $this->fetch();
    }
    /**
     * 订单详情
     */
    public function orderdetail(){
        $id = input('param.');
        $orderModel = new OrderModel();
        $courseModel = new CourseModel();
        $userModel = new UserModel();
        $courseCateModel = new CourseCateModel();
        $data = objToArray($orderModel->getOne($id)); //order information
        if(empty($data)){
            $this->error('系统错误');
        }
        $order_no = $data['order_no'];

        $residue = Db::table('fl_residue')->where(array('user_order_no'=>$order_no,'status'=>2))->find();
        if($residue){ //Balance payment order
            $residue['edit_author'] = Db::table('fl_user_admin')->where('id',$residue['edit_author'])->field('id,user_name')->find()['user_name'];
        }
        $course = objToArray($courseModel->getOne($data['course_id'])); //course information
        $user = objToArray($userModel->getOne($data['buy_id'])); //user information
        $user['user_name'] = json_decode($user['user_name']);
        $course['classify_name'] =$courseCateModel->getOne($course['classify_id'])['classify_name'];
        $this->assign([
            'data'=>$data,
            'user'=>$user,
            'course'=>$course,
            'residue' => $residue
        ]);
       return $this->fetch();
    }
    /**
     * 订单删除
     */
    public function delorder(){
        $data = input('param.');
        if(!$data){
            putMsg(0,'系统错误');
        }
        $data['is_del'] = 1;
        $orderModel = new OrderModel();
        $result = $orderModel->ediDataOne($data,url('order/index'));
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,'删除成功');
    }

    /**
     * 添加线下支付 改变订单状态
     */
    public function addresidue(){
        if(request()->isAjax()){
            $order_no = input('param.order_no');
            if(!$order_no){
                putMsg(0,'系统错误');
            }
            $orderModel = new OrderModel();
            $One = $orderModel->getOne(array('order_no'=>$order_no));
            if(!$One){
                putMsg(0,'该数据出问题啦');
            }
            $course = Db::table('fl_course')->where('id',$One['course_id'])->field('id,depoit_price,courser_price,courser_name')->find();
            $deposit_money = $course['courser_price']-$course['depoit_price'];
            $money_1 = $One['money_residue']; //residues money
            $money_2 = $One['price']; //payment deposit
            if($deposit_money != $money_1 || $money_2 != $course['depoit_price']){
                putMsg(0,'金额数目出现问题，请联系管理员');
            }
        try{
            $arr = array(
                'buy_id'=>$One['buy_id'],
                'course_id'=>$One['course_id'],
                'order_no'=>$orderModel->createOrderNumber('OL'),
                'price'=>$money_1,
                'total_money'=>$course['courser_price'],
                'user_order_no'=>$order_no,
                'oktime'=>time(),
                'status'=>2,
                'pay_type'=>3,
                'edit_author'=>session('id')
            );
            $id = Db::table('fl_residue')->insert($arr);
            if(!$id){
                putMsg(0,'系统错误');
            }
            $data = array(
                'price'=>$course['courser_price'],
                'payment_type'=>1,
                'money_residue'=>0,
                'residue_id'=>$id
            );
           $result =$orderModel->where(array('order_no'=>$order_no,'id'=>$One['id']))->update($data);
            if($result != false){
                sendSmsFirst($One['tel'],$course['courser_name'],'SMS_134328156');
                putMsg(1,'操作成功');
            }
            putMsg(0,'系统错误');
        }catch (\Exception $e){
            putMsg(0,$e->getMessage().__LINE__);
        }

        }
    }

    /**
     * 订单excel 导出
     */
    public function orderexcel(){
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误','',2);
        }
        $xlsCell = array(
            array('id','订单序列'),
            array('buy_id','购买用户'),
            array('classify_name','课程类型'),
            array('course_id','购买课程'),
            array('order_no','订单编号'),
            array('price','价格'),
            array('oktime','付款时间'),
            array('addtime','购买时间'),
            array('order_status','付款状态'),
            array('tel','电话号码'),
            array('pay_type','支付方式'),
            array('pay_type_no','支付方式订单号'),
            array('payment_type','交款方式'),
            array('is_del','是否删除状态'),
            array('money_residue','定金剩余支付金额'),
            array('residue_id','余额支付ID'),
            array('parting','分割线'),
        );
        $xlsCell_1 = array(
            //  定金的情况下 支付余款的订单
            array('residus_id','余额支付ID'),
            array('residus_buy_id','购买用户'),
            array('residus_course_id','购买课程'),
            array('residus_order_no','订单编号'),
            array('residus_price','余款'),
            array('residus_total_money','全款'),
            array('residus_user_order_no','用户定金编号'),
            array('residus_oktime','用户付款时间'),
            array('residus_status','付款状态'),
            array('residus_pay_type','付款方式'),
            array('residus_pay_type_no','微信支付订单编号'),
            array('residus_edit_author','余款线下支付时编辑者'),
        );
        $orderModel = new OrderModel();
        $result = $orderModel->getOrderExcel($id);
        if(!$result){
            $this->error('系统错误');
        }
        $arr = array();
        if(count($result) == count($result,1)){ //判断一维数组 true
            if($result['parting'] == '||'){ //单条数据的时候
                $xlsCell = array_merge($xlsCell,$xlsCell_1);
            }
            $fileName = $result['order_no'];
            $arr[] = $result;
            $result = $arr;
        }else{ //多数据导出时候
            $xlsCell = array_merge($xlsCell,$xlsCell_1);
            $fileName = 'AllOrder_'.date('Ymd');
        }
      exportExcel($xlsCell,$result,config('fileName_save').$fileName.'_'.time().'.xls');
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeOrderButton($id)
    {
        return [
            '详情' => [
                'auth' => 'order/orderdetail',
                'href' => url('order/orderdetail', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-search'
            ],
            '删除' => [
                'auth' => 'order/delorder',
                'href' => "javascript:orderDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
              '导出' => [
               'auth' => 'order/orderexcel',
               'href' => url('order/orderexcel', ['id' => $id]),
               'btnStyle' => 'primary',
               'icon' => 'fa fa-cloud-download'
           ],
        ];
    }
}