<?php
require '../vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1', 11300,30);

//设置管道名称
$pheanstalk->useTube('beansTube');

//消费任务 并处理: 从管道中去除任务,处理完毕后,删除任务
$pheanstalk->watch('beansTube');

while (1) {
    //取出任务
    $job = $pheanstalk->reserve();
//获取任务数据
    $job_data = $job->getData();
//TODO 处理任务 如30分钟未付款取消

    //处理任务完毕后, 删除任务
    $pheanstalk->delete($job);

    echo $job_data."\n";

    sleep(0.6);
    echo date('Y-m-d H:i:s'). ' | 消费者: 消息消费成功!' . PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;
}


