<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/6/4
 * Time: 16:11
 */
namespace app\admin\model;
class PurchaseNumModel extends CommonModel
{
    protected  $name='purchase_num';


    /**检查是否已经存在了该元素的记录
     * @param $where
     * @param null $field
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public function checked_is_exist($where,$field = null){
       $result = $this->where($where)->field($field)->find();
        if(empty($result)){
            return false;
        }
        return $result;
    }
}