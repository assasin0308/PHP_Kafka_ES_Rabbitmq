<?php

require '../vendor/autoload.php';

use Predis\Client;

//$redis = new Client();
//$redis->connect('127.0.0.1',6379);

$redis = new Client(
    [
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0
    ]
);
print_r($redis);

$redis->select(0);

# 1. String 简单字符串
$redis->set('name','zhangsan');

// 模拟投递任务

for($i=0;$i< 10000;$i++){
    print_r($redis->lpush('order_task','task'.$i)).PHP_EOL;
}
