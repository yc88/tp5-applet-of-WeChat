<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-20
 * Time: 10:14
 */
namespace app\admin\model;
class InfoModel extends  CommonModel
{
    protected  $name='info';

    /**根据分类id查看其下面还有没有资讯的存在
     * @param $cate_id
     * @return int|string
     */
    public function checkedCateSon($cate_id){
        return $this->where('id',$cate_id)->count();
    }
}