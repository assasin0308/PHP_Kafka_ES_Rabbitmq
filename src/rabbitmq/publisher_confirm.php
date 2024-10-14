<?php
require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

// 声明队列
$channel->queue_declare('Acewill', false, true, false, false);

// 1.生产者消息确认
// publisher-confirm机制确认消息是否成功到达交换机。
// 启用pubslisher-confirm机制
$channel->confirm_select();
//设置确认回调
$channel->set_ack_handler(function ( AMQPMessage $message) {
    echo "Message Acknowledged: " . $message->getBody() . "\n";
    echo "消息已确认\n";
});
$channel->set_nack_handler(function(AMQPMessage $message){
    echo "Message Not Acknowledged: " . $message->getBody() . "\n";
    echo "消息未确认\n";
});

//发布消息
$message = new AMQPMessage('Hello, RabbitMQ!');
$channel->basic_publish($message, '', 'Acewill');

// 等待确认
$channel->wait_for_pending_acks(0);

//关闭连接
$channel->close();
$connection->close();