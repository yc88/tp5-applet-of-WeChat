<?php
/**微信支付相关
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\wx\controller;
use app\wx\model\CourseModel;
use app\wx\model\OrderModel;
use app\wx\model\UserModel;
use think\Db;
use think\Log;

class Weipay extends  Common
{
    public function _initialize(){
        //php 判断http还是https
    $this->http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }

    /**
     * 微信支付接口 统一下单接口
     */
    public function wxPay(){
        $order_no = trim($_REQUEST['order_no']);
        $uid = isset($_POST['userId']) ? intval($_POST['userId']) : null;
        if(!$order_no || !$uid){
            putMsg(0,'系统错误'.__LINE__);
        }
        $orderModel = new OrderModel();
        $order = $orderModel->getOneData(array('order_no'=>$order_no));
        if(!$order){
            putMsg(0,'订单不存在');
        }
        if($order['order_status'] != 1){
            putMsg(0,'订单支付状态有误');
        }
        $userModel = new UserModel();
        $openId = $userModel->where(array('id'=>$uid))->field('openid')->find();
        if(!$openId['openid']){
            putMsg(0,'用户状态不正常');
        }
        if($order['price'] == 0 || !$order['price'] ){
            putMsg(0,'系统错误');
        }
        /**
         * 统一下单
         */
        vendor('Weixinpay');
        $weixinpay = new \Weixinpay();
        $arr = $weixinpay->getXcXUrl($order_no,$order['price'],$openId['openid']);
        putMsg(1,$arr);
    }
    //构建字符串
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 微信回调 异步通知地址
     */
    public function wei_xin_notify()
    {
        Vendor("Weixinpay");
        $weixinpay = new \Weixinpay();
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $weixinpay->saveData($xml);
        $path = "./log/";
        if (!is_dir($path)){
            mkdir($path,0777);  // 创建文件夹test,并给777的权限（所有权限）
        }
        $file = $path."weixin_".date("Ymd").".log";    // 写入的文件
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($weixinpay->returnCheckSign() == FALSE) {
            file_put_contents($file,'验签失败'.PHP_EOL,FILE_APPEND);
            $weixinpay->setReturnParameter("return_code", "FAIL");//返回状态码
            $weixinpay->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {
           file_put_contents($file,'进来了，验证签名成功'.PHP_EOL,FILE_APPEND);
            $weixinpay->setReturnParameter("return_code", "SUCCESS");//设置返回码
            $weixinpay->setReturnParameter("return_msg", "OK");//设置返回码
            //成功的业务逻辑
            $data = $weixinpay->getData();
            if ($data['return_code'] != 'SUCCESS') {
                file_put_contents($file,'FAIL|return_code not success'.PHP_EOL,FILE_APPEND);
                return false;
            }
            file_put_contents($file, date('Y-m-d H:i:s').'=>' .json_encode($data) . PHP_EOL, FILE_APPEND);
            $orderNo = $data['out_trade_no'];
            $transaction_id = $data['transaction_id'];
            $total_fee = $data['total_fee']; //金额
            $orderModel = new OrderModel();
            $courseModel = new CourseModel();
            $order1 = $orderModel->getOneData(array('order_no' => $orderNo));
            $order2 = Db::table('fl_residue')->where(array('order_no' => $orderNo))->find();
            file_put_contents($file, date('Y-m-d H:i:s').'=>' .json_encode($order2) . PHP_EOL, FILE_APPEND);
            if($order1){ //定金支付
                $arr['oktime'] = time();
                $arr['order_status'] = 2;
                $arr['pay_type'] = 1;
                $arr['pay_type_no'] = $transaction_id; //微信支付订单号
                $couresId = $order1['course_id'];
                $tel = $order1['tel'];
                if($order1['order_status'] != 2){
                    $order_status = $orderModel->where(array('order_no' => $orderNo))->update($arr);
                    $res = $courseModel->getOneData(array('id' => $couresId), 'id,buy_number,courser_name');
                    $courseTitle =$res['courser_name'];
                    $data1 = array(
                        'buy_number' => $res['buy_number'] + 1
                    );
                    $course_status = $courseModel->where('id', $couresId)->update($data1);
                    if($total_fee/100 != $order1['price']){
                        file_put_contents($file, '金额数据不对' . $total_fee.'//'.$order1['price'] . PHP_EOL, FILE_APPEND);
                        return false;
                    }
                    if (!$order_status) {
                        file_put_contents($file, '订单状态修改错误' . $orderModel->getLastSql() . PHP_EOL, FILE_APPEND);
                        return false;
                    }
                    if (!$course_status) {
                        file_put_contents($file, '购买课程人数修改失败' . $courseModel->getLastSql() . PHP_EOL, FILE_APPEND);
                        return false;
                    }
                    if($order1 && $order_status && $course_status){
                        $result = sendSmsFirst($tel,$courseTitle,'SMS_134326560');
                        if($result[0] == 0){
                            file_put_contents($file, '短信通知失败' . $result[1] . PHP_EOL, FILE_APPEND);
                            return false;
                        }
                        return false;
                    }
                }
            }elseif($order2){ //余额支付
                if($total_fee/100 != $order2['price']){
                    file_put_contents($file, '金额数据不对' . $total_fee.'//'.$order2['price'] . PHP_EOL, FILE_APPEND);
                    return false;
                }
                 $arr_2 = array(
                     'oktime'=>time(),
                     'status'=>2,
                     'pay_type_no'=>$transaction_id
                 );
                if($order2['status'] != 2){
                    $status = Db::table('fl_residue')->where(array('order_no' => $orderNo))->update($arr_2);
                    //
                    $status_order = $orderModel->where(array('order_no' => $order2['user_order_no']))->update(
                        array(
                            'residue_id'=>$order2['id'],
                            'payment_type'=>1,
                            'money_residue'=>0,
                            'price'=>$order2['total_money'],
                        )
                    );

                    $tel = $orderModel->where(array('order_no' => $order2['user_order_no']))->find()['tel'];
                    $courseTitle =$courseModel->getOneData(array('id'=>$order2['course_id']),'id,courser_name')['courser_name'];
                    if (!$status) {
                        file_put_contents($file, '余额状态修改错误' . $orderModel->getLastSql() . PHP_EOL, FILE_APPEND);
                        return false;
                    }
                    if (!$status_order) {
                        file_put_contents($file, '余额付款下订单状态修改失败' . $courseModel->getLastSql() . PHP_EOL, FILE_APPEND);
                        return false;
                    }
                    if($order2 && $status && $status_order ){
                        $result = sendSmsFirst($tel,$courseTitle,'SMS_134328156');
                        if($result[0] == 0){
                            file_put_contents($file, '短信通知失败' . $result[1] . PHP_EOL, FILE_APPEND);
                            return false;
                        }
                        return false;
                    }
                }
            }

        }
        $xml =$weixinpay->returnXml();
        echo $xml;
    }

}