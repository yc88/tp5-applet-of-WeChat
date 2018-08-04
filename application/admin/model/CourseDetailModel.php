<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/17
 * Time: 16:22
 */
namespace app\admin\model;
class CourseDetailModel extends  CommonModel
{
        protected $name = 'course_detail';

    /**插入数据
     * @param $data
     * @return array
     */
    public function insertCourse($data){
        $result =  $this->allowField(true)->insertGetId($data);
        if(!$result){
            return false;
        }
        if(false === $result){
            // 验证失败 输出错误信息
            return false;
        }else{
            return $result;
        }
    }
}