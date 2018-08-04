<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-24
 * Time: 15:16
 */
namespace app\wx\controller;
use app\wx\model\UserModel;
use think\Controller;
use think\Db;

class Login extends  Controller
{
    //授权登录
    public function authLogin(){
        $openid = $_POST['openid'];
        if(!$openid){
          putMsg(0,'授权失败'.__LINE__);
        }
        $where = array();
        $where['openid'] = trim($openid);
        $uid = Db::table('fl_user')->where($where)->field('id')->find();
        if($uid){
            $userInfo = Db::table('fl_user')->where(array('id'=>$uid['id']))->find();
            if($userInfo['user_status'] == 2){
                putMsg(0,'授权失败，该用户已经被冻结');
            }
            $userArr = array(
                'id'=>$userInfo['id'],
                'user_name'=>$userInfo['user_name'] ? json_decode($userInfo['user_name']) : null,
                'user_photo'=>$userInfo['user_photo'] ? $userInfo['user_photo'] : null
            );
            putMsg(1,$userArr);
        }else{
            $data = array();
//            $data['user_name'] = $_POST['nickName'];
//            $data['real_name'] = $_POST['nickName'];
//            $data['user_sex'] = $_POST['gender']; //1 男 2 女 3 未知
//            $data['user_photo'] = $_POST['avatarUrl'];
            $data[ 'openid' ]  = $openid;
            $data['user_source'] = 'wx';
            $data['addtime'] = time();
            if(!$data['openid']){
                putMsg(0,'授权失败,请重新授权'.__FILE__.__LINE__);
            }
            $result = Db::table('fl_user')->insertGetId($data);
            if($result){
                $userArr = array(
                    'id'=>$result,
                    'user_name'=>null,
                    'user_photo'=>null
                );
                putMsg(1,$userArr);
            }else{
                putMsg(0,'授权失败,请重新授权'.__FILE__.__LINE__);
            }
        }
    }
    /**
     * 根据code 获取sessionkey
     */
    public function getWxSessionKey(){
        $config = config('weixin');
        $appid = $config['appId'];
        $secret = $config['secret'];
        $code = trim($_REQUEST['code']);
        if(!$code){
            putMsg(0,'操作失败，非法操作');
        }
        if(!$appid || !$secret){
            putMsg(0,'系统错误，非法操作');
        }
        $get_token_url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
//        构造访问 以post方式提交xml到对应的接口url
        $ch =curl_init();
        curl_setopt ( $ch , CURLOPT_URL , $get_token_url );
        curl_setopt ( $ch , CURLOPT_HEADER , 0 );
        curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt ( $ch , CURLOPT_CONNECTTIMEOUT , 10 );
        curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER , false );//
        $res = curl_exec ( $ch );
        curl_close($ch);
        echo  exit($res);
    }

    /**
     * 添加用户名称等相关信息
     *
     */
    public function  editUser(){ //
            $data = array();
         $uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : null;
            $data['user_name'] =json_encode($_REQUEST['nickName']);
            $data['real_name'] = $_REQUEST['nickName'];
            $data['user_sex'] = $_REQUEST['gender']; //1 男 2 女 3 未知
            $data['user_photo'] = $_REQUEST['avatarUrl'];
        if(!$uid){
            putMsg(0,'系统错误');
        }

        $userModel = new UserModel();
        $userInfo =$userModel->where(array('id'=>$uid))->find();
        if($userInfo['user_status'] == 2){
            putMsg(0,'授权失败，该用户已经被冻结');
        }
        $result = $userModel->where(array('id'=>$uid))->update($data);
        if($result !== false){
            putMsg(1,'修改成功');
        }
        putMsg(0,'修改失败');

    }

    /**
     * 获取access_token 凭证
     */
    public function getAccessToken(){
        $config = config('weixin');
        $appid = $config['appId'];
        $secret = $config['secret'];
        $get_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
//        构造访问 以post方式提交xml到对应的接口url
        $ch =curl_init();
        curl_setopt ( $ch , CURLOPT_URL , $get_token_url ); // 	需要获取的URL地址，也可以在curl_init()函数中设置。
        curl_setopt ( $ch , CURLOPT_HEADER , 0 ); // 	启用时会将头文件的信息作为数据流输出。
        curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 ); //在启用CURLOPT_RETURNTRANSFER的时候，返回原生的（Raw）输出
        curl_setopt ( $ch , CURLOPT_CONNECTTIMEOUT , 10 ); //在尝试连接时等待的秒数。设置为0，则无限等待。
        curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER , false );// 禁止 cURL 验证对等证书（
        $res = curl_exec ( $ch );
        curl_close($ch);
        echo  exit($res);

    }
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
}