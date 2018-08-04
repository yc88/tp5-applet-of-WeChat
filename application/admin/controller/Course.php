<?php
/** 课程管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-14
 * Time: 17:19
 */
namespace app\admin\controller;


use app\admin\model\CourseCateModel;
use app\admin\model\CourseDetailModel;
use app\admin\model\CourseModel;
use app\admin\model\CourseTimeModel;
use app\admin\model\OrderModel;
use app\admin\model\TeacherModel;
use think\Db;

class Course extends  Base
{
    //课程列表
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $map = [];
            if (!empty($param['searchText'])) {
                $where['courser_name'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $courseModel = new CourseModel();
            $teacherModel = new TeacherModel();
            $courseCateModel = new CourseCateModel();
            $selectResult = $courseModel->getIndexList($where, $offset, $limit,$map);
            foreach($selectResult as $key=>$vo){
                $teacher = $teacherModel->getOne($vo['teacher_id']);
                $vo['begins_time'] = date('Y-m-d H:i:s',$vo['begins_time']);
                $vo['is_publish'] = $vo['is_publish'] == 1 ? '已发布' : '未发布';
                $vo['teacher_id'] = $teacher['name_z'].'('.$teacher['name_z'].')';
                $vo['classify_id'] = $courseCateModel->getOne($vo['classify_id'])['classify_name'];
                $selectResult[$key]['operate'] = showOperate($this->makeButtonCourseAll($vo['id'],'course/coursedetail','course/editcourse','course/delcourse'));
            }
            $return['total'] = $courseModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        return $this->fetch();
    }
    //课程详情
    public function coursedetail(){
        $courseCateModel = new CourseCateModel();
        $teacherModel = new TeacherModel();
        $courseModel = new CourseModel();
        $courseDetailModel = new CourseDetailModel();
        $id = input('param.');
        if(!$id){
            $this->error('系统错误');
        }
        $data = objToArray($courseModel->getOne($id));
        $teacher = objToArray($teacherModel->getAll(array('id'=>$data['teacher_id']),'id,name_z,name_y'));
        $classify = objToArray($courseCateModel->getAll(array('id'=>$data['classify_id']),'id,classify_name'));
        $data['teacher_name'] = $teacher[0]['name_z'].'('.$teacher[0]['name_y'].')';
        $data['classify_name'] = $classify[0]['classify_name'];
        $cour_detail_img = $courseDetailModel->getAll(array('detail_cate_id'=>1,'course_id'=>$data['id']),'id,detail_url,sort','sort ASC'); //课程详情
        $FQA_img = $courseDetailModel->getAll(array('detail_cate_id'=>2,'course_id'=>$data['id']),'id,detail_url,sort','sort ASC'); //常见问题
        $Sta_img = $courseDetailModel->getAll(array('detail_cate_id'=>3,'course_id'=>$data['id']),'id,detail_url,sort','sort ASC'); //入学须知
        if(!empty($course['video_url'])){
            $data['video_type'] = explode(".", $course['video_url'])[1];
        }
            $this->assign([
                'data'=>$data,
                'cour_detail_img'=>$cour_detail_img,
                'FQA_img'=>$FQA_img,
                'Sta_img'=>$Sta_img
            ]);
        return $this->fetch();
    }
    //添加课程
    public function addCourse(){
        $courseCateModel = new CourseCateModel();
        $teacherModel = new TeacherModel();
        $courseModel = new CourseModel();
        if(request()->isAjax()){
            $data = input('param.');
            $arr['begins_time'] = strtotime($data['startime']); //2018-04-18
            $arr['end_time'] = strtotime($data['endtime']);
            $video = $data['video'];
            if(!$data['classify_id']){
                putMsg(0,'请选择分类');
            }
            if(!$data['courser_name']){
                putMsg(0,'请填写课程名称');
            }
            if($courseModel->checkedNameUnique(array('courser_name'=>$data['courser_name']))){
                putMsg(0,'该名称已经存在');
            }
            if(!$data['courser_num']){
                putMsg(0,'请填写上课人数');
            }
            if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['courser_price'])){
                putMsg(0,'请正确输入价格,小数点保留两位');
            }
            if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['depoit_price'])){
                putMsg(0,'请正确输入价格,小数点保留两位');
            }
            $arr['classify_id'] = $data['classify_id'];
            $arr['teacher_id'] = $data['teacher_id'];
            $arr['courser_name'] = $data['courser_name'];
            $arr['courser_num'] = $data['courser_num'];
            $arr['courser_price'] = $data['courser_price']; //全额
            $arr['depoit_price'] = $data['depoit_price'];  //定金
            $arr['courser_detail'] = $data['courser_detail'];
            $arr['is_publish'] = $data['is_publish'];
            //视频上传
            if($video){
                $arr['video_url'] = $video;
            }
