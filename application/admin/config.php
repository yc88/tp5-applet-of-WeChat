<?php
//配置文件
return [
    // 模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/static/admin/css',
        '__JS__'     => '/static/admin/js',
        '__IMG__' => '/static/admin/images',
        '__layer__' => '/static/layer', //layer js路径
        '__NoImg__' => '/static/noimg.jpg', //默认没有图片显现
        '__layui__' =>'/static/admin/js/layui'
    ),
    'Host'=>'www.floertp.com',
    'img_url'=>'/static/',
    'fileName_save'=>'static/excel/',
    // 管理员状态 用户状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁用'
    ],
    // 角色状态
    'role_status' => [
        '1' => '启用',
        '2' => '禁用'
    ],
];