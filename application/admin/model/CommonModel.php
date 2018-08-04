<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-16
 * Time: 9:03
 */
namespace app\admin\model;
use think\Model;

class CommonModel extends  Model
{
    /**
     * 获取一条数据
     */
    public function getOne($where){
        if(!is_array($where)){
            $where = array('id'=>$where);
        }
        return $this->where($where)->find();
    }

    /**获取列表的数据
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getIndexList($where = null,$offset,$limit,$map=[]){
        return $this->where($where)->whereOr($map)->limit($offset, $limit)->order('id desc')->select();
    }

    /**获取需要的相关数据
     * @param null $where
     * @param null $field
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAll($where = null ,$field = null,$order='id DESC'){
        return $this->where($where)->field($field)->order($order)->select();
    }
    /**获取数据的总条数
     * @param $where
     * @return int|string
     */
    public function getListCount($where){
        return $this->where($where)->count();
    }

    /**插入数据
     * @param $data 插入数据
     * @param $validate 验证的消息
     * @param $url 成功跳转URL
     * @return array
     */
    public function insertDataOne($data,$url){
        $result =  $this->allowField(true)->save($data);
        if(!$result){
            return array(0,$this->getError());
        }
        if(false === $result){
            // 验证失败 输出错误信息
            return array(0,$this->getError());
        }else{
            return array($result,$url);
        }
    }

    /**修改数据
     * @param $data
     * @param $url
     * @return array
     */
    public function ediDataOne($data,$url){
        try{ //innodb
            $result = $this->save($data, ['id' => $data['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return array(0,$this->getError());
            }else{
                return array(1,$url);
            }
        }catch(PDOException $e){
            return array(0,$this->getError());
        }
    }

    /**删除数据
     * @param $id
     * @return array|bool
     */
    public function  delDataOne($id){
        if(!is_array($id)){
            $id = array('id'=>$id);
        }
        try{
            $this->where($id)->delete();
            return  array(1,'操作成功');
        }catch(PDOException $e){
            return array(0,$e->getMessage());
        }
    }
    /**检测重名
     * @param $where
     * @return array|false|\PDOStatement|string|Model
     */
    public function checkedNameUnique($where){
        return $this->where($where)->find();
    }
    /**生成订单号
     * @param string $topSig
     * @return string
     * 平台id . 2012-10-25 9:59:59到现在的秒数 . 订单生成的年份.五位字符串 WX174379512A551005
     */
    public  function createOrderNumber($topSig = 'WX')
    {
        $orderPrefix = sprintf("%09d", time() - 1351130399);
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $twoFix = $yCode[intval(date('Y')) - 2018] . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
        return $topSig . $orderPrefix . $twoFix;
    }
}