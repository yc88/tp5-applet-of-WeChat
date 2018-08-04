<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-24
 * Time: 14:02
 */
namespace app\wx\model;
use think\Model;
class CommonModel extends  Model
{

    /**查询一条相关数据
     * @param $where
     * @param null $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneData($where,$field = null){
        if(!is_array($where)){
            $where = array('id'=>$where);
        }
        $result = $this->where($where)->field($field)->find();
        if(!$result){
            return false;
        }
        return $result->getData();
    }

    /**获取数据 带分页
     * @param array $where
     * @param int $limit
     * @param null $field
     * @param string $order
     * @return array
     */
    public function getAllList($where = array(),$limit = 10,$field = null,$order = 'id DESC'){
        $count = $this->where($where)->count();
        $list = $this->where($where)->field($field)->order($order)->paginate($limit,$count); //数据
        $page = $list->render(); //分页显示
        return array(
            'list'=>$list,
            'page'=>$page
        );
    }

    /**插入数据操作 事物操作
     * @param $data
     * @return array
     */
    public function addData($data)
    {
        $this->startTrans();
        try {
            $dataid = $this->insertGetId($data);
            // 提交事务
            $this->commit();
            return array(1, $dataid);
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return array(0, $e->getMessage());
        }
    }

    /**表字段自身加一
     * @param array $where
     * @param int $num
     * @param null $field
     * @return int|true
     * @throws \think\Exception
     */
    public function incSet($where = null,$field = null,$num = 1){
       return $this->where($where)->setInc($field,$num);
    }

    /**获取数据列表
     * @param array $where
     * @param string $field
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($where = array(),$field = '',$order = 'id ASC'){
        return $this->where($where)->field($field)->order($order)->select();
    }

    /**生成订单号
     * @param string $topSig
     * @return string
     * 平台id . 2012-10-25 9:59:59到现在的秒数 . 订单生成的年份.五位字符串 WX174379512A551005
     */
    public  function createOrderNumber($topSig = 'WX'){
        $orderPrefix = sprintf("%09d", time() - 1351130399);
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $twoFix = $yCode[intval(date('Y')) - 2018].substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))),0,6);
     return $topSig. $orderPrefix . $twoFix;
    }
}