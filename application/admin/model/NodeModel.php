<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 17:31
 */
namespace app\admin\model;
use think\Model;
class NodeModel extends  Model
{
    protected  $name = 'node';

    /**根据条件查询节点数据
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getActions($where){
        return $this->where($where)->field('control_name,action_name')->select();
    }

    /**获取左侧菜单栏目
     * @param string $rule
     * @return array
     */
    public function getMenu($rule = ''){
        if(empty($rule)){
            return [];
        }
        //假设是超级管理员 那就是*
        $where = '*' == $rule ? 'is_menu = 2' : 'is_menu = 2 and id in(' . $rule . ')';
        $result = $this->field('id,node_name,type_id,control_name,action_name,style')
            ->where($where)->select();
        $menu = trimMenu($result);
        return $menu;
    }

    /**获取节点的相关数据
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getNodeList(){
        return $this
            ->field('id,node_name name,type_id pid,is_menu,style,control_name,action_name')
            ->select();
    }

    /**新增节点数据
     * @param $data
     * @return false|int
     */
    public function insertNode($data){
        return $this->save($data);
    }

    /**更新数据
     * @param $data
     * @return false|int
     */
    public function editNode($data){
       return $this->allowField(true)->save($data,['id' => $data['id']]);
    }

    /**删除节点数据
     * @param $id
     * @return bool|int
     */
    public function delNode($id){
        if(!$id){
            return false;
        }
        $count = $this->where('type_id',$id)->count();
        if($count >= 1){
            return false;
        }
        return $this->where('id',$id)->delete();
    }

    /**
     * 获取节点数据
     */
    public function getNodeInfo($id)
    {
        $result = $this->field('id,node_name,type_id')->select();
        $str = '';

        $role = new RoleModel();
        $rule = $role->getRuleById($id);

        if(!empty($rule)){
            $rule = explode(',', $rule);
        }

        foreach($result as $key=>$vo){
            $str .= '{ "id": "' . $vo['id'] . '", "pId":"' . $vo['type_id'] . '", "name":"' . $vo['node_name'].'"';

            if(!empty($rule) && in_array($vo['id'], $rule)){
                $str .= ' ,"checked":1';
            }

            $str .= '},';

        }

        return '[' . rtrim($str, ',') . ']';
    }
}
