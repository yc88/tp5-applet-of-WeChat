<?php
//配置文件
return [
    'template'  =>  [
        'layout_on'     =>  true,
        'layout_name'   =>  'layout',
    ],
    // 模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/static/home/css',
        '__JS__'     => '/static/home/js',
        '__IMG__' => '/static/home/img',
        '__BS__' =>'/static/home/bootstrap-3.3.7',
        '__layer__' => '/static/layer', //layer js路径
        '__NoImg__' => '/static/noimg.jpg', //默认没有图片显现
    ),
];