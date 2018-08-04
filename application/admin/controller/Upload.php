<?php
/** 文件上传 不验证权限
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-16
 * Time: 14:48
 */
namespace app\admin\controller;
use app\admin\model\CourseDetailModel;
use app\admin\model\CourseModel;
use app\admin\model\RecipeElementModel;
use app\admin\model\RecipeModel;
use app\admin\model\RecipeNameModel;
use think\Controller;
use think\Db;

class Upload extends  Controller

{
    /**
     * ajax上传图片
     * return array(view => '图片显示的url', 'value' => '数据库要存储的值')
     */
    public function upload_img()
    {
        uploadImg();
    }

    /**
     * 上传图片 课程相关图片的上传
     * return array(view => '图片显示的url', 'value' => '数据库要存储的值')
     */
    public function courser_img()
    {
        uploadImg('file');
    }
    /**
     * 视频上传
     */
    // todo 视频上传问题
    public function up_video(){
        $file = request()->file('file');
        $static = config('img_url');
        $vedio = '.'.$static.'video';
        if(!is_dir($vedio)){
            mkdir($vedio,0777,true);
        }
        if($file){
            $info = $file->move($vedio);
            if($info){
                $fileName =  $info->getSaveName();
                $fileName = str_replace("\\",'/',$fileName);
                putMsg(1,$fileName);
            }else{
                putMsg(0,'系统错误');
            }
        }
    }
    /**
     * 单文件视频的删除 课程
     */
    public function ajax_del_video(){
        $video = input('param.url_vi');
        $rootUrl = '.'.config('img_url').'video/';
        $url = $rootUrl.$video;
        if(!$video){
            putMsg(0,'系统错误');
        }
        unlink($url);
        putMsg(1,'删除成功');
    }
    /**
     * 图片删除 课程
     */
    public function ajax_del_img(){
        $data = input('param.');
        if(!$data['id']){
            putMsg(0,'系统错误');
        }
        $courseModel = new CourseModel();
        $result = $courseModel->getOne($data['id']);
        $old_img = explode(',',$result['courser_img']);
        $del_img =$data['courser_img'];
        $key = array_search($del_img,$old_img);
        if(is_int($key)){
            unset($old_img[$key]);
            $arr['courser_img'] = implode(',', $old_img);
            $arr['id'] = $result['id'];
            $result = $courseModel->ediDataOne($arr,url('course/index'));
            if($result[0] == 0){
                putMsg(0,'删除失败');
            }
            removeUploadImage(array(
                array($del_img,300,150),
                array($del_img,600,600)
            ));
            putMsg(1,'删除成功');
        }
    }

    /**
     * 图片删除 课程详情 常见问题 入学须知
     */
    public function ajax_del_more_img(){
        $data = input('param.');
        if(!$data['id']){
            putMsg(0,'系统错误');
        }
        $courseDetailModel = new CourseDetailModel();
        $result = $courseDetailModel->getOne($data['id']);
        $del_img =$result['detail_url'];
        $result = $courseDetailModel->delDataOne($data);
        if($result[0] == 0){
            putMsg(0,'删除失败');
        }
        removeUploadImage(array(
            array($del_img,325,250),
            array($del_img,650,500),
            array($del_img,1300,1000),
        ));
        putMsg(1,'删除成功');
    }
    //修改图片的顺序
    public function edit_sort(){
        $id = input('param.');
        if(!$id['id'] ||  !$id['sort']){
            putMsg(0,'系统错误');
        }
        $courseDetailModel = new CourseDetailModel();
        $result = $courseDetailModel->update(array('id'=>$id['id'],'sort'=>$id['sort']));
        if(!$result){
            putMsg(0,'系统错误');
        }
        putMsg(1,'排序成功');
    }

    /**课程详情页面的图片上传
     * @return mixed
     */
    public function arrImg(){
        if(request()->isAjax()){
            $arr_im = input("param.");
            if(empty($arr_im)){
                putMsg(0,'没有上传图片');
            }
            putMsg(1,$arr_im['img']);
        }
     return $this->fetch('course:arrImg');
    }

    /**
     * 根据课程获取该课程下面的食谱名称
     */
    public function getrecipeName(){
        $cid = input('param.');
        if(!$cid){
            putMsg(0,'系统错误');
        }
        $recipeNameModel = new RecipeNameModel();
        $recipeModel = new RecipeModel();
        $allName = $recipeNameModel->getAll(array('pid'=>0,'courser_id'=>$cid['cid']),'id,recipe_name,courser_id');
        foreach($allName as $k => $v){
   $allName[$k]['is_exist_recipe'] = $recipeModel->getListCount(array('recipe_name_id'=>$v['id'],'course_id'=>$v['courser_id'])) == 1 ? true : false;
            $sonArr = $recipeNameModel->getAll(array('pid'=>$v['id'],'courser_id'=>$cid['cid']),'id,recipe_name,courser_id');
            foreach($sonArr as $sk => $sv){
                $sonArr[$sk]['is_exist_recipe'] = $recipeModel->getListCount(array('recipe_name_id'=>$sv['id'],'course_id'=>$sv['courser_id'])) == 1 ? true : false;
            }
        $allName[$k]['son'] = $sonArr;
        }
        putMsg(1,$allName);
    }

    /**
     * 添加食谱时获取元素信息
     */
    public function getrecipeElement(){
        $eid = input('param.');
        if(!$eid){
            putMsg(0,'系统错误');
        }
        $recipeElementModel = new RecipeElementModel();
        $result = $recipeElementModel->getOne(array('id'=>$eid['eid']));
        if(empty($result)){
            putMsg(0,'系统错误');
        }
        $result['unit_name'] = Db::table('fl_inventory_unit')->where(array('id'=>$result['element_unit']))->field('id,unit_name')->find()['unit_name'];
        putMsg(1,$result);
    }

    /**
     * 是否为演示食谱
     */
    public function edit_recipe_is_demo(){
        $data= input('param.');
        if(!$data['id']){
            putMsg(0,'系统错误');
        }
        $is_demo = $data['val'];
        $recipeModel = new RecipeModel();
        $result = $recipeModel->getOne(array('id'=>$data['id']));
        if(empty($result)){
            putMsg(0,'系统错误');
        }
      $result = $recipeModel->ediDataOne(array('id'=>$data['id'],'is_demo'=>$is_demo),url(''));

        if($result[0] == 0){
            putMsg(1,$result[1]);
        }
        putMsg(1,'操作成功');
    }

}