//            课程首页介绍
            if(isset($data['courser_img'])){
                $arr['courser_img'] = extendImage($data['courser_img'],'courseImg',
                    array(
                        array(
                            'width' => 350,
                            'height' => 180
                        ),
                        array(
                            'width' => 600,
                            'height' => 600
                        ),
                        array(
                            'width' => 1200,
                            'height' => 180
                        ),
                ));
            }
        //常见问题
            $FQA_arr = array();
            if(isset($data['FQA'])){
                $FQA = explode(',',$data['FQA']);
                foreach ($FQA as $fk => $fv) {
                    if (!empty($fv)) {
                        $FQA_arr[$fk] = extendImage($fv, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
            //入学须知
            $start_begin_arr = array();
            if(isset($data['start_begin'])){
                $startArr = explode(',',$data['start_begin']);
                foreach ($startArr as $sk => $sv) {
                    if (!empty($sv)) {
                        $start_begin_arr[$sk] = extendImage($sv, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
            //课程详情图片
            $imgUrl = array();
            if(isset($data['courser_img_arr'])) {
                $imgUrlArr = explode(',',$data['courser_img_arr']);
                foreach ($imgUrlArr as $k => $v) {
                    if (!empty($v)) {
                        $imgUrl[$k] = extendImage($v, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
            $arr['addtime'] = time();
            $arr['add_author'] = session('id');
            $result = $courseModel->insertCourse($arr,url('course/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            $course_detail_model = new CourseDetailModel(); // 详情图片表
            $data_arr = array( //detail_url detail_cate_id
                'course_id'=>$result[0],
            );
            if(isset($FQA_arr)){ //常见问题
                foreach($FQA_arr as $FQk => $FQv){
                    $data_arr['detail_url'] = $FQv;
                    $data_arr['detail_cate_id'] = 2;
                    $data_arr['addtime']=time();
                    $result_id_1 = $course_detail_model->insertCourse($data_arr);
                    if($result_id_1){
                        $course_detail_model->update(array('sort'=>$result_id_1,'id'=>$result_id_1));
                    }
                }
            }
            if(isset($start_begin_arr)){ //入学须知
                foreach($start_begin_arr as $stk => $stv){
                    $data_arr['detail_url'] = $stv;
                    $data_arr['detail_cate_id'] = 3;
                    $data_arr['addtime']=time();
                    $result_id_2 = $course_detail_model->insertCourse($data_arr);
                    if($result_id_2){
                        $course_detail_model->update(array('sort'=>$result_id_2,'id'=>$result_id_2));
                    }
                }
            }
            if(isset($imgUrl)){ //课程详情
                foreach($start_begin_arr as $stk => $stv){
                    $data_arr['detail_url'] = $stv;
                    $data_arr['detail_cate_id'] = 1;
                    $data_arr['addtime']=time();
                    $result_id_3 =$course_detail_model->insertCourse($data_arr);
                    if($result_id_3){
                        $course_detail_model->update(array('sort'=>$result_id_3,'id'=>$result_id_3));
                    }
                }
            }
            putMsg(1,$result[1]);
        }
        $courseCate = $courseCateModel->getAll('','id,classify_name');
        $teacher = $teacherModel->getAll('','id,name_z,name_y');
        $this->assign([
            'courseCate'=>objToArray($courseCate),
            'teacher'=>objToArray($teacher)
        ]);
        return $this->fetch();
    }
    //编辑课程
    public function editCourse(){
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误');
        }
        $courseCateModel = new CourseCateModel();
        $teacherModel = new TeacherModel();
        $courseModel = new CourseModel();
        $courseDetailModel = new CourseDetailModel();
        if(request()->isAjax()){
            $data = input('param.');
            $arr['begins_time'] = strtotime($data['startime']); //2018-04-18
            $arr['end_time'] = strtotime($data['endtime']);
            $video = $data['video'];
            if(!$data['courser_name']){
                putMsg(0,'请填写课程名称');
            }
            $checkedName = $courseModel->checkedNameUnique(array('courser_name'=>$data['courser_name']));
            if($checkedName && $data['id'] != $checkedName['id'] ){
                putMsg(0,'该名称已经存在');
            }
            if(!$data['courser_num']){
                putMsg(0,'请填写上课人数');
            }
            if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['courser_price'])){
                putMsg(0,'请正确输入价格,小数点保留两位');
            }
            if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['depoit_price'])){
                putMsg(0,'请正确输入价格,小数点保留两位');
            }

            $arr['id'] = $data['id'];
            $arr['classify_id'] = $data['classify_id'];
            $arr['teacher_id'] = $data['teacher_id'];
            $arr['courser_name'] = $data['courser_name'];
            $arr['courser_num'] = $data['courser_num'];
            $arr['courser_price'] = $data['courser_price']; //全额
            $arr['depoit_price'] = $data['depoit_price'];  //定金
            $arr['courser_detail'] = $data['courser_detail'];
            $arr['is_publish'] = $data['is_publish'];
            //视频上传
            if($video){
                $old_url = $courseModel->getOne($arr['id'])['video_url'];
                if($old_url && $video !== $old_url){
                    $rootUrl = '.'.config('img_url').'video/';
                    $url = $rootUrl.$old_url;
                    unlink($url);
                }
                $arr['video_url'] = $video;
            }
//            首页图片
            $newImg = '';
            $old_course_img = $courseModel->getOne($arr['id'])['courser_img'];
            if(isset($data['courser_img']) && $data['courser_img'] != $old_course_img){
                $newImg = extendImage($data['courser_img'],'courseImg',
                    array(

                    array(
                        'width' => 350,
                        'height' => 180
                    ),
                    array(
                        'width' => 600,
                        'height' => 600
                    ),
                    array(
                        'width' => 1200,
                        'height' => 180
                    ),

                ));
                $arr['courser_img'] = $newImg;
            }
//            课程详情图片
            $imgUrl = array();
            if(isset($data['courser_img_arr'])) {
                $imgUrlArr = explode(',',$data['courser_img_arr']);
                foreach ($imgUrlArr as $k => $v) {
                    if (!empty($v)) {
                        $imgUrl[$k] = extendImage($v, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
//            常见问题
            $FQA_arr = array();
            if(isset($data['FQA'])){
                $FQA = explode(',',$data['FQA']);
                foreach ($FQA as $fk => $fv) {
                    if (!empty($fv)) {
                        $FQA_arr[$fk] = extendImage($fv, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
//           入学须知
            $start_begin_arr = array();
            if(isset($data['start_begin'])){
                $startArr = explode(',',$data['start_begin']);
                foreach ($startArr as $sk => $sv) {
                    if (!empty($sv)) {
                        $start_begin_arr[$sk] = extendImage($sv, 'courseImg', array(
                            array(
                                'width' => 325,
                                'height' => 250
                            ),
                            array(
                                'width' => 650,
                                'height' => 500
                            ),
                            array(
                                'width' => 1300,
                                'height' => 1000
                            ),
                        ));
                    }
                }
            }
            $arr['addtime'] = time();
            $arr['add_author'] = session('id');
            $result = $courseModel->ediDataOne($arr,url('course/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            $course_detail_model = new CourseDetailModel();
            $data_arr = array( //detail_url detail_cate_id
                'course_id'=>$arr['id']
            );
            if(isset($FQA_arr)){ //常见问题
                foreach($FQA_arr as $FQk => $FQv){
                    $data_arr['detail_url'] = $FQv;
                    $data_arr['detail_cate_id'] = 2;
                    $data_arr['addtime']=time();
                    $result_id_1 = $course_detail_model->insertCourse($data_arr);
                    if($result_id_1){
                        $course_detail_model->update(array('sort'=>$result_id_1,'id'=>$result_id_1));
                    }
                }
            }
            if(isset($start_begin_arr)){ //入学须知
                foreach($start_begin_arr as $stk => $stv){
                    $data_arr['detail_url'] = $stv;
                    $data_arr['detail_cate_id'] = 3;
                    $data_arr['addtime']=time();
                    $result_id_2 = $course_detail_model->insertCourse($data_arr);
                    if($result_id_2){
                        $course_detail_model->update(array('sort'=>$result_id_2,'id'=>$result_id_2));
                    }
                }
            }

            if(isset($imgUrl)){ //课程详情
                foreach($imgUrl as $dk => $dv){
                    $data_arr['detail_url'] = $dv;
                    $data_arr['detail_cate_id'] = 1;
                    $data_arr['addtime']=time();
                    $result_id_3 =$course_detail_model->insertCourse($data_arr);
                    if($result_id_3){
                        $course_detail_model->update(array('sort'=>$result_id_3,'id'=>$result_id_3));
                    }
                }
            }
            if($newImg){ //首页图片删除
                removeUploadImage(array(
                    array($old_course_img,350,180),
                    array($old_course_img,600,600),
                    array($old_course_img,1200,180)
                ));
            }

            putMsg(1,$result[1]);
        }
        $courseCate = $courseCateModel->getAll('','id,classify_name');
        $teacher = $teacherModel->getAll('','id,name_z,name_y');
        $course = objToArray($courseModel->getOne($id));
        $cour_detail_img = $courseDetailModel->getAll(array('detail_cate_id'=>1,'course_id'=>$course['id']),'id,detail_url,sort','sort ASC'); //课程详情
        $FQA_img = $courseDetailModel->getAll(array('detail_cate_id'=>2,'course_id'=>$course['id']),'id,detail_url,sort','sort ASC'); //常见问题
        $Sta_img = $courseDetailModel->getAll(array('detail_cate_id'=>3,'course_id'=>$course['id']),'id,detail_url,sort','sort ASC'); //入学须知
        if(!empty($course['video_url'])){
            $course['video_type'] = explode(".", $course['video_url'])[1];
        }
        $this->assign([
            'courseCate'=>objToArray($courseCate),
            'teacher'=>objToArray($teacher),
            'course'=>$course,
            'cour_detail_img'=>$cour_detail_img,
            'FQA_img'=>$FQA_img,
            'Sta_img'=>$Sta_img
        ]);
        return $this->fetch();
    }
    //删除课程
    public function delCourse(){
        $id = input('param.');
        $courseModel = new CourseModel();
        $data = $courseModel->getOne($id);
        if(!$data){
            putMsg(0,'系统错误');
        }
        $orderModel = new OrderModel();
        $count = $orderModel->getListCount(array('course_id'=>$id['id']));
        if($count >= 1){
            putMsg(0,'用学员购买了此课程，你最后不删除该课程');
        }
        $courser_img = $data['courser_img'];
        $result = $courseModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        $courser_img = explode(',',$courser_img);
        foreach($courser_img as $k => $v){
            removeUploadImage(array(
                array($v,350,180),
                array($v,600,600),
                array($v,1200,180),
            ));
        }
        putMsg(1,$result[1]);
    }

    /**
     * 课程列表拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButtonCourseAll($id,$detailUrl,$editUrl,$delUrl)
    {
        return [
            '详情' => [
                'auth' => $detailUrl,
                'href' => url($detailUrl, ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '编辑' => [
                'auth' => $editUrl,
                'href' => url($editUrl, ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => $delUrl,
                'href' => "javascript:roleDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    //课程分类
    public function  courseClassify(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['classify_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $cate = new CourseCateModel();
            $selectResult = $cate->getIndexList($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $vo['addtime'] = date('Y-m-d H:i:s',$vo['addtime']);
                $selectResult[$key]['operate'] = showOperate(makeButtonAll($vo['id'],'course/editclassify','course/delclassify'));
            }
            $return['total'] = $cate->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        return $this->fetch();
    }

    //添加分类
    public function addClassify(){
        if(request()->isAjax()){
            $data = input('param.');
            $courseCateModel = new CourseCateModel();
            $data['addtime'] = time();
            if(!$data['classify_name']){
                putMsg(0,'请填写分类名称');
            }
            if($courseCateModel->checkedNameUnique(array('classify_name'=>$data['classify_name']))){
                putMsg(0,'该名称已经存在');
            }
            $result = $courseCateModel->insertDataOne($data,url('course/courseClassify'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        return $this->fetch();
    }

    //编辑分类
    public function editClassify(){
        $id = input('param.id');
        $courseCateModel = new CourseCateModel();
        if(request()->isAjax()){
            $data = input('param.');
            $courseCateModel = new CourseCateModel();
            $data['addtime'] = time();
            if(!$data['classify_name']){
                putMsg(0,'请填写分类名称');
            }
          /*  if(!$data['course_price']){
                putMsg(0,'请输入价格');
            }
            if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['course_price'])){
                putMsg(0,'请正确输入价格,小数点保留两位');
            }*/
            $name_is = $courseCateModel->checkedNameUnique(array('classify_name'=>$data['classify_name']));
            if($name_is && $name_is['id'] != $data['id']){
                putMsg(0,'该名称已经存在');
            }
            $result = $courseCateModel->ediDataOne($data,url('course/courseClassify'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        $this->assign([
            'courseCate' => $courseCateModel->getOne($id)
        ]);
        return $this->fetch();
    }

    //删除分类

    public function delClassify(){
        $id = input('param.id');
        $courseCateModel = new CourseCateModel();
        $courseModel = new CourseModel();
        $number = $courseModel->checkedCateSon($id);
        if($number >= 1 ){
            putMsg(0,'该分类下面还有课程，请重新操作'.$number);
        }
        $result = $courseCateModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,$result[1]);
        return $this->fetch();
    }

    //课程时间
    public function coursetime(){
        $courseModel = new CourseModel();
        $data = $courseModel->getAll(array('is_publish'=>1),'id,courser_name');
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['course_id'])) {
                $where['course_id'] = ['eq', intval($param['course_id'])];
            }
//            if (!empty($param['searchText'])) {
//                $where['courser_name'] = ['like', '%' . $param['searchText'] . '%'];
//            }
            $courseTimeModel = new CourseTimeModel();
            $selectResult = $courseTimeModel->getIndexList($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['course_id'] = $courseModel->getOne(array('id'=>$vo['course_id']))['courser_name'];
                $selectResult[$key]['is_game_over'] = $vo['is_game_over'] == 1 ? '已经结束' : '还未结束';
                $selectResult[$key]['operate'] = showOperate($this->makeButtonCourseTime($vo['id'],'course/editcoursetime'));
            }
            $return['total'] = $courseTimeModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        $this->assign([
            'course'=>$data
        ]);
        return $this->fetch();
    }
    //添加课程时间
    public function addcoursetime(){
        $courseModel = new CourseModel();
        $data = $courseModel->getAll(array('is_publish'=>1),'id,courser_name');
        if(request()->isAjax()){
            $data = input('param.');
//            $arr = array();
            $courseTimeModel = new CourseTimeModel();
            $data['addtime'] = time();
            $result = $courseTimeModel->insertDataOne($data,url('course/coursetime'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        $this->assign([
            'course'=>$data
        ]);
        return $this->fetch();
    }
    //修改课程时间
    public function editcoursetime(){
        $id = input('param.id');
        $courseModel = new CourseModel();
        $data = $courseModel->getAll(array('is_publish'=>1),'id,courser_name');
        $courseTimeModel = new CourseTimeModel();
        $one = $courseTimeModel->getOne($id);
        if(request()->isPost()){
            $data = input('param.');
            $courseTimeModel = new CourseTimeModel();
            $data['addtime'] = time();
            $result = $courseTimeModel->ediDataOne($data,url('course/coursetime'));
            if($result[0] == 0){
                $this->error($result[1]);
            }
            $this->success('操作成功',$result[1]);
        }
        $this->assign([
            'course'=>$data,
            'one'=>$one
        ]);
        return $this->fetch();
    }

    //课程时间
    private function makeButtonCourseTime($id,$editUrl)
    {
        return [
            '编辑' => [
                'auth' => $editUrl,
                'href' => url($editUrl, ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ]
//,
//            '删除' => [
//                'auth' => $delUrl,
//                'href' => "javascript:roleDel(" .$id .")",
//                'btnStyle' => 'danger',
//                'icon' => 'fa fa-trash-o'
//            ]
        ];
    }

}