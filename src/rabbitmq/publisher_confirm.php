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
// 1.生产者消息确认
// publisher-confirm机制确认消息是否成功到达交换机。
// 启用pubslisher-confirm机制
$channel->confirm_select();
//设置确认回调
$channel->set_ack_handler(function ( AMQPMessage $message) {
//    print_r($message);
    echo "消息已确认  Message Acknowledged: " . $message->getBody() . "\n";

});
$channel->set_nack_handler(function(AMQPMessage $message){
    echo "消息未确认 Message Not Acknowledged: " . $message->getBody() . "\n";

});

//发布消息 非持久化消息
//$message = new AMQPMessage('Hello, RabbitMQ2 ------- publisher_confirm!');
//发布消息 持久化消息
$message = new AMQPMessage(
    'Hello, RabbitMQ this is 持久化消息 !', [
        'content_type' => 'text/plain',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        'mandatory' => true, // 启用return机制
    ]
);
$channel->basic_publish($message, $exchangeName, $route_key);

// 等待确认
$channel->wait_for_pending_acks(0);

//关闭连接
$channel->close();
$connection->close();