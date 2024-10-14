<?php
require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

$exchangeName = 'Acewill';
$queueName = 'hongzhuangyuan';
$routing_key = 'canxingjian';
//声明交换机
$channel->exchange_declare($exchangeName, 'direct', false, true, false);
// 声明队列
$channel->queue_declare($queueName, false, true, false, false);
$channel->queue_bind($queueName, $exchangeName, $routing_key);



//启用publisher-return
$channel->set_return_listener(function ($reply_code, $reply_text, $exchange, $routing_key, AMQPMessage $message) {
    echo "Message returned: " . $message->getBody() . "\n";
    echo "Reply Code: " . $reply_code . "\n";
    echo "Reply Text: " . $reply_text . "\n";
    echo "Exchange: " . $exchange . "\n";
    echo "Routing Key: " . $routing_key . "\n";
});

// 发布消息，并设置mandatory标志
$message = new AMQPMessage(
    'Hello, RabbitMQ publisher_return !', [
        'content_type' => 'text/plain',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        'mandatory' => true, // 启用return机制
    ]
);
$channel->basic_publish($message, $exchangeName, $routing_key, true);
//等待确认
$channel->wait_for_pending_acks_returns(0);
echo "Message sent.\n";

// 关闭连接
$channel->close();
$connection->close();