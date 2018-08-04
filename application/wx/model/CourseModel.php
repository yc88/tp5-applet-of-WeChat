<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-24
 * Time: 14:04
 */
namespace app\wx\model;
use think\Db;

class CourseModel extends  CommonModel
{
    protected  $name = 'course';

    /**获取课程数据
     * @param null $where
     * @param null $field
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCourseList($where = null,$field = null,$order = "id DESC"){
        $courseModel = Db::table('fl_course');
        return $courseModel->where($where)->field($field)->order($order)->select();
//            return $this->where($where)->field($field)->order($order)->select();
    }
}