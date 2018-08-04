<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 14:34
 */
namespace app\admin\validate;
use think\Validate;

class RoleValidate extends Validate
{
    protected  $rule = [
        ['role_name', 'require', '用户名不能为空'],
        ['role_name', 'unique:role', '角色已经存在'],
    ];
}