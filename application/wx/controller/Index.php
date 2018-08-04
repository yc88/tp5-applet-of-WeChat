<?php
namespace app\wx\controller;

use app\wx\model\CourseModel;
use think\Controller;
use think\Db;

class Index extends Controller
{
//    小程序首页课程展示数据获取
    public function index()
    {   $banner =Db::table('fl_banner')->where(array('type'=>2,'is_show'=>1))->field('title,img_url')->order('id DESC')->select();
        foreach($banner as $bk => $bv ){
            $banner[$bk]['img_url'] = getImgName($bv['img_url'], 1200, 600); //首页课程展示图片
        }
        $courseModel = new CourseModel();
        $field = 'id,courser_name,buy_number,courser_price,courser_img';
//        甜点课程  classify_id = 1
        $where = array('classify_id' => 1);
        $dessert = $courseModel->getCourseList($where, $field);
        if (!empty($dessert)) {
            foreach ($dessert as $k => $v) {
                $img = explode(',', $v['courser_img']);
                if (is_array($img)) { //1200, 180)
                    $dessert[$k]['index_img'] = getImgName($img[0], 600, 600); //首页课程展示图片
                }
                $dessert[$k]['buy_number'] = $v['buy_number'] ? $v['buy_number'] : 1;
            }
        }
//        面包课程  classify_id = 2
        $map = array('classify_id' => 2);
        $bread = $courseModel->getCourseList($map, $field);
        if (!empty($bread)) {
            foreach ($bread as $k1 => $v1) {
                $img2 = explode(',', $v1['courser_img']);
                if (is_array($img2)) {
                    $bread[$k1]['index_img'] = getImgName($img2[0], 1200, 180); //首页课程展示图片
                }
                $bread[$k1]['buy_number'] = $v1['buy_number'] ? $v1['buy_number'] : 1;
            }
        }
        if (empty($dessert) && empty($bread) && empty($banner)) {
            putMsg(0, '系统错误1');
        }
        $arr = array(
            'banner'=>$banner,
            'dessert' => $dessert,
            'bread' => $bread
        );
        putMsg(1, $arr);
    }
}

