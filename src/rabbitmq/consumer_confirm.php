<?php

require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

$exchangeName = 'Acewill';
$queueName = 'hongzhuangyuan';
$route_key = 'canxingjian';

//声明交换机
$channel->exchange_declare($exchangeName, 'direct', false, true, false);
// 声明队列
$channel->queue_declare($queueName, false, true, false, false);
$channel->queue_bind($queueName, $exchangeName, $route_key);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// 回调函数，处理接收到的消息
$callback = function (AMQPMessage $msg) {
    try {
        // 模拟消息处理逻辑 异常情况
//        $data = json_decode($msg->getBody(), true);
//        if (!isset($data['valid'])) {
//            throw new Exception("Invalid message format");
//        }

        echo ' [消费者:] 收到消息:  ', $msg->body, "\n";

        // 模拟消息处理
        sleep(3);

        // 手动确认消息
        $msg->ack();

    } catch (Exception $e) {
        // 捕获异常，不发送确认，使消息重新入队列
        echo " [消费者:] 发生错误: ", $e->getMessage(), "\n";
        $msg->nack(true); // 重新入队列
    }






    echo " [消费者:] 消息已被处理!\n";
};

// 设置消费者，启用消息确认机制
$channel->basic_consume($queueName, '', false, false, false, false, $callback);


// 等待消息
while ($channel->is_consuming()) {
    $channel->wait();
}

// 关闭连接
$channel->close();
$connection->close();