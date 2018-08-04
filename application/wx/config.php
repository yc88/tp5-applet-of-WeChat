<?php
//配置文件
return [
    'Host'=>'www.floertp.com',
    'img_url'=>'/static',
    //微信配置参数
    'weixin'=>array(
        'appId' =>'wx394b2cda86f622c5',			//微信appid
        'secret'=>'08fa69411f39fa7ced83f0e9aeab16af', //微信secret
        'mchId' => '1503442261',
        'key' =>'qwert123456asdfg456789florevp852', //商户秘钥
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://wx.florevp.com/wx/Weipay/wei_xin_notify',
        'SSLCERT_PATH' => 'data/apiclient_cert.pem',
        'SSLKEY_PATH' => 'data/apiclient_key.pem',
        'REPORT_LEVENL' => 2 //全量上报
    ),
    'log'=>array(
        'type' => 'File',
        'level' => ['log'],
        // error和sql日志单独记录
        'path'=>LOG_PATH.'/weixin/',
    ),
];