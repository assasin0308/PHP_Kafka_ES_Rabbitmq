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



// 发布消息 模拟10000条消息堆积
//for($i=1;$i <= 10000; $i++){
//    $message = new AMQPMessage( date('Y-m-d H:i:s')." | 这是第 {$i} 条惰性队列消息! \n");
//    $channel->basic_publish($message, $exchange, $route_key);
//    echo date('Y-m-d H:i:s')." 这是第 {$i} 条惰性队列消息! \n ";
//}


/**********************************Consumer消费消息**********************************/

$callback = function ($msg) {
    echo  date('Y-m-d H:i:s'). ' 消费者收到: ', $msg->body, "\n";
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
