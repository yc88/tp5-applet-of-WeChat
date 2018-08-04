<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 14:50
 */
namespace app\admin\model;
 use think\Model;

 class UserAdmin extends  Model
 {

     protected  $name = 'user_admin';


     /**
      * 根据用户名检测用户名是否存在
      */
    public function checkedName($userName){
        return $this->alias('a')
                    ->join('role r','a.role_id = r.id')
                    ->where('a.user_name',$userName)
                    ->field('a.*,r.id as rid ,r.role_name,r.rule')
                    ->find();
    }

     /**根据id修改管理员的相关状态
      * @param null $data
      * @param $id
      * @return false|int
      */
     public function updateAdminStatus($data = null,$id){
            $result = $this->where(array('id'=>$id))->update($data);
            return $result;
     }

     /**根据条件查询管理员列表
      * @param $where
      * @param $firstPage
      * @param $number
      * @return false|\PDOStatement|string|\think\Collection
      */
     public function getWhereList($where,$firstPage,$number){
         $list = $this
             ->alias('u')
             ->join('role r','u.role_id=r.id')
             ->where($where)
             ->limit($firstPage,$number)
             ->field('u.*,r.role_name')
             ->order('id ASC')
             ->select();
         return $list;
     }

     /**
      * 根据搜索条件获取所有的用户数量
      * @param $where
      */
     public function getAllUsers($where)
     {
         return $this->where($where)->count();
     }

     /**
      * 插入管理员信息
      * @param $param
      */
     public function insertUser($param)
     {
         try{
             $result =  $this->validate('UserValidate')->save($param);
             if(false === $result){
                 // 验证失败 输出错误信息
                 return array(0,$this->getError());
             }else{
                 return  array(1,url('user/index'));
//                     msg(1, url('user/index'), '添加用户成功');
             }
         }catch(PDOException $e){
             return array(0, $e->getMessage());
         }
     }

     /**
      * 编辑管理员信息
      * @param $param
      */
     public function editUser($param)
     {
         try{

             $result =  $this->validate('UserValidate')->save($param, ['id' => $param['id']]);
             if(false == $result){
                 // 验证失败 输出错误信息
                 return array(0,$this->getError());
             }else{
                 return  array(1,url('user/index'));
             }
         }catch(PDOException $e){
             return array(0,$e->getMessage());
         }
     }

     /**
      * 根据管理员id获取角色信息
      * @param $id
      */
     public function getOneUser($id)
     {
         return $this->where('id', $id)->find();
     }

     /**
      * 删除管理员
      * @param $id
      */
     public function delUser($id)
     {
         try{
             $this->where('id', $id)->delete();
             return array(1,'删除成功');
         }catch( PDOException $e){
             return array(0, $e->getMessage());
         }
     }

     /**
      * 根据用户名获取管理员信息
      * @param $name
      */
     public function findUserByName($name)
     {
         return $this->where('user_name', $name)->find();
     }

     /**
      * 更新管理员状态
      * @param array $param
      */
     public function updateStatus($param = [], $uid)
     {
         try{
             $this->where('id', $uid)->update($param);
             return array(1,url('/admin/index'));
         }catch (\Exception $e){

             return array(0, $e->getMessage());
         }
     }
 }
