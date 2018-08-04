<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/7
 * Time: 14:58
 */
namespace app\wx\controller;
use app\wx\model\CourseModel;
use app\wx\model\OrderModel;
use app\wx\model\UserModel;
use think\Db;
use think\Request;

class Buy extends  Common
{
    public function _initialize()
    {

//        dump($_REQUEST['uid']);exit;
        $uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : null;
        if (!$uid) {
            putMsg(0, '系统错误'.__LINE__.$_REQUEST['uid']);
        }
        $GLOBALS['uid'] = $uid;
    }
    /**
     * 创建订单
     */
    public function createOrder(){
        $uid = $GLOBALS['uid'];
        $real_name = $_REQUEST['real_name'];
        $phone = $_REQUEST['phone'];
        $code = $_REQUEST['code'];
        $course_id = intval($_REQUEST['course_id']);
        $type_id = intval($_REQUEST['type_id']); //0 是全额 1 是定金
        $is_agreement = intval($_REQUEST['is_agreement']); //1 没有同意 2 同意
        $userModel = new UserModel();
        $user = $userModel->where(array('id'=>$uid))->find();
        if(!$user){
            putMsg(0,'系统错误');
        }
        if($user['user_status'] != 1){
            putMsg(0,'用户状态不正常，请联系管理员');
        }
        $codeWhere = array(
            'phone'=>$phone,
            'code' =>$code,
            'type'=>'register',
        );
        $code = Db::table('fl_sms')->where($codeWhere)->field('id')->find();
        if(!$code){
            putMsg(0,'验证码错误，请重新验证');
        }
        $courseModel = new CourseModel();
        $course = $courseModel->getOneData(array('id'=>$course_id,'is_publish'=>1));
        if(!$course){
            putMsg(0,'该课程已经下架');
        }
        $data = array();
        $orderModel = new OrderModel();
        $price = $course['courser_price'];
        $money_residue = 0;
        if($type_id != 0){
            $price = $course['depoit_price'];
            $money_residue = ($course['courser_price'] - $course['depoit_price']);
        }
        $payment_type = ($type_id == 1) ? 2 : 1; //订单表中 交款方式 1 是全额 2 是定金
        $data['buy_id'] =  $uid;
        $data['course_id'] = $course_id;
        $data['order_no'] =$orderModel->createOrderNumber();
        $data['price'] = $price;
        $data['addtime'] = time();
        $data['order_status'] = 1;
        $data['tel'] = $phone;
        $data['pay_type'] = 1;
        $data['is_agreement'] =$is_agreement ;
        $data['payment_type'] = $payment_type;
        $data['money_residue'] = $money_residue;
        try{
            $userModel->where(array('id'=>$uid))->update(array('real_name'=>$real_name));
            $orderModel->addData($data);
            $arr = array(
                'order_no'=>$data['order_no'],
                'userId' => $uid
            );
            putMsg(1,$arr);
        }catch (\Exception $e){
            putMsg(0,$e->getMessage());
        }
    }

    /**
     * 支付余额 统一下单
     */
    public function buyResidue(){
        $uid = $GLOBALS['uid'];
        $order_no = $_REQUEST['order_no']; //原有订单
        if(!$order_no){
            putMsg(0,'订单号码不存在');
        }
        $orderModel = new OrderModel();
        $order = $orderModel->getOneData(array('order_no'=>$order_no,'buy_id'=>$uid));
            if(!$order){
                putMsg(0,'订单错误');
            }
          $userModel = new UserModel();
        $openId = $userModel->where(array('id'=>$uid))->field('openid')->find();
        if(!$openId['openid']){
            putMsg(0,'用户状态不正常');
        }
        $money_1 = $order['price'];
        $money_2 = $order['money_residue'];
        $order_number = $orderModel->createOrderNumber();
        $data = array(
            'buy_id'=>$uid,
            'course_id'=>$order['course_id'],
            'order_no'=>$order_number,
            'price'=>$money_2,
            'total_money'=>($money_2 + $money_1),
            'user_order_no'=>$order_no
        );

        $id = Db::table('fl_residue')->insert($data);
        if(!$id){
            putMsg(0,'系统错误'.__LINE__);
        }
//        $one_data = Db::table('fl_residue')->find(array('id'=>$id));
        vendor('Weixinpay');
        $weixinpay = new \Weixinpay();
        $arr = $weixinpay->getXcXUrl($order_number,$money_2,$openId['openid']);
        putMsg(1,$arr);
    }

