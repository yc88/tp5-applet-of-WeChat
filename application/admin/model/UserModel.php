<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-17
 * Time: 11:50
 */
namespace app\admin\model;

class UserModel extends CommonModel
{
    protected  $name = 'user';

    /**获取用户名称 或者 其他
     * @param $where
     * @param null $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getUserName($where,$field = null){
        if(!is_array($where)){
            $where = array('id'=>$where);
        }
        return $this->where($where)->field($field)->find();
    }
}