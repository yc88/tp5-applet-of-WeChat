<?php
/**资讯管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-20
 * Time: 9:56
 */
namespace app\admin\controller;
use app\admin\model\InfoClassifyModel;
use app\admin\model\InfoModel;
use app\admin\model\UserAdmin;
use app\admin\model\UserModel;

class Info extends Base
{
    //资讯列表
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
             if (!empty($param['searchText'])) {
                 $where['info_name'] = ['like', '%' . $param['searchText'] . '%'];
             }
            $infoModel = new InfoModel();
            $userModel = new UserAdmin();
            $infoClassify = new InfoClassifyModel();
            $selectResult = $infoModel->getIndexList($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $vo['info_author'] = $userModel->getOneUser($vo['info_author'])['user_name'];
                $vo['info_time'] = date('Y-m-d H:i:s',$vo['info_time']);
                $vo['info_cate'] = $infoClassify->getOne($vo['info_cate'])['cate_name'];
                $vo['is_show'] = $vo['is_show'] == 1 ? '是' : '否';
                $selectResult[$key]['operate'] = showOperate(makeButtonAll($vo['id'],'info/editinfo','info/delinfo'));
            }
            $return['total'] = $infoModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
       return $this->fetch();
    }
    //资讯 add
    public function addinfo(){
        $classifyModel = new InfoClassifyModel();
        $infoModel = new InfoModel();
        if(request()->isAjax()){
            $data = input('param.');
            if(!$data['info_name']){
                putMsg(0,'请填写课程名称');
            }
            if(!$data['info_cate']){
                putMsg(0,'请选择分类');
            }
            if($infoModel->checkedNameUnique(array('info_name'=>$data['info_name']))){
                putMsg(0,'该名称已经存在');
            }
            $arr['info_cate'] = $data['info_cate'];
            $arr['info_name'] = $data['info_name'];
            $arr['info_detail'] = $data['info_detail'];
            $arr['is_show'] = $data['is_show'];
            if($data['info_img']){
                $imgUrl =  extendImage($data['info_img'],'InfoImg',array(
                    array(
                        'width'=>300,
                        'height'=>150
                    ),
                    array(
                        'width'=>600,
                        'height'=>600
                    ),
                    array(
                        'width'=>1200,
                        'height'=>600
                    ),
                ));
                $arr['info_img'] = $imgUrl;
            }
            $arr['info_time'] = time();
            $arr['info_author'] = session('id');
            $result = $infoModel->insertDataOne($arr,url('info/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }

        $classify = $classifyModel->getAll('','id,cate_name');
        $this->assign([
            'infocate'=>objToArray($classify),
        ]);
        return $this->fetch();
    }
    //资讯 编辑
    public function editinfo(){
        $classifyModel = new InfoClassifyModel();
        $infoModel = new InfoModel();
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误');
        }
        if(request()->isAjax()){
            $data = input('param.');
            if(!$data['info_name']){
                putMsg(0,'请填写课程名称');
            }
            if(!$data['info_cate']){
                putMsg(0,'请选择分类');
            }
            if($id != $data['id'] && $infoModel->checkedNameUnique(array('info_name'=>$data['info_name']))){
                putMsg(0,'该名称已经存在');
            }
            $arr['id'] =$data['id'] ;
            $arr['info_cate'] = $data['info_cate'];
            $arr['info_name'] = $data['info_name'];
            $arr['info_detail'] = $data['info_detail'];
            $arr['is_show'] = $data['is_show'];
            $imgUrl = '';
            if($data['info_img'] && $data['info_img'] !=$data['old_img']){
                $imgUrl =  extendImage($data['info_img'],'InfoImg',array(
                    array(
                        'width'=>300,
                        'height'=>150
                    ),
                    array(
                        'width'=>600,
                        'height'=>600
                    ),
                    array(
                        'width'=>1200,
                        'height'=>600
                    ),
                ));
                $arr['info_img'] = $imgUrl;
            }
            $arr['info_time'] = time();
            $arr['info_author'] = session('id');
            $result = $infoModel->ediDataOne($arr,url('info/index'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            if($imgUrl){
                removeUploadImage(array(array($data['old_img'],300,150),array($data['old_img'],600,600),array($data['old_img'],1200,600)));
            }
            putMsg(1,$result[1]);
        }
        $info = objToArray($infoModel->getOne($id));
        $classify = $classifyModel->getAll('','id,cate_name');
        $this->assign([
            'info'=>$info,
            'infocate'=>objToArray($classify),
        ]);
        return $this->fetch();
    }
    //资讯 删除
    public function delinfo(){
        $id = input('param.');
        $InfoModel = new InfoModel();
        $data = $InfoModel->getOne($id);
        if(!$data){
            putMsg(0,'系统错误');
        }
        $img = $data['info_img'];
        $result = $InfoModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
     removeUploadImage(array(
                array($img,300,150),
                 array($img,600,600),
               array($img,1200,600)
            ));
        putMsg(1,$result[1]);
    }

    //资讯分类
    public function infoclassify(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['cate_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $cate = new InfoClassifyModel();
            $selectResult = $cate->getIndexList($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $vo['cate_time'] = date('Y-m-d H:i:s',$vo['cate_time']);
                $selectResult[$key]['operate'] = showOperate(makeButtonAll($vo['id'],'info/editinfoclassify','info/delinfoclassify'));
            }
            $return['total'] = $cate->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        return $this->fetch();
    }
    //资讯分类添加
    public function addinfoclassify(){
        if(request()->isAjax()){
            $data = input('param.');
            $CateModel = new InfoClassifyModel();
            $data['cate_time'] = time();
            if(!$data['cate_name']){
                putMsg(0,'请填写分类名称');
            }
            if($CateModel->checkedNameUnique(array('cate_name'=>$data['cate_name']))){
                putMsg(0,'该名称已经存在');
            }
            $result = $CateModel->insertDataOne($data,url('info/infoclassify'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        return $this->fetch();
    }
    //资讯分类编辑
    public function editinfoclassify(){
        $id = input('param.id');
        $CateModel = new InfoClassifyModel();
        if(request()->isAjax()){
            $data = input('param.');
            $CateModel = new InfoClassifyModel();
            $data['cate_time'] = time();
            if(!$data['cate_name']){
                putMsg(0,'请填写分类名称');
            }
            $name_is = $CateModel->checkedNameUnique(array('cate_name'=>$data['cate_name']));
            if($name_is && $name_is['id'] != $data['id']){
                putMsg(0,'该名称已经存在');
            }
            $result = $CateModel->ediDataOne($data,url('info/infoclassify'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        $this->assign([
            'infocate' => $CateModel->getOne($id)
        ]);
        return $this->fetch();
    }
    //资讯分类删除
    public function delinfoclassify(){
        $id = input('param.id');
        $CateModel = new InfoClassifyModel();
        $infoModel = new InfoModel();
        $number = $infoModel->checkedCateSon($id);
        if($number >= 1 ){
            putMsg(0,'该分类下面还有资讯，请重新操作');
        }
        $result = $CateModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,$result[1]);
        return $this->fetch();
    }
}