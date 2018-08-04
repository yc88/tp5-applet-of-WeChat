<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-25
 * Time: 9:20
 */
namespace app\wx\model;
class QuestionModel extends  CommonModel
{
    protected $name = 'question';

    /**根据条件获取最近的一条数据
     * @param array $where
     * @param null $field
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    public function getUpTime($where = array(),$field = null){
        $oneData = $this->where($where)->field($field)->find();
        if(!$oneData){
            return false;
        }
        return $oneData->getData();
    }

    /**获取数据 带分页
     * @param array $where
     * @param int $limit
     * @param null $field
     * @param string $order
     * @return array
     */
    public function getQuestionAll($where = array(),$limit = 10,$field = null,$order = 'id DESC'){
        $count = $this->where($where)->count();
        $list = $this->where($where)->field($field)->order($order)->paginate($limit,$count); //数据
        $page = $list->render(); //分页显示
        return array(
            'list'=>$list,
            'page'=>$page
        );
    }

}