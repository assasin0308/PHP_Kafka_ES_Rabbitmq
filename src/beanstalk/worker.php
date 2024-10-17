<?php
require '../vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1', 11300,30);

//设置管道名称
$pheanstalk->useTube('beansTube');

//消费任务 并处理: 从管道中去除任务,处理完毕后,删除任务
$pheanstalk->watch('beansTube');

//取出任务
$job = $pheanstalk->reserve();
//获取任务数据
$job_data = $job->getData();

//TODO 处理任务 30分钟未付款取消
$pheanstalk->delete($job);


print_r($job);
print_r($job_data);