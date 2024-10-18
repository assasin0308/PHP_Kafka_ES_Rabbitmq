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

$redis->select(0);

# 1. String 简单字符串
$redis->set('name','zhangsan');


