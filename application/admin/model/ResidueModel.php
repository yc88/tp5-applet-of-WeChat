<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 2018/5/15
 * Time: 17:34
 */
namespace app\admin\model;
class ResidueModel extends CommonModel
{
    protected  $name = 'residue';

    /**根据订单号查询是否定金支付
     * @param $order_no
     * @return bool
     */
    public function cheked_is_full_pay($order_no){
        $result = $this->where(array('user_order_no'=>$order_no,'status'=>2))->find();
        return $result ? $result : false;
    }
}