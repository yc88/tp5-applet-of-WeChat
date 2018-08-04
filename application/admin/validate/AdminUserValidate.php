<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-13
 * Time: 14:34
 */
namespace app\admin\validate;
use think\Validate;

class AdminUserValidate extends Validate
{
    protected  $rule = [
        ['userName', 'require', '用户名不能为空'],
        ['password', 'require', '密码不能为空'],
        ['code', 'require', '验证码不能为空']
    ];
}