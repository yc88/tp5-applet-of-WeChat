<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-16
 * Time: 11:21
 */
namespace app\admin\model;

class CourseModel extends  CommonModel
{
  protected  $name = 'course';

    /**���ݷ���id�鿴�����滹��û�пγ̵Ĵ���
     * @param $cate_id
     * @return int|string
     */
    public function checkedCateSon($cate_id){
        return $this->where('id',$cate_id)->count();
    }

    /**����γ�
     * @param $data
     * @param $url
     * @return array
     */
    public function insertCourse($data,$url){
        $result =  $this->allowField(true)->insertGetId($data);
        if(!$result){
            return array(0,$this->getError());
        }
        if(false === $result){
            // ��֤ʧ�� ���������Ϣ
            return array(0,$this->getError());
        }else{
            return array($result,$url);
        }
    }

}