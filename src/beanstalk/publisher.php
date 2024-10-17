<?php
require '../vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1', 11300,30);

//设置管道名称
$pheanstalk->useTube('beansTube');

// 模拟保存订单数据
$order = [
    'order_id' => uniqid(),
    'order_time' => date('Y-m-d H:i:s'),
    'order_status' => 'pending',
    'remarks' => '这是一个测试订单数据'
];

//投送消息 到管道
$pheanstalk_id = $pheanstalk->put(
    json_encode($order,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
);

print_r($pheanstalk_id);

//echo date('Y-m-d H:i:s'). ' | 生产者: 投送消息成功!' . PHP_EOL;



// 检查连接是否成功

