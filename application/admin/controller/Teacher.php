<?php
/** 课程导师
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-16
 * Time: 12:17
 */
namespace app\admin\controller;

use app\admin\model\CourseModel;
use app\admin\model\TeacherModel;

class Teacher extends  Base
{
    //导师列表
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $map = [];
            if (!empty($param['searchText'])) {
                $where['name_z'] = ['like', '%' . $param['searchText'] . '%'];
                $map['name_y'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $teacher = new TeacherModel();
            $selectResult = $teacher->getIndexList($where, $offset, $limit,$map);
            foreach($selectResult as $key=>$vo){
                $vo['addtime'] = date('Y-m-d H:i:s',$vo['addtime']);
                $vo['sex'] = $vo['sex'] == 1 ? '男' : '女';
                $selectResult[$key]['operate'] = showOperate(makeButtonAll($vo['id'],'teacher/editteacher','teacher/delteacher'));
            }
            $return['total'] = $teacher->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
      return  $this->fetch();
    }
    //添加导师
    public function addTeacher(){
        if(request()->isAjax()){
            $data = input('param.');
            $teacherModel = new TeacherModel();
            if(!$data['name_z'] && !$data['name_y']){
                putMsg(0,'请填写导师的一个名字');
            }
            if($teacherModel->checkedNameUnique(array('name_z'=>$data['name_z']))){
                putMsg(0,'该中文名称已经存在');
            }
            if($teacherModel->checkedNameUnique(array('name_y'=>$data['name_y']))){
                putMsg(0,'该英文名称已经存在');
            }
            if(!is_numeric($data['age']) || is_numeric($data['age']) && $data['age'] > 100){
                putMsg(0,'请正确输入年龄');
            }
            //teacherHead/20180417/12729dfd2e2f85d596558a20cdd7f920.jpg
            $data['teacher_img'] = '';
            if(!empty($data['default_img'])){
                $data['teacher_img'] = extendImage($data['default_img'], 'teacherHead', array(array('width' => 120, 'height' => 100),array('width' => 100, 'height' => 60)));
            }
            $arr = array(
                'name_z'=>$data['name_z'],
                 'name_y'=>$data['name_y'],
                 'age'=>$data['age'],
                 'sex'=>$data['sex'],
                 'teacher_detail'=>$data['teacher_detail'],
                'teacher_img'=>$data['teacher_img'],
                'addtime'=>time()
            );
            $result = $teacherModel->insertDataOne($arr,url('teacher/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);

        }
        return   $this->fetch();
    }
    //编辑导师
    public function editTeacher(){
        $id = input('param.id');
        $teacherModel  = new TeacherModel();
        $firstOne = $teacherModel->getOne(array('id'=>$id));
        $old_img = $firstOne['teacher_img'];
        if(!$id){
            putMsg(0,'系统错误');
        }
        if(request()->isAjax()){
            $data = input('param.');
            $teacherModel = new TeacherModel();
            if(!$data['name_z'] && !$data['name_y']){
                putMsg(0,'请填写导师的一个名字');
            }
            $name_z_c = $teacherModel->checkedNameUnique(array('name_z'=>$data['name_z']));
            $name_y_c = $teacherModel->checkedNameUnique(array('name_y'=>$data['name_y']));
            if($name_z_c && $data['id'] != $name_z_c['id']){
                putMsg(0,'该中文名称已经存在');
            }
            if($name_y_c && $data['id'] != $name_y_c['id']){
                putMsg(0,'该英文名称已经存在');
            }
            if(!is_numeric($data['age']) || is_numeric($data['age']) && $data['age'] > 100){
                putMsg(0,'请正确输入年龄');
            }
            if($data['default_img'] && $data['default_img'] != $old_img){
                $new_teacher_img = extendImage($data['default_img'], 'teacherHead', array(array('width' => 120, 'height' => 100),array('width' => 100, 'height' => 60)));
                $arrData['teacher_img'] = $new_teacher_img;
            }
            $arrData['name_z'] = $data['name_z'] ;
            $arrData['name_y'] =$data['name_y']  ;
            $arrData['sex'] =$data['sex'] ;
            $arrData['age'] = $data['age'];
            $arrData['teacher_detail'] =$data['teacher_detail'] ;
            $arrData['addtime'] = time();
            $arrData['id'] = $data['id'];
            $result = $teacherModel->ediDataOne($arrData,url('teacher/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            if($old_img && $new_teacher_img){
                removeUploadImage(array(array($old_img, 120, 100),array($old_img,100, 60)));
            }
            putMsg(1,$result[1]);


        }
        $this-> assign(['data'=>$firstOne]) ;
        return  $this->fetch();
    }
    //删除导师
    public function delTeacher(){
        $id = input('param.id');
        if(!$id){
            putMsg(0,'系统错误');
        }
        $teacherModel = new TeacherModel();
        $courseModel = new CourseModel();
        $result = $teacherModel->getOne(array('id'=>$id));
        $course = $courseModel->getListCount(array('teacher_id'=>$id));
        if(!$result){
            putMsg(0,'系统错误');
        }
        if($course >= 1){
            putMsg(0,'此导师还有相关课程，不能删除');
        }
        $img_url = $result['teacher_img'];
        $result = $teacherModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        removeUploadImage(array(array($img_url,120,100),array($img_url,100,60)));
            putMsg(1,'删除成功');
    }

}