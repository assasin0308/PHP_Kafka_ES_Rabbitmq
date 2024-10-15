<?php
require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

$exchange = 'lazy_exchange';
$queue = 'lazy_queue';
$route_key = 'lazy_route_key';

// 声明惰性队列
$channel->exchange_declare($exchange, 'direct', false, true, false);
$args = new AMQPTable( [ 'x-queue-mode' => 'lazy']);
$channel->queue_declare($queue, false, true, false, false,false,$args);
$channel->queue_bind($queue, $exchange,$route_key);



// 发布消息
$message = new AMQPMessage( date('Y-m-d H:i:s').' 生产者: Hello, Lazy Queue!');
$channel->basic_publish($message, $exchange, $route_key);

/********************************************************************/

$callback = function ($msg) {
    echo  date('Y-m-d H:i:s'). ' 消费者:  Received: ', $msg->body, "\n";
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
