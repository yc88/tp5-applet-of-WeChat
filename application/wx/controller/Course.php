<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-25
 * Time: 16:36
 */
namespace app\wx\controller;
use app\wx\model\CourseDetailModel;
use app\wx\model\CourseModel;
use think\Db;

class Course extends  Common
{
    /**
     * 课程详情数据获取
     */
    public function course_detail(){
        $cid = isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : null;
        if(!$cid){
            putMsg(0,'系统错误');
        }
        $courseModel = new CourseModel();
        $one = $courseModel->getOneData(array('id'=>$cid),'id,courser_name,courser_img,video_url');
        if($one['video_url']){
            $one['video_url'] = config('img_url').'/video/'.$one['video_url'];
        }
        if(!$one){
            putMsg(0,'系统错误');
        }
        $one['courseDetailImg'] = $this->getDetailImg($one['id'],1);
        $one['FqaImg'] = $this->getDetailImg($one['id'],2);
        $one['staImg'] = $this->getDetailImg($one['id'],3);
//       $arr =  array_merge($img,$one);
        putMsg(1,$one);
    }
    /**
     * 选择课程  课程选择页面的课程下拉框
     */
    public function course_list(){
        $cid = $_REQUEST['cid'];
        $courseModel = new CourseModel();
        $checkCourse = $courseModel->getOneData(array('id'=>$cid),'id,courser_name,courser_price,depoit_price');
        $where = array('is_publish'=>1);
        $where['id'] = array('neq',$cid);
        $courseList = $courseModel->getList($where,'id,courser_name,courser_price,depoit_price','id ASC');
         array_unshift($courseList,$checkCourse);
        putMsg(1,$courseList);
    }

    /**课程详情页面图片的获取
     * @param $cid
     * @param int $cate_id
     * @param int $type
     * @return bool
     */
    public function getDetailImg($cid,$cate_id = 1,$type_n = 1){
        $cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $cid ;
        $cate_id = isset($_REQUEST['cate_id']) ? $_REQUEST['cate_id']:$cate_id;

        if(!$cid || !$cate_id){
            if($type_n == 1){
                return false;
            }else{
                putMsg(0,'系统错误');
            }
        }
    $courseDetailModel = new CourseDetailModel();
   $detailImg = $courseDetailModel->getAllList(array('detail_cate_id'=>$cate_id,'course_id'=>$cid),15,'id,detail_url,sort','sort ASC,addtime ASC'); //课程详情图片
        foreach ($detailImg['list'] as $k1 => $v1) {
            $detailImg['list'][$k1]['detail_url'] = getImgName($v1['detail_url'],1300,1000);
        }
        if($type_n == 1){
            return $detailImg['list'];
        }else{
            putMsg(1,$detailImg['list']);
        }
    }
    /**
     * 获取当前的课程
     */
    public function course_list_one(){
        $cid = $_REQUEST['cid'];
        $courseModel = new CourseModel();
        $checkCourse = $courseModel->getOneData(array('id'=>$cid),'id,courser_name,courser_price,depoit_price');
        putMsg(1,$checkCourse);
    }
}