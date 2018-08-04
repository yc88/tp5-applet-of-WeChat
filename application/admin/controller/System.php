<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-19
 * Time: 9:18
 */
namespace app\admin\controller;
use app\admin\model\BannerModel;
use think\Db;

class System extends  Base
{
    //站点设置
    public function webconfig(){
        $siteModel = Db::table('fl_site');
        $One = $siteModel->where(array('id'=>1))->find();
        if(request()->isAjax()){
            $data = input('param.');
            $arr = array();
            if($data['id']){
                $old_logo = $data['old_logo'];
                $arr['id'] = $data['id'];
                $arr['web_name_z'] = $data['web_name_z'];
                $arr['web_name_y'] =$data['web_name_y'] ;
                $arr['site_url'] =$data['site_url'] ;
                $arr['email'] =$data['email'] ;
                $arr['phone'] = $data['phone'];
                $arr['address'] =$data['address'] ;
                $arr['records'] = $data['records'];
                $newLogo = '';
                if($data['default_img'] && $old_logo != $data['default_img']){
                    $newLogo = extendImage($data['default_img'],'logo',array(array('width'=>120,'height'=>100)));
                    $arr['logo'] = $newLogo;
                }
                $siteModel = Db::table('fl_site');
               $result =$siteModel->where('id',$arr['id'])->update($arr);
                if($result != false){
                    putMsg(0,'修改失败');
                }
                if($newLogo){
                    removeUploadImage(array($old_logo,120,100));
                }
                putMsg(1,'修改成功');
            }
        }
        $this->assign([
           'site'=>$One
        ]);
        return $this->fetch();
    }
    //banner列表
    public function banner(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $bannerModel = new BannerModel();
            $selectResult = $bannerModel->getIndexList($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $vo['addtime'] = date('Y-m-d H:i:s',$vo['addtime']);
                $vo['type'] = $vo['type'] == 1 ? 'PC' : '手机';
                $vo['is_show'] = $vo['is_show'] == 1 ? '显示' : '隐藏';
                $selectResult[$key]['operate'] = showOperate(makeButtonAll($vo['id'],'system/editbanner','system/delbanner'));
            }
            $return['total'] = $bannerModel->getListCount($where);  // 总数据
            $return['rows'] = $selectResult;
            putMsg1(1,$return);
        }
        return $this->fetch();
    }
    //banner 添加
    public function addbanner(){
        if(request()->isAjax()){
            $bannerModel = new BannerModel();
            $data = input('param.');
            if(!$data['title']){
                putMsg(0,'请填写标题');
            }
            if($bannerModel->checkedNameUnique(array('title'=>$data['title']))){
               putMsg(0,'该名称已经存在');
            }
            if($data['default_img']){
                $newImg = extendImage($data['default_img'],'banner',array(array('width'=>1200,'height'=>600),array('width'=>1200,'height'=>150)));
                $data['img_url'] = $newImg;
            }
            $data['addtime'] = time();
            $result = $bannerModel->insertDataOne($data,url('system/banner'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        return $this->fetch();
    }
    //banner 修改
    public function editbanner(){
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误');
        }
        $bannerModel = new BannerModel();
        $data_one = objToArray($bannerModel->getOne($id));
         $old_img =  $data_one['img_url'];
        if(request()->isAjax()){
            $bannerModel = new BannerModel();
            $data = input('param.');
            if(!$data['title']){
                putMsg(0,'请填写标题');
            }
            $checke = $bannerModel->checkedNameUnique(array('title'=>$data['title']));
            if($checke && $data['id'] !== $checke['id']){
                putMsg(0,'该名称已经存在');
            }
            $arr['id'] = $data['id'];
            $arr['title'] = $data['title'];
            $arr['link'] = $data['link'];
            $arr['type'] = $data['type'];
            $arr['sort'] = $data['sort'];
            $arr['is_show'] = $data['is_show'];
            $newImg = '';
            if($data['default_img']){
                $newImg = extendImage($data['default_img'],'banner',array(array('width'=>1200,'height'=>600),array('width'=>1200,'height'=>150)));
                $arr['img_url'] = $newImg;
            }
            $arr['addtime'] = time();
            $result = $bannerModel->ediDataOne($arr,url('system/banner'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            if($old_img && $newImg){
                removeUploadImage(array(array($old_img,1200,600),array($old_img,1200,150)));
            }
            putMsg(1,$result[1]);
        }
        $this->assign([
            'data'=>$data_one
        ]);
        return $this->fetch();
    }
    //banner 删除
    public function delbanner(){
        $id = input('param.');
        if(!$id){
            putMsg(0,'系统错误');
        }
        $bannerModel = new BannerModel();
        $data = $bannerModel->getOne($id);
        $img = $data['img_url'];
        $result = $bannerModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        if($img){
            removeUploadImage(array(array($img,1200,600),array($img,1200,150)));
        }
        putMsg(1,$result[1]);
    }
}