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
$job_id = $pheanstalk->put(
    json_encode($order,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR), //消息体
    0, //优先级 数值越小,优先级越高
    10, //延迟时间 0表示立即执行,单位秒,延迟让消费者去消费
    60 //超时时间 任务执行一段时间后没有执行完毕,则重新入队
);

//查看单个任务状态
//$job = $pheanstalk->peek($job_id); //获取单个任务
//print_r($pheanstalk->statsJob($job_id));

//查看整个管道的任务状态
print_r($pheanstalk->statsTube('beansTube'));





print_r($pheanstalk_id);

echo date('Y-m-d H:i:s'). ' | 生产者: 投送消息成功!' . PHP_EOL;



// 检查连接是否成功

