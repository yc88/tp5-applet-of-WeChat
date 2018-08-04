<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/14
 * Time: 18:40
 */
namespace app\admin\controller;
use app\admin\model\InformSmsModel;
use think\Db;

class Sms extends Base
{
    /**
     * 通知短信记录
     */
    public function smsindex(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $map = [];
            if (!empty($param['searchText'])) {
                $where['courser_name'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $smsModel = new InformSmsModel();
            $selectResult = $smsModel->getIndexList($where, $offset, $limit,$map);
            foreach($selectResult as $k =>$v){
                $selectResult[$k]['sms_author'] = Db::table('fl_user_admin')->where('id',$v['sms_author'])->field('id,user_name')->find()['user_name'];
                $selectResult[$k]['operate'] = showOperate($this->makeButtonInformSms($v['id'],'sms/delsms'));
            }
            $return['total'] = $smsModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
       return $this->fetch('smsindex');
    }
    /**
     * 发送短信通知
     */
    public function sendsms(){
        if(request()->isPost()){
            $data = input("param.");
            $course_name = $data['course_name'];
            $begin_time = $data['begin_time'];
            $end_time = $data['end_time'];
            $phone = $data['phone'];
            $phoneArr = explode(',',$phone);
            foreach($phoneArr as $k => $v){
                if(strlen($v) < 11 || strlen($v) >= 12){
                    putMsg(0,'此：'.$v.' 长度错误');
                }
                if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$|^19[\d]{9}$#', $v)){
                    putMsg(0,'此：'.$v.' 号码有误');
                }
            $result = sendSmsFirst($v,trim($course_name),'SMS_134321635','',$begin_time,$end_time,$begin_time);
                $sms_author = '';
                if($result[0] == 0){
                    $sms_author = $result[1];
                }
                if($result[0] == 1){
                    $arr = array(
                        'phone'=>$v,
                        'course_name'=>$course_name,
                        'begin_time'=>date('Y-m-d H:i:s',strtotime($begin_time)),
                        'end_time'=>date('Y-m-d H:i:s',strtotime($end_time)),
                        'smstime'=>date('Y-m-d H:i:s',time()),
                        'sms_author'=>session('id'),
                        'is_status'=>($sms_author ? $sms_author : 'ok')
                    );
                    Db::table('fl_inform_sms')->insert($arr);
                }
            }
            putMsg(1,'发送成功');
        }
        return  $this->fetch();
    }

    /**删除通知数据
     * @throws \think\Exception
     */
    public function delsms(){
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误');
        }
        $informSmsModel = new InformSmsModel();
        $result = $informSmsModel->where(array('id'=>$id))->delete();

        if(!$result){
            putMsg(0,'操作失败');
        }
        putMsg(1,'操作成功');
    }
    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButtonInformSms($id,$delUrl)
    {
        return [
            '删除' => [
                'auth' => $delUrl,
                'href' => "javascript:roleDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}