    /**
     * 发送手机验证码
     */
    public function SmsSend(){
        $uid = $GLOBALS['uid'];
        $where = array('id'=>$uid);
        $userModel = new UserModel();
        $user = $userModel->getOneData($where,'id,user_phone,user_status');
        if(!$user){
            putMsg(0,'用户不存在');
        }
        if($user['user_status'] != 1){
            putMsg(0,'用户状态不正常，请联系管理员');
        }
        $phone = $_REQUEST['phone'];
        if(!$phone){
            putMsg(0,'请输入手机号码');
        }
        $isMatched = preg_match('/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/', $phone);
        if(!$isMatched){
            putMsg(0,'请输入正确的手机号码');
        }
        if(!$user['user_phone']){
            $userModel->where($where)->update(array(
                'user_phone'=>$phone
            ));
        }
        $code = randomCode(6);
        $request = Request::instance();
      $id =  Db::table('fl_sms')->insert(
          array(
              'type'=>'register',
              'ip'=>$request->ip(),
              'code'=>$code,
              'add_time'=>time(),
              'phone'=>$phone
          )
      );
        if(!$id){
            putMsg(0,'系统错误'.__LINE__);
        }
        $result = sendSmsFirst($phone,$code,'SMS_134845029');
        if($result[0] == 0){
            putMsg($result[0],$result[1]);
        }
        putMsg(1,'你的验证码已发送，注意查收');
    }


//    获取订单信息 组装服务通知信息
    public function getOrderInfo(){
        $order_no = $_REQUEST['order_no'];
        $uid = $GLOBALS['uid'];
        if(!$order_no){
            putMsg(0,'订单编号不能为空');
        }
        $OrderModel = new OrderModel();
        $One = $OrderModel->getOneData(array('order_no'=>$order_no,'buy_id'=>$uid),'id,buy_id,course_id,price,oktime,payment_type,tel');
//        if(empty($One)){
//            putMsg(0,'系统错误');
//        }
        $One['oktime'] =date('Y-m-d H:i:s',$One['oktime']) ;
        $One['payment_type'] = ($type == 1) ? '余额支付' : (($One['payment_type'] == 1) ? '全款':'定金(需要支付余款)');
        $course = Db::table('fl_course')->where('id',$One['course_id'])->field('id,courser_name')->find();
        $One['courser_name'] = $course['courser_name'];
        putMsg(1,$One);
    }

    /**
     * 获取access_token 凭证
     */
    public function pushServiceInfo(){
        $access_token = $_REQUEST['access_token'];
        $prepay_id = $_REQUEST['prepay_id'];
        $order_no = $_REQUEST['order_no'];
        $uid = $GLOBALS['uid'];
        $type = $_REQUEST['typed_id']; //余额支付 1 （定金支付 全款支付 2）
        if(!$access_token ||  !$prepay_id || !$order_no){
            putMsg(0,'系统错误');
        }
        $userModel = new UserModel();
        $user = $userModel->getOneData(array('id'=>$uid),'id,openid');
        $OrderModel = new OrderModel();
        if(intval($type) == 1){
            //余额支付
            $order_no_f = Db::table('fl_residue')->where(array('order_no'=>$order_no))->field('id,user_order_no')->find();
            $order_no = $order_no_f['user_order_no'];
        }
        $One = $OrderModel->getOneData(array('order_no'=>$order_no,'buy_id'=>$uid),'id,buy_id,course_id,price,oktime,payment_type,tel');
        $One['oktime'] =date('Y-m-d H:i:s',$One['oktime']) ;
        $One['payment_type'] = ($type == 1) ? '余款支付' : (($One['payment_type'] == 1) ? '全款支付':'定金支付');
        $course = Db::table('fl_course')->where('id',$One['course_id'])->field('id,courser_name')->find();
        $One['courser_name'] = $course['courser_name'];
        $data = array(
            'touser'=>$user['openid'],
            'template_id'=>'Qdsd1_P57rzqOphuIZeIYc3kj4Fig6QhTtYqd-puDok',
            'page'=>'pages/index/index',
            'form_id'=>$prepay_id,
            'data'=> array(
                'keyword1'=>array('value'=>$One['courser_name']),
                'keyword2'=>array('value'=>$One['price']),
                'keyword3'=>array('value'=>$order_no),
                'keyword4'=>array('value'=>$One['payment_type']),
                'keyword5'=>array('value'=>'如有疑问请致电'),
                'keyword6'=>array('value'=>'0755-82529570'),
                'keyword7'=>array('value'=>'上步工业区201栋东(华新站A1出口直行50米)'),
                'keyword8'=>array('value'=>$One['oktime']),
            ),
        );
        $get_token_url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;
//        构造访问 以post方式提交xml到对应的接口url
        $ch =curl_init();
        curl_setopt ( $ch , CURLOPT_URL , $get_token_url ); // 	需要获取的URL地址，也可以在curl_init()函数中设置。
        curl_setopt ( $ch , CURLOPT_HEADER , 0 ); // 	启用时会将头文件的信息作为数据流输出。
        curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 ); //在启用CURLOPT_RETURNTRANSFER的时候，返回原生的（Raw）输出
        curl_setopt ( $ch , CURLOPT_CONNECTTIMEOUT , 10 ); //在尝试连接时等待的秒数。设置为0，则无限等待。
        curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER , false );// 禁止 cURL 验证对等证书
        curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($data));
        $res = curl_exec ( $ch );
        curl_close($ch);
        echo  exit($res);
    }
}