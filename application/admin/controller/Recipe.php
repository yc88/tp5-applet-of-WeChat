<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/28
 * Time: 13:55
 */
namespace app\admin\controller;
use app\admin\model\CourseModel;
use app\admin\model\PurchaseModel;
use app\admin\model\PurchaseNumModel;
use app\admin\model\RecipeElementAmountModel;
use app\admin\model\RecipeElementModel;
use app\admin\model\RecipeModel;
use app\admin\model\RecipeNameModel;
use app\admin\model\UserAdmin;
use think\Db;

class Recipe extends Base
{
    /**元素库存列表管理
     * @return mixed
     */
    public function inventory(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['element_name'])) {
                $where['element_name'] = ['like', '%' . $param['element_name'] . '%'];
            }
            if (!empty($param['begin_oktime'])) {
                $where['addtime'] = ['gt',  strtotime($param['begin_oktime'])];
            }
            if (!empty($param['end_oktime'])) {
                $where['addtime'] = ['lt',  strtotime($param['end_oktime'])];
            }
            if (!empty($param['begin_oktime']) && !empty($param['end_oktime'])) {
                if($param['begin_oktime'] > $param['end_oktime']){
                    $where['addtime'] = array();
                }
                $where['addtime'] = array('between',[strtotime($param['begin_oktime']),strtotime($param['end_oktime'])]) ;
            }
            $RecipeModel = new RecipeElementModel();
            $user = new UserAdmin();
            $selectResult = $RecipeModel->getIndexList($where, $offset, $limit);
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['author'] = $user->getOneUser($vo['author'])['user_name'];
                $selectResult[$key]['addtime'] = date('Y-m-d H:i:s', $vo['addtime']);
                $selectResult[$key]['inventory_num'] =$vo['inventory_num'].($vo['element_unit'] == 2 ? 'g' : Db::table('fl_inventory_unit')->where(array('id'=>$vo['element_unit']))->find()['unit_name']);
                $selectResult[$key]['input'] = "<input type='checkbox' value='{$vo['id']}' name='status_ok' onclick='son_checked()'>"; //excel educe
                $selectResult[$key]['operate'] = showOperate($this->makeInventoryButton($vo['id']));
            }
            $return['total'] = $RecipeModel->getListCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        return $this->fetch();
    }

    /**添加元素库存
     * @return mixed
     */
    public function addinventory(){
        $unitList = Db::table('fl_inventory_unit')->order('id ASC')->select();
        if(request()->isAjax()){
            $data= input('param.');
            $element_name = $data['element_name'];
            $inventory_num = $data['inventory_num'];
            $unit_id = $data['unit_id'];
             if(!$element_name){
                 putMsg(0,'元素名称不能为空');
             }
//            if(!$inventory_num || $inventory_num < 0){
//                putMsg(0,'库存数量不能为空或者不能小于零');
//            }
            if(!$unit_id){
                putMsg(0,'单位不能为空');
            }
            if(intval($data['unit_id']) == 2){
                $data['inventory_num'] = $inventory_num*1000; //单位变成 g
            }
            try{
                $data['element_unit'] = $unit_id;
                $data['addtime'] = time();
                $data['author'] = session('id');
                $recipeModel = new RecipeElementModel();
                $result = $recipeModel->insertDataOne($data,url('recipe/inventory'));
                if($result[0] == 0){
                    putMsg(0,$result[1]);
                }
                putMsg(1,$result[1]);
            }catch (\Exception $e){
                putMsg(0,$e->getMessage().__LINE__);
            }
        }
        $this->assign([
           'unitList' => $unitList
        ]);
        return $this->fetch();
    }

    /**编辑元素库存
     * @return mixed
     */
    public function editinventory(){
        $unitList = Db::table('fl_inventory_unit')->order('id ASC')->select();
        $id = input('param.id');
        if(!$id){
            $this->error('系统错误');
        }
        $recipeModel = new RecipeElementModel();
        if(request()->isAjax()){
            $data= input('param.');
            $element_name = $data['element_name'];
            $inventory_num = $data['inventory_num'];
            $unit_id = $data['unit_id'];
            if(!$element_name){
                putMsg(0,'元素名称不能为空');
            }
//            if(!$inventory_num || $inventory_num < 0){
//                putMsg(0,'库存数量不能为空或者不能小于零');
//            }
            if(intval($unit_id) == 2){
                $inventory_num= $inventory_num*1000; //单位变成 g
            }
            if(!$unit_id){
                putMsg(0,'单位不能为空');
            }
            try{
                $arr = array(
                    'id'=>$data['id'],
                    'element_name'=>$element_name,
                    'inventory_num'=>$inventory_num
                );
                $arr['element_unit'] = $unit_id;
                $arr['addtime'] = time();
                $arr['author'] = session('id');
                $recipeModel = new RecipeElementModel();
                $result = $recipeModel->ediDataOne($arr,url('recipe/inventory'));
                if($result[0] == 0){
                    putMsg(0,$result[1]);
                }
                putMsg(1,$result[1]);
            }catch (\Exception $e){
                putMsg(0,$e->getMessage().__LINE__);
            }
        }
        $data = $recipeModel->getOne($id);
        if(intval($data['element_unit']) == 2){
            $data['inventory_num'] = $data['inventory_num']/1000;
        }
        $this->assign([
            'unitList' => $unitList,
            'data'=>$data
        ]);
        return $this->fetch();
    }

    /**
     * 删除库存
     */
    public function delinventory(){
        $id = input('param.id');
        $recipeModel = new RecipeElementModel();
        $result = $recipeModel->delDataOne($id);
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,$result[1]);
    }

    /**
     * 导出库存
     */
    public function downinventory(){

        putMsg(1,'正在下载 ');
    }
    /**
     * 拼装元素库存操作按钮
     * @param $id
     * @return array
     */
    private function makeInventoryButton($id)
    {
        return [
            '详情' => [
                'auth' => 'order/orderdetail',
                'href' =>"javascript:editinventory($id)",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-search'
            ],
            '删除' => [
                'auth' => 'order/delorder',
                'href' => "javascript:orderDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
        ];
    }

    /**
     * 拼装食谱库存操作按钮
     * @param $id
     * @return array
     */
    private function makeRecipeButton($id)
    {
        return [
            '详情' => [
                'auth' => 'recipe/editrecipe',
                'href' =>url('recipe/editrecipe', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-search'
            ],
            '删除' => [
                'auth' => 'recipe/delrecipe',
                'href' => "javascript:orderDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            '导出' => [
                'auth' => 'recipe/downrecipe',
                'href' => url('recipe/downrecipe', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-cloud-download'
            ],
        ];
    }

    /**食谱列表页面
     * @return mixed
     */
    public function recipeindex(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where['pid'] =0;
            if (!empty($param['recipe_name'])) {
                $where['recipe_name'] = ['like', '%' . trim($param['recipe_name']) . '%'];
//              $data =  Db::table('fl_recipe_name')->where($where1)->select();
//                $str = array();
//                    foreach($data as $k =>$v){
//                        $str[]= $v['id'];
//                    }
//                $str = implode(',',$str);
//                $where['recipe_name_id'] = ['in',  $str];
            }
            if (!empty($param['begin_oktime'])) {
                $where['addtime'] = ['gt',  strtotime($param['begin_oktime'])];
            }
            if (!empty($param['end_oktime'])) {
                $where['addtime'] = ['lt',  strtotime($param['end_oktime'])];
            }
            if (!empty($param['begin_oktime']) && !empty($param['end_oktime'])) {
                if($param['begin_oktime'] > $param['end_oktime']){
                    $where['addtime'] = array();
                }
                $where['addtime'] = array('between',[strtotime($param['begin_oktime']),strtotime($param['end_oktime'])]) ;
            }
            $RecipeModel = new RecipeModel();
            $recipeName = new RecipeNameModel();
            $user = new UserAdmin();
            $courseModel = new CourseModel();
            $selectResult = $recipeName->getIndexList($where, $offset, $limit);
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['author'] = $user->getOneUser($vo['author'])['user_name'];
                $selectResult[$key]['addtime'] = date('Y-m-d H:i:s', $vo['addtime']);
//                $selectResult[$key]['recipe_name_id'] =Db::table('fl_recipe_name')->where(array('id'=>$vo['recipe_name_id']))->find()['recipe_name'];
                 $selectResult[$key]['courser_id'] =$courseModel->getOne(array('id'=>$vo['courser_id']))['courser_name'];
                $selectResult[$key]['input'] = "<input type='checkbox' value='{$vo['id']}' name='status_ok' onclick='son_checked()'>"; //excel educe
                $selectResult[$key]['operate'] = showOperate($this->makeRecipeButton($vo['id']));
            }
            $return['total'] = $recipeName->getListCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    /**添加食谱
     * @return mixed
     */
    public function addrecipe(){
        $courseModel = new CourseModel();
        $course = $courseModel->getAll('','id,courser_name','id ASC');
        $this->assign([
            'course'=>$course
        ]);
        return $this->fetch();
    }

    /**添加食谱名称
     * @return mixed
     */
    public function addrecipename(){
        $recipeNameModel = new RecipeNameModel();
        $courseModel = new CourseModel();
        $course = $courseModel->getAll('','id,courser_name','id ASC');
        $recipe = $recipeNameModel->getAll(array('pid'=>0),'id,recipe_name,courser_id','id ASC');
        foreach($recipe as $k => $v){
            $recipe[$k]['son'] = $recipeNameModel->getAll(array('pid'=>$v['id']),'id,recipe_name','id ASC');
        }
        if(request()->isAjax()){
            $data = input("param.");
            $data['addtime'] = time();
            $data['author'] = session('id');
            $result = $recipeNameModel->insertDataOne($data,url('recipe/addrecipe'));
            if($result[0] == 0){
                putMsg(0,$result[1]);
            }
            putMsg(1,$result[1]);
        }
        $this->assign([
            'course'=>$course,
            'recipe'=>$recipe
        ]);
        return $this->fetch();
    }

    /**
     * 编辑食谱名称
     */
    public function editrecipename(){
        $data = input('param.');
        if(!$data['title'] || !$data['cid'] ||  !$data['id'] ){
            putMsg(1,'系统错误');
        }
        $recipeNameModel = new RecipeNameModel();
        $arr = array(
            'recipe_name' =>trim($data['title']),
            'author'=>session('id'),
            'addtime'=>time()
        );
        $where = array(
            'id'=>$data['id'],
            'courser_id'=>$data['cid']
        );
        $count = $recipeNameModel->getListCount(array('courser_id'=>$data['cid'],'id'=>$data['id'],'recipe_name' =>trim($data['title'])));
        if($count){
            putMsg(1,'操作成功1');
        }
        $result = $recipeNameModel->where($where)->update($arr);
        if($result != false){
            putMsg(1,'操作成功');
        }
        putMsg(0,'操作失败');
    }

    /**
     * 添加具体的食谱 元素
     */
    public function addrecipedetail(){
        $id = input("param.id");
        $cid = input("param.cid");
        if(!$id || !$cid){
            putMsg(0,'系统错误');
        }
        $recipeNameModel = new RecipeNameModel();
        $courseModel = new CourseModel();
        $recipeElement = new RecipeElementModel();
        $nameData = $recipeNameModel->getOne(array('id'=>$id));
        $course = $courseModel->getOne(array('id'=>$cid));
        $element = $recipeElement->getAll('','id,element_name','id ASC');
        if(request()->isAjax()){
            $data = input("param.");
            $is_demo = $data['is_demo'];
            $cid = $data['cid'];
            $id = $data['id'];
            if(!isset($data['element_name']) && !isset($data['students_use']) && !isset($data['teachers_use']) && !isset($data['is_back_num']) && !isset($data['unit_id'])){
                putMsg(0,'请填写元素');
            }
            $element_name = $data['element_name']; //元素名称 array
            $students_use = $data['students_use']; //学生用量
            $teachers_use = $data['teachers_use']; //老师用量
            $is_back_num = $data['is_back_num']; //回收资源
            $unit_id = $data['unit_id'];//元素的单位

            $recipeModel = new RecipeModel(); //食谱主表
            $topR  = array(
                'course_id'=>$cid,
                'recipe_name_id'=>$id,//课程的食谱名称
                'is_demo'=>$is_demo,
                'addtime'=>time(),
                'author'=>session('id')
            );
            try{
                $recipe_id = $recipeModel->insertGetId($topR);
                if(!$recipe_id){
                    putMsg(0,'系统错误');
                }
                $elementArr['recipe_id'] = $recipe_id;
                foreach($element_name as $k =>$v){
                    $elementArr['element_id'] = $v;
                    $elementArr['element_unit'] = $unit_id[$k];
                    if($unit_id[$k] == 2){
                        $elementArr['students_use'] = $students_use[$k]*1000;
                        $elementArr['teachers_use'] = $teachers_use[$k]*1000;
                        $elementArr['is_back_num'] = $is_back_num[$k]*1000;
                    }else{
                        $elementArr['students_use'] = $students_use[$k];
                        $elementArr['teachers_use'] = $teachers_use[$k];
                        $elementArr['is_back_num'] = $is_back_num[$k];
                    }
                    Db::table('fl_recipe_element_amount')->insertGetId($elementArr);
                }
                $headR = $recipeModel->getOne(array('id'=>$recipe_id));
                $footR = Db::table('fl_recipe_element_amount')->where(array('recipe_id'=>$recipe_id))->field('id,element_id,element_unit,students_use,teachers_use,is_back_num')->select();
                $recipeNameArr = $recipeNameModel->getOne(array('id'=>$headR['recipe_name_id']));
                $recipeName = $recipeNameArr['recipe_name'];
                if($recipeNameArr['pid'] != 0){
                    $recipeName =$recipeNameModel->getOne(array('id'=>$recipeNameArr['pid']))['recipe_name'].'===>'.$recipeNameArr['recipe_name'];
                }
                foreach($footR as $fk =>$fv){
                    $footR[$fk]['element_name'] = $recipeElement->where(array('id'=>$fv['element_id']))->field('id,element_name')->find()['element_name'];
                    $footR[$fk]['element_unit_name'] =Db::table('fl_inventory_unit')->find()['unit_name'];
                }
                $backName = array(
                    'recipeName'=>$recipeName,
                    'detailElement'=>$footR
                );

                putMsg(1,$backName);
            }catch (\Exception $e){
                putMsg(0,$e->getMessage());
            }
        }
        $this->assign([
            'nameData'=>$nameData,
            'course'=>$course,
            'element'=>$element
        ]);
        return $this->fetch();
    }

    /**
     * 新增该食谱下的新元素
     */
    public function  addonerecipedetail(){
        $id = input("param.id");
        $cid = input("param.cid");
        if(!$id || !$cid){
            $this->error('系统错误');
        }
        $recipeNameModel = new RecipeNameModel();
        $courseModel = new CourseModel();
        $recipeElement = new RecipeElementModel();
        $recipeModel = new RecipeModel(); //食谱主表
        $recipeElementAmoutModel = new RecipeElementAmountModel();
        $nameData = $recipeNameModel->getOne(array('id'=>$id));
        $course = $courseModel->getOne(array('id'=>$cid));
        $oneRecipe = $recipeModel->getOne(array('recipe_name_id'=>intval($nameData['id']),'course_id'=>intval($cid)));
        if(!isset($oneRecipe)){
          $this->error('该食谱还没创建，请先去创建食谱');
        }
        $exesits_element = $recipeElementAmoutModel->getAll(array('recipe_id'=>$oneRecipe['id']),'id,element_id');
        $exesit_id = array();
        foreach($exesits_element as $ak => $av){
            $exesit_id[] = $av['element_id'];
        }
        $exesit_id_str = implode(',',$exesit_id);
        $where['id'] = ['not in',$exesit_id_str];
        $element = $recipeElement->getAll($where,'id,element_name','id ASC');
        if(request()->isAjax()){
            $data = input("param.");
            $cid = $data['cid'];
            $id = $data['id'];
            if(!isset($data['element_name']) && !isset($data['students_use']) && !isset($data['teachers_use']) && !isset($data['is_back_num']) && !isset($data['unit_id'])){
                putMsg(0,'请填写元素');
            }
            $element_name = $data['element_name']; //元素名称 array
            $students_use = $data['students_use']; //学生用量
            $teachers_use = $data['teachers_use']; //老师用量
            $is_back_num = $data['is_back_num']; //回收资源
            $unit_id = $data['unit_id'];//元素的单位
            try{
                $recipe_id = $recipeModel->getOne(array('recipe_name_id'=>intval($nameData['id']),'course_id'=>intval($cid)));
                if(!isset($recipe_id)){
                    putMsg(0,'系统错误');
                }
             $ids = array();
                $elementArr['recipe_id'] = $recipe_id['id'];
                foreach($element_name as $k =>$v){
                    $elementArr['element_id'] = $v;
                    $elementArr['element_unit'] = $unit_id[$k];

                    if($unit_id[$k] == 2){
                        $elementArr['students_use'] = $students_use[$k]*1000;
                        $elementArr['teachers_use'] = $teachers_use[$k]*1000;
                        $elementArr['is_back_num'] = $is_back_num[$k]*1000;
                    }else{
                        $elementArr['students_use'] = $students_use[$k];
                        $elementArr['teachers_use'] = $teachers_use[$k];
                        $elementArr['is_back_num'] = $is_back_num[$k];
                    }
                    $ids[] = Db::table('fl_recipe_element_amount')->insertGetId($elementArr);
                }
                putMsg(1,'操作成功');
            }catch (\Exception $e){
                putMsg(0,$e->getMessage());
            }
        }

        $this->assign([
            'nameData'=>$nameData,
            'course'=>$course,
            'element'=>$element
        ]);
       return $this->fetch();
    }
    /**
     * 删除具体的食谱元素
     */
    public function deldetailelement(){
        $id = input('param.id');
        if(!$id){
            putMsg(0,'系统错误');
        }
        $recipeElementAmountModel = new RecipeElementAmountModel();
        $count = $recipeElementAmountModel->getListCount(array('id'=>$id));
        if($count == 0){
            putMsg(0,'系统错误');
        }
        $recipeElementAmountModel->where(array('id'=>$id))->delete();
        putMsg(1,'操作成功');
    }

    /**
     * 编辑具体的食谱元素
     */
    public function editdetailelement(){
        $data = input('param.');
        if(!$data['id'] || !$data['recipe_id'] || !$data['element_id'] || !$data['element_unit']){
            putMsg(0,'系统错误');
        }
        if($data['element_unit'] == 2){
            $data['students_use'] =  $data['students_use']*1000;
            $data['teachers_use'] =  $data['teachers_use']*1000;
            $data['is_back_num'] =  $data['is_back_num']*1000;
        }
        $recipeElementModel = new RecipeElementAmountModel();
        $result = $recipeElementModel->ediDataOne($data,url(''));
        if($result[0] == 0){
            putMsg(0,$result[1]);
        }
        putMsg(1,'操作成功');
    }

    /**编辑食谱 食谱详情
     * @return mixed
     */
    public function editrecipe(){
        $id = input("param.");
        if(!$id){
            $this->error('系统错误');
        }
        $courseModel = new CourseModel();
        $recipeNameModel = new RecipeNameModel();
        $recipeModel = new RecipeModel();
        $recipeElementModel = new RecipeElementModel();
        $recipeAmountModel = new RecipeElementAmountModel();
        $course = $courseModel->getAll('','id,courser_name','id ASC');
        $recipeName = $recipeNameModel->getOne(array('id'=>$id['id'],'pid'=>0));
        $count = $recipeNameModel->getListCount(array('pid'=>$id['id']));
        $recipe = $recipeModel->getOne(array('course_id'=>$recipeName['courser_id'],'recipe_name_id'=>$recipeName['id'])); //只有一个顶级食谱存在时候
        if($count > 0 && !$recipe){ //存在多级食谱情况下
            $son = $recipeNameModel->getAll(array('pid'=>$recipeName['id'],'courser_id'=>$recipeName['courser_id']),'id,recipe_name,courser_id');
            $recipeName['is_recipe'] = false;
            $recipe['recipe_name'] = $recipeName; //顶级
            $recipeName['son_recipe_name'] =$son;
//            dump(objToArray($recipe));
            foreach($son as $ak => $av){ //获取子级食谱
                $sonRecipe=$recipeModel->getOne(array('course_id'=>$av['courser_id'],'recipe_name_id'=>$av['id']));
                if($sonRecipe){ //添加了食谱的
                    $son[$ak]['is_exists_recipe'] = true;
                    $recipeData = Db::table('fl_recipe_element_amount')->where(array('recipe_id'=>$sonRecipe['id']))->select();
                    foreach($recipeData as $k => $v){
                            $recipeData[$k]['element_name'] = Db::table('fl_recipe_element')->where(array('id'=>$v['element_id']))->field('id,element_name')->find()['element_name'];
                            $recipeData[$k]['element_unit_name'] = Db::table('fl_inventory_unit')->where(array('id'=>$v['element_unit']))->find()['unit_name'];
                        if($v['element_unit'] == 2){
                            $recipeData[$k]['students_use'] = $v['students_use']/1000;
                            $recipeData[$k]['teachers_use'] =$v['teachers_use']/1000;
                            $recipeData[$k]['is_back_num'] =$v['is_back_num']/1000;
                        }
                    }
                    $son[$ak]['recipe'] = $sonRecipe;
                    $sonRecipe['recipe_data'] = $recipeData;
                } else{
                    $son[$ak]['is_exists_recipe'] = false;
                    $sonRecipe['recipe_data']=array();
                }
            }
        }else if($count == 0 && $recipe){ //就只有顶级名称的时候
            $recipeName['is_recipe'] = true;
            $recipe['recipe_name'] = $recipeName;
            $element = $recipeAmountModel->getAll(array('recipe_id'=>$recipe['id']));
            foreach($element as $ek => $ev){
                $element[$ek]['element_name'] = $recipeElementModel->getOne(array('id'=>$ev['element_id']))['element_name'];
                $element[$ek]['element_unit_name'] = Db::table('fl_inventory_unit')->where(array('id'=>$ev['element_unit']))->find()['unit_name'];
                if($ev['element_unit'] == 2){
                    $element[$ek]['students_use'] = $ev['students_use']/1000;
                    $element[$ek]['teachers_use'] =$ev['teachers_use']/1000;
                    $element[$ek]['is_back_num'] =$ev['is_back_num']/1000;
                }
            }
            $recipeName['element'] = $element;
//            dump(objToArray($recipe));
        }else{ //没有创建食谱的情况下
            $this->error('该食谱名称还没有创建具体的食谱',url('recipe/addrecipe'));
        }

        /**
         * 编辑食谱课程 是否是演示
         */
        if(request()->isPost()){
            $data = input('param.');
            $r_id = $data['r_id']; //食谱顶级id
            $course_id = $data['courser_id'];
            $is_course = $courseModel->getListCount(array('id'=>$course_id));
            if(!$is_course){
                putMsg(0,'系统错误');
            }
            $course_id_old = Db::table('fl_recipe_name')->where(array('id'=>$r_id))->field('id,courser_id')->find();
//            dump($course_id_old);exit;
            if(intval($course_id_old['courser_id']) == intval($course_id)){
                putMsg(1,url('recipe/recipeindex'));
            }
            try{
//                两种情况 1，顶级食谱 有次级食谱的时候
                $count = $recipeNameModel->getListCount(array('pid'=>$course_id_old['id']));
                if($count > 0){ //次级食谱
                $son = $recipeNameModel->getAll(array('pid'=>$course_id_old['id']));
                    foreach($son as $k =>$v){
                        $recipeModel
                            ->where(
                            array(
                                'recipe_name_id'=>$v['id']
                            )
                        )
                            ->update(
                                array(
                                    'course_id'=>intval($course_id),
                                    'addtime'=>time(),
                                    'author'=>session('id')));
                        $recipeNameModel->where(array('id'=>$v['id']))
                            ->update(
                                array(
                                    'courser_id'=>intval($course_id),
                                    'addtime'=>time())
                            );
                    }
                    $result = $recipeNameModel->where(array('id'=>$course_id_old['id']))
                        ->update(
                            array(
                        'courser_id'=>intval($course_id),
                        'addtime'=>time())
                        );
                    if($result !== false){
                        putMsg(1,url('recipe/recipeindex'));
                    }
                    putMsg(0,$recipeNameModel->getError());
                }else{ // 顶级食谱
                    $result = $recipeNameModel->ediDataOne(array(
                        'id' => $course_id_old['id'],
                        'courser_id' => $course_id,
                         'addtime'=>time(),
                          'author'=>session('id')
                    ), url(''));
                    if ($result[0] == 0) {
                        putMsg(0, $result[1]);
                    }
                    $result1 = $recipeModel
                        ->where(
                            array(
                                'recipe_name_id' => $course_id_old['id']
                            )
                        )
                        ->update(
                            array(
                                'course_id'=>intval($course_id),
                                'addtime'=>time(),
                                'author'=>session('id')));
                    if ($result1 != false) {
                        putMsg(1, url('recipe/recipeindex'));
                    }
                    putMsg(0,$recipeModel->getError());
                }
            }catch (\Exception $e){
                putMsg(0,$e->getMessage());
            }
        }
        $this->assign([
            'course'=>$course,
            'recipe'=>$recipe
        ]);
        return $this->fetch();
    }

    /**
     * 删除食谱
     */
    public function delrecipe(){

        putMsg(1,'删除成功');
    }

    /**
     * 食谱下载
     */
    public function downrecipe(){


        putMsg(1,'等待下载');
    }

    /**采购列表
     * @return mixed
     */
    public function purchase(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['purchase_name'])) { //采购名称
                $where['purchase_name'] = ['like', '%' . $param['purchase_name'] . '%'];
            }
            if (!empty($param['begin_oktime'])) {
                $where['addtime'] = ['gt',  strtotime($param['begin_oktime'])];
            }
            if (!empty($param['end_oktime'])) {
                $where['addtime'] = ['lt',  strtotime($param['end_oktime'])];
            }
            if (!empty($param['begin_oktime']) && !empty($param['end_oktime'])) {
                if($param['begin_oktime'] > $param['end_oktime']){
                    $where['addtime'] = array();
                }
                $where['addtime'] = array('between',[strtotime($param['begin_oktime']),strtotime($param['end_oktime'])]) ;
            }

            $purchaseModel = new PurchaseModel();
            $courseModel = new CourseModel();
            $user = new UserAdmin();
            $selectResult = $purchaseModel->getIndexList($where, $offset, $limit);
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['author'] = $user->getOneUser($vo['author'])['user_name'];
                $selectResult[$key]['addtime'] = date('Y-m-d H:i:s', $vo['addtime']);
                $selectResult[$key]['courser_id'] =$courseModel->field('id,courser_name')->find()['courser_name'];
                $selectResult[$key]['input'] = "<input type='checkbox' value='{$vo['id']}' name='status_ok' onclick='son_checked()'>"; //excel educe
                $selectResult[$key]['operate'] = showOperate($this->makePurchaseButton($vo['id']));
            }
            $return['total'] = $purchaseModel->getListCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        return $this->fetch();
    }
    /**采购 增加
     * @return mixed
     */
    public function addpurchase(){
        $courseModel = new CourseModel();
        $recipeModel = new RecipeModel();
        $recipeNameModel = new RecipeNameModel();
        $eleMentAmount = new RecipeElementAmountModel();
        $recipeElement = new RecipeElementModel();
        $purchaseModel = new PurchaseModel();
        $purchaseNumModel = new PurchaseNumModel();
        $course = $courseModel->getAll('','id,courser_name','id ASC');
        if(request()->isAjax()){
            $data = input('param.');
            $purchase_name = $data['purchase_name'];
            $student_num = $data['student_num'];
            $teacher_num = $data['teacher_num'];
            $courser_id = $data['courser_id'];
            $recipe_ids = $data['checkedStr'];
            $checkedStr = $data['checkedStr']; //string
            if(empty($checkedStr)){
                putMsg(0,'你还没有选择食谱');
            }
            if(!intval($courser_id)){
                putMsg(0,'选择课程');
            }
            if(!$purchase_name){
                putMsg(0,'添加采购名称');
            }
            if(intval($student_num) < 0 || intval($teacher_num) < 0){
                putMsg(0,'老师或者学生的人数不能小于零');
            }
            $is_course = $courseModel->getListCount(array('id'=>$courser_id));
            if($is_course != 1){
                putMsg(0,'该课程已经不存在，请重新操作');
            }
            try{
                $purchase_id = $purchaseModel->insertGetId(array(
                    'purchase_name'=>$purchase_name,
                    'student_num' =>$student_num,
                    'teacher_num'=>$teacher_num,
                    'courser_id'=>$courser_id,
                    'recipe_ids'=>$recipe_ids,
                    'addtime'=>time(),
                    'author'=>session('id')
                ));
                $recipe_id = explode(',',$checkedStr); //顶级食谱名称id
                foreach($recipe_id as $ik => $iv){ //总共的食谱数量的计算
                    $sonRecipe = $recipeNameModel->getAll(array('pid'=>$iv,'courser_id'=>$courser_id),'id,courser_id');
                    $is_exist_son = count($sonRecipe) == 0 ? false : true;
                    if($is_exist_son == true){ //存在子级食谱
                        foreach($sonRecipe as $sonK => $sonV){ //二级食谱 名称
             $is_son_exist_recipe = $recipeModel->getOne(array('recipe_name_id'=>$sonV['id'],'course_id'=>$courser_id)); //当前二级食谱的数据是否存在
                            if($is_son_exist_recipe){ //存在具体的食谱时处理
                                $eleMentAmountData = $eleMentAmount->getAll(array('recipe_id'=>$is_son_exist_recipe['id']));
                                foreach($eleMentAmountData as $ak => $av){ //元素成份
                                    $elementNum = $recipeElement->getOne(array('id'=>$av['element_id']));
                                    if($is_son_exist_recipe['is_demo'] == 1){ //只有老师用量 + 回收量 （库存总含量 inventory_total 该食谱使用总量 use_total）
                                        $eleMentAmountData[$ak]['use_total'] = $av['teachers_use'];
                                    }else{ //学员用量 + 老师用量 + 回收用量
                                        $eleMentAmountData[$ak]['use_total'] = $av['students_use']+$av['teachers_use'];
                                    }
                                    $eleMentAmountData[$ak]['inventory_total'] =$elementNum['inventory_num'];
                                    $eleMentAmountData[$ak]['unit_id'] =$elementNum['element_unit'];
                                    $is_exist_purchase_element = $purchaseNumModel
                                        ->checked_is_exist(
                                            array(
                                                'element_id'=>$av['element_id'],
                                                'courser_id'=>$courser_id,
                                                'purchase_id'=>intval($purchase_id)
                                            ));
                                    if($is_exist_purchase_element !== false){ //已经存在 修改该数据
                                        $purchaseNumModel
                                            ->where(array('id'=>$is_exist_purchase_element['id'],'element_id'=>$av['element_id'],'courser_id'=>$courser_id))
                                            ->update(array(
                                                'total'=>$is_exist_purchase_element['total'] + $eleMentAmountData[$ak]['use_total'],
                                                'back_total'=>$is_exist_purchase_element['back_total'] + $av['is_back_num']
                                            ));
                                    }else{ //不存在 新增该数据
                                        $purchaseNumModel->insertGetId(array(
                                            'element_id'=>$av['element_id'],
                                            'courser_id'=>$courser_id,
                                            'purchase_id'=>intval($purchase_id),
                                            'total'=>$eleMentAmountData[$ak]['use_total'],
                                            'back_total'=>$av['is_back_num'],
                                            'addtime'=>time()
                                        ));
                                    }
                                }
                            }
                        }

                    }else{ //不存在子级食谱
                        $is_exist_recipe = $recipeModel->getOne(array('recipe_name_id'=>$iv,'course_id'=>$courser_id)); //当前顶级食谱的数据是否存在
                        if($is_exist_recipe){ //存在具体的食谱时处理
                            $eleMentAmountData = $eleMentAmount->getAll(array('recipe_id'=>$is_exist_recipe['id']));
                            foreach($eleMentAmountData as $ak => $av){ //元素成份
                                $elementNum = $recipeElement->getOne(array('id'=>$av['element_id']));
                                if($is_exist_recipe['is_demo'] == 1){ //只有老师用量 + 回收量 （库存总含量 inventory_total 该食谱使用总量 use_total）
                                    $eleMentAmountData[$ak]['use_total'] = $av['teachers_use'];
                                }else{ //学员用量 + 老师用量 + 回收用量
                                    $eleMentAmountData[$ak]['use_total'] = $av['students_use']+$av['teachers_use'];
                                }
                                $eleMentAmountData[$ak]['inventory_total'] =$elementNum['inventory_num'];
                                $eleMentAmountData[$ak]['unit_id'] =$elementNum['element_unit'];
                                $is_exist_purchase_element = $purchaseNumModel
                                    ->checked_is_exist(
                                        array(
                                        'element_id'=>$av['element_id'],
                                        'courser_id'=>$courser_id,
                                            'purchase_id'=>intval($purchase_id)
                                    ));
                                if($is_exist_purchase_element !== false){ //已经存在 修改该数据
                                    $purchaseNumModel
                                        ->where(array('id'=>$is_exist_purchase_element['id'],'element_id'=>$av['element_id'],'courser_id'=>$courser_id))
                                        ->update(array(
                                            'total'=>$is_exist_purchase_element['total'] + $eleMentAmountData[$ak]['use_total'],
                                            'back_total'=>$is_exist_purchase_element['back_total'] + $av['is_back_num']
                                        ));
                                }else{ //不存在 新增该数据
                                    $purchaseNumModel->insertGetId(array(
                                        'element_id'=>$av['element_id'],
                                        'courser_id'=>$courser_id,
                                        'purchase_id'=>intval($purchase_id),
                                        'total'=>$eleMentAmountData[$ak]['use_total'],
                                        'back_total'=>$av['is_back_num'],
                                        'addtime'=>time()
                                    ));
                                }
                            }
                        }
                    }
                }
                $purchase = $purchaseModel->where(array('id'=>$purchase_id,'courser_id'=>$courser_id))->field('id,course_id,purchase_name,student_num,teacher_num')->find();
                $allElement = $purchaseNumModel->getAll(array('purchase_id'=>$purchase_id,'courser_id'=>$courser_id));
                foreach($allElement as $allk => $allv){
                        $purchaseNumModel
                            ->where(array(
                                'id'=>$allv['id'],
                                'purchase_id'=>$purchase['id']
                            ))
                            ->update(array(
                                'total'=>$allv['total']*(intval($purchase['student_num'])+intval($purchase['teacher_num']))
                            ));
                    $oneRecipeElement = $recipeElement->getOne(array('id'=>$allv['element_id']));

                    $recipeElement
                        ->where(array(
                            'id'=>$allv['element_id']))
                            ->update(array(
                                'inventory_num'=>($oneRecipeElement['inventory_num'] - $allv['total'])
                            ));
                }
                putMsg(1,url('recipe/purchase'));
            }catch (\Exception $e){
                putMsg(0,$e->getMessage());
            }
        }
        $this->assign([
            'course'=>$course
        ]);
        return $this->fetch();
    }
    /**采购 详情
     * @return mixed
     */
    public function editpurchase(){
        $id = input("param.");
        if(!$id){
            $this->error('系统错误');
        }
        $courseModel = new CourseModel();
        $course = $courseModel->getAll('','id,courser_name','id ASC');
        $purchaseModel = new PurchaseModel();
        $purchaseOne = $purchaseModel->getOne(array('id'=>$id['id'])); //当前的采购数据
        if(!$purchaseOne){
            $this->error('系统错误');
        }
        $recipeNameModel = new RecipeNameModel();
        $recipeModel = new RecipeModel();
        $allName = $recipeNameModel->where('id','in',$purchaseOne['recipe_ids'])->field('id,recipe_name,courser_id')->select();
        foreach($allName as $k => $v){
            $allName[$k]['is_exist_recipe'] = $recipeModel->getListCount(array('recipe_name_id'=>$v['id'],'course_id'=>$v['courser_id'])) == 1 ? true : false;
            $sonArr = $recipeNameModel->getAll(array('pid'=>$v['id'],'courser_id'=>$purchaseOne['courser_id']),'id,recipe_name,courser_id');
            foreach($sonArr as $sk => $sv){
                $sonArr[$sk]['is_exist_recipe'] = $recipeModel->getListCount(array('recipe_name_id'=>$sv['id'],'course_id'=>$sv['courser_id'])) == 1 ? true : false;
            }
            $allName[$k]['son'] = $sonArr;
        }



//        dump(objToArray($allName));
        $this->assign([
            'course'=>$course,
            'purchaseOne'=>$purchaseOne,
            'allName'=>$allName

        ]);
        return $this->fetch();
    }
    /**采购 删除
     * @return mixed
     */
    public function delpurchase(){

        return $this->fetch();
    }

    /**
     * 采购下载
     */
    public function downpurchase(){

        putMsg(1,'等待下载');
    }
    /**
     * 拼装采购操作按钮
     * @param $id
     * @return array
     */
    private function makePurchaseButton($id)
    {
        return [
            '详情' => [
                'auth' => 'recipe/editpurchase',
                'href' =>url('recipe/editpurchase', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-search'
            ],
            '删除' => [
                'auth' => 'recipe/delpurchase',
                'href' => "javascript:orderDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            '导出' => [
                'auth' => 'recipe/downpurchase',
                'href' => url('recipe/downpurchase', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-cloud-download'
            ],
        ];
    }
}