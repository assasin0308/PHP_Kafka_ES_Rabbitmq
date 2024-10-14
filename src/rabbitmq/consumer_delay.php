<?php

require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/*
 * 死信交换机可以用于实现消息的延迟投递,例如,当用户下单后,需要等待30分钟才能支付,如果用户在30分钟内没有支付,那么该订单将会被取消,此时可以将订单信息投递到死信交换机中,
    等待30分钟后,消费者从死信交换机中获取订单信息,并进行处理.
 * */

// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

// 定义交换机和队列
$exchange = 'delayed_exchange';
$queue = 'delayed_queue';
$route_key = 'delayed_route_key';

$dlx_exchange = 'dlx_exchange'; //死信交换机
$dlx_queue = 'dlx_queue'; //死信队列
$dlx_route_key = 'dlx_route_key'; //死信路由键

// 声明死信交换机和死信队列
$channel->exchange_declare($dlx_exchange, 'direct', false, true, false);
$channel->queue_declare($dlx_queue, false, true, false, false);
$channel->queue_bind($dlx_queue, $dlx_exchange,$dlx_route_key);

// 声明原始队列，并设置死信交换机
$args = new AMQPTable([
    'x-dead-letter-exchange' => $dlx_exchange,
    'x-dead-letter-routing-key' => $dlx_route_key,
    'x-message-ttl' => 10000 // 设置消息过期时间为10秒,单位为毫秒
]);
$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_declare($queue, false, true, false, false, false, $args);
$channel->queue_bind($queue, $exchange, $route_key);


// 发送消息，设置过期时间为10秒
$messageBody = [
    'order_id' => '12345',
    'user_id' => '67890',
    'amount' => 100.00
];
$properties = [
   'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    'content_type' => 'text/plain',
];
$message = new AMQPMessage(json_encode($messageBody), $properties);
$channel->basic_publish($message, '', $queue);

echo " [生产者:] 发送延迟消息!\n";


// 消费死信队列中的消息
$callback = function ($msg) {
    $orderData = json_decode($msg->body, true);
    echo ' [消费者:] 收到延迟消息 ', $msg->body, "\n";
    // 处理订单取消逻辑
    echo "Order ID: " . $orderData['order_id'] . " has been cancelled.\n";

};

$channel->basic_consume($dlx_queue, '', false, true, false, false, $callback);


while ($channel->is_consuming()) {
    $channel->wait();
}

// 关闭连接
$channel->close();
$connection->close();
