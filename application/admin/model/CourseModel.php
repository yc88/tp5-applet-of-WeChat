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

    /**根据分类id查看其下面还有没有课程的存在
     * @param $cate_id
     * @return int|string
     */
    public function checkedCateSon($cate_id){
        return $this->where('id',$cate_id)->count();
    }

    /**插入课程
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
            // 验证失败 输出错误信息
            return array(0,$this->getError());
        }else{
            return array($result,$url);
        }
    }

}