<?php

// 使用rabbitmq rabbitmq_delayed_message_exchange 延迟队列插件 实现延迟任务
require_once  '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Wire\AMQPTable;

// 创建链接
$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', '123456','/');
$channel = $connection->channel();

$exchange = 'delayed_exchange';
$queue = 'delayed_queue';

// 定义交换机类型为 x-delayed-message
$arguments = new AMQPTable(['x-delayed-type' => AMQPExchangeType::DIRECT]);
$channel->exchange_declare($exchange, 'x-delayed-message', false, true, false, false, false, $arguments);

// 声明队列
$channel->queue_declare($queue, false, true, false, false);

// 绑定队列到交换机
$channel->queue_bind($queue, $exchange);


// 发送延迟消息

$orderId = 'order_123';
$delayInSeconds = 15 ;  // 15 minutes

$messageBody = json_encode(['order_id' => $orderId]);
$headers = new AMQPTable(['x-delay' => $delayInSeconds * 1000]); // 延迟时间以毫秒为单位

$message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
$message->set('application_headers', $headers);

$channel->basic_publish($message, $exchange);

echo date('Y-m-d H:i:s')." Order $orderId 发送延迟消息.\n";

/***********************************************************************************************************************/
// 处理延迟消息
$callback = function ($msg) {
    $orderData = json_decode($msg->body, true);
    $orderId = $orderData['order_id'];

    // 取消订单逻辑
    echo date('Y-m-d H:i:s').  " Order $orderId 因为超过时限未支付,取消!.\n";

};

$channel->basic_consume($queue, '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

// 关闭连接
$channel->close();
$connection->close();