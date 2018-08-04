<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
// 应用公共文件

/**数据返回
 * @param $status
 * @param null $info
 * @param null $data
 */
function putMsg($status, $info = null, $data = null)
{
    header('Content-Type:application/json;charset=utf-8');
    if (is_array($info) || is_object($info)) {
        $data = $info;
        $info = null;
    }
    $result = array(
        'status' => $status
    );
    if (!empty($info)) {
        $result['msg'] =$info;
    }
    if (isset($data)) {
        $result['data'] = $data;
    }
    ob_end_clean();
    echo json_encode($result);
    exit();
}

/**列表页面数据返回 一维
 * @param $status
 * @param null $info
 * @param null $data
 */
function putMsg1($status, $info = null, $data = null)
{
    header('Content-Type:application/json;charset=utf-8');
    if (is_array($info) || is_object($info)) {
        $data = $info;
        $info = null;
    }
    $result = array(
        'status' => $status
    );
    if (!empty($info)) {
        $result['msg'] =$info;
    }
    if (isset($data)) {
        $result = $data;
    }
    ob_end_clean();
    echo json_encode($result);
    exit();
}

/**md5+加密字符串加密函数
 * @param $password
 * @return string
 */
function encryptSelf($password){
     $salt = config('salt');
    return md5($password.$salt);
}

/**获取图片预览地址
 * @param $fileName
 * @param null $width
 * @param null $height
 * @param string '' $subFile
 * @return string
 */
function getImgName($fileName, $width = null, $height = null, $subFile = '')
{
    if(!$fileName){
        return false;
    }
    $file = explode('.', $fileName);
    $name = $file[0];
    $ext = $file[1];
    if (!$width && !$height) {
        return config('img_url').$subFile . $fileName;
    } else {
        return config('img_url').$subFile . '/' . $name . '_' . $width . 'x' . $height . '.' . $ext;
    }
}

/**比较两个时间戳相差多少
 * @param $begin_time
 * @param $end_time
 * @return array
 */
function timediff($begin_time,$end_time){
    if($begin_time < $end_time){
        $startTime = $begin_time;
        $endTime = $end_time;
    }else{
        $startTime = $end_time;
        $endTime = $begin_time;
    }
    //计算天数
    $timeDiff = $endTime-$startTime;
    $days = intval($timeDiff/86400);
    //计算小时数
    $remain = $timeDiff%86400;
    $hours = intval($remain/3600);
    //计算分钟数
    $remain = $remain%3600;
    $mins = intval($remain/60);
    //计算秒数
    $secs = $remain%60;
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
    return $res;
}

/**获取一天以内的开始 结束时间
 * @param int $type
 * @return array
 */
function to_day_time($type = 1){
  $begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
  $endtime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
    $time_arr = [];
    switch($type){
        case 1 :
            $time_arr['begintime'] = strtotime($begintime);
            $time_arr['endtime'] = strtotime($endtime);
            break;
        case 2 :
            $time_arr['begintime'] = $begintime;
            $time_arr['endtime'] = $begintime;
            break;
    }
return $time_arr;
}

/**获取距离当前好久的时间
 * @param $end_time
 * @return string
 */
function get_day_time($end_time){
    $time =  timediff(time(),$end_time);
    $title = ($time['day']) ? $time['day'].'天前' : (($time['hour']) ? $time['hour'].'小时前':($time['min'] ? $time['min'].'分钟前' : $time['sec'].'秒前'));
    return $title;
}
/**生成随机码
 * @param $size
 * @param bool|false $isAlpha
 * @return string
 */
function randomCode($size, $isAlpha = false)
{
    if ($isAlpha) {
        $str = "023456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str) - 1;
        $code = '';
        for ($i = 0; $i < $size; $i++) {
            $code .= $str[mt_rand(0, $max)];
        }
    } else {
        $base = pow(10, $size - 1);
        $code = (string)mt_rand($base, $base * 10 - 1);
    }
    return $code;
}

/**取得AcsClient
 * @return DefaultAcsClient|null
 */
function getAcsClient(){
    $acsClient = null;
    vendor('api_sdk.vendor.autoload');
    // 加载区域结点配置
    Config::load();
    //产品名称:云通信流量服务API产品,开发者无需替换
    $product = "Dysmsapi";
    //产品域名,开发者无需替换
    $domain = "dysmsapi.aliyuncs.com";
    // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
    $accessKeyId = "LTAIqXGHo8j8JBca"; // AccessKeyId
    $accessKeySecret = "dmRKk4LRISkvBP3cxhUCgDdJLdEQyu"; // AccessKeySecret
    // 暂时不支持多Region
    $region = "cn-hangzhou";
    // 服务结点
    $endPointName = "cn-hangzhou";
    if($acsClient == null) {
        //初始化acsClient,暂不支持region化
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);
    }
    return $acsClient;
}

/**发送短信
 * @param $PhoneNumbers
 * @param $code
 * @param string $type_code
 * @param null $outId
 * @param null $time1
 * @param null $time2
 * @param null $time3
 * @return mixed|SimpleXMLElement
 */
function sendSms($PhoneNumbers,$code,$type_code = "SMS_133625004",$outId = null,$time1=null,$time2 = null,$time3 = null){
    vendor('api_sdk.vendor.autoload');
    // 加载区域结点配置
    Config::load();
    // 初始化SendSmsRequest实例用于设置发送短信的参数
    $request = new SendSmsRequest();
    //可选-启用https协议
    //$request->setProtocol("https");
    // 必填，设置短信接收号码
    $request->setPhoneNumbers($PhoneNumbers);
    // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $request->setSignName("搜糖科技");
    // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $request->setTemplateCode($type_code);

    if($type_code == 'SMS_134321635'){
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值 课程通知
            "code"=>$code, //课程名称
            "time1"=>$time1, //开始时间
            "time2" =>$time2, //课程终止时间
            "time3" =>$time3, //开始时间
            "product"=>"ytx" //未知
        ), JSON_UNESCAPED_UNICODE));
    }else{
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code"=>$code, //验证码
            "product"=>"ytx" //未知
        ), JSON_UNESCAPED_UNICODE));
    }
    // 可选，设置流水号
    $request->setOutId($outId); //订单号码？
    // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
    $request->setSmsUpExtendCode("1234567");
    // 发起访问请求
    $acsResponse = getAcsClient()->getAcsResponse($request);
    return $acsResponse;
}

/**发送验证码
 * @param $PhoneNumbers
 * @param $code
 * @param string $type_code
 * @param null $outId
 * @param null $time1
 * @param null $time2
 * @param null $time3
 * @return array
 */
function sendSmsFirst($PhoneNumbers,$code,$type_code = "SMS_133625004",$outId = null,$time1=null,$time2 = null,$time3 = null){
            $obj = sendSms($PhoneNumbers,$code,$type_code,$outId,$time1,$time2,$time3);
            switch($obj->Code){
                case 'OK':
                    $arr = array(1,'成功');
                    break;
                case 'isv.BUSINESS_LIMIT_CONTROL':
                    $arr = array(0,'操作太频繁');
                    break;
                default:
                    if($type_code == 'SMS_134321635'){ //通知短信
                        $arr =  array(0,$obj->Code);
                    }else{
                        $arr =  array(0,'未知错误');
                    }
                    break;
            }
    return $arr;
}