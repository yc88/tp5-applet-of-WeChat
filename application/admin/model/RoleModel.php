<?php
/** 角色表
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 17:20
 */
namespace app\admin\model;
use think\Model;
class RoleModel extends  Model
{
    protected  $name = 'role';

    /**获取角色信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public  function getInfoRole($id){
        $roleData = $this->where('id',$id)->find()->toArray();
        //超级用户是*
        if(empty($roleData['rule'])){
            $roleData['action'] = '';
            return $roleData;
        }else if('*' == $roleData['rule']){
            $where = '';
        }else{
            $where = 'id'.' in('.$roleData['rule'].')';
        }
        //获取节点model
        $nodeModel = new NodeModel();
        $nodeData = $nodeModel->getActions($where);
        $nodeData = objToArray($nodeData);
        foreach($nodeData as $k => $v){
                if('#' != $v['control_name']){
                    $roleData['action'][] =$v['control_name'] . '/' . $v['action_name'];
                }else if($v['control_name'] == '#'){
                    $roleData['action'][] = '';
                }
        }
        return $roleData;
    }
    /**
     * 根据搜索条件获取角色列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getRoleByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getAllRole($where)
    {
        return $this->where($where)->count();
    }

    /**获取role所有数据
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList(){
        return $this->select();
    }

    /**新增角色名称
     * @param $data
     * @return array
     */
    public function insertRole($data){
        $result =  $this->validate('RoleValidate')->save($data);
        if(!$result){
            return array(0,$this->getError());
        }
        if(false === $result){
            // 验证失败 输出错误信息
            return array(0,$this->getError());
        }else{
            return array(1,url('role/index'));
        }

    }

    /**修改用户角色
     * @param $data
     * @return array
     */
    public function editRole($data){
        try{ //innodb
            $result = $this->validate('RoleValidate')->save($data, ['id' => $data['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return array(0,$this->getError());
            }else{
                return array(1,url('role/index'));
            }
        }catch(PDOException $e){
            return array(0,$this->getError());
        }
    }

    /**
     * 根据角色id获取角色信息
     * @param $id
     */
    public function getOneRole($id)
    {
        return $this->where('id', $id)->find();
    }

    /**删除角色数据
     * @param $id
     * @return array|bool
     */
    public function delRole($id){
        if(!$id){
            return false;
        }
        try{
            $this->where('id', $id)->delete();
            return  array(1,'操作成功');
        }catch(PDOException $e){
            return array(0,$e->getMessage());
        }
    }

    // 获取角色的权限节点
    public function getRuleById($id)
    {
        $res = $this->field('rule')->where('id', $id)->find();

        return $res['rule'];
    }

    /**
     * 分配权限
     * @param $param
     */
    public function editAccess($param)
    {
        try{
            $this->save($param, ['id' => $param['id']]);
            return array(0,'操作成功');
        }catch(PDOException $e){
            return array(1,$e->getMessage());
        }
    }
}