# PHP_Kafka_ES_Rabbitmq
php消息中间件Kafka/ES/RabbitMQ


**常用的 Docker 操作命令：**
```shell
# 启动并进入项目中的服务容器，--rm 表示退出容器后自动删除容器
docker-compose run --rm php-cli bash

#创建Laravel项目
composer create-project --prefer-dist laravel/laravel PHP_Kafka_ES_RabbitMQ

# kafka PHP客户端安装
composer require nmred/kafka-php

# rabbitmq PHP客户端安装 ^2.10
composer require php-amqplib/php-amqplib --ignore-platform-req=ext-sockets

```

![img_1.png](img_1.png)
```txt
RabbitMQ常见重点问题

1. 消息的可靠性问题,如何确保发送的消息至少被消费一次 ? 
    * 生产者消息确认 
        > publisher-confirm 发送者确认消息是否投递到交换机 成功返回ack,失败返回nack; 
        > publisher-return  发送者回执,消息投递到交换机了,但是没有路由到队列,返回ack及路由失败原因;
        !! 确认消息发送机制时,需要给每一个消息设置一个全局唯一id,以区分不同消息,避免ack冲突
    
    * 消息持久化
        delivery_mode => AMQPMessage::DELIVERY_MODE_PERSISTENT
    
    * 消费者消息确认
        消费者处理消息后可以向MQ发送ack回执,MQ收到ack回执后才会删除消息,否则会重新投递消息给消费者
        
    * 消费者失败重试机制
        重试机制耗尽后,直接reject,丢弃消息.
        重试机制耗尽后,返回nack,消息消息重新入队列
        重试机制耗尽后,将失败消息投递至指定的交换机
        
2. 延迟消息问题,如何实现消息的延迟投递 ? -> 死信交换机
    当一个队列中的消息满足以下情况之一时,可以称为死信:
        1. 消费者basic.reject或者basic.nack声明消息消费失败,并且requeue=false
        2. 该消息是一个过期消息,超时无人消费
        3. 该消息被投递到队列中,队列中的消息数量已经超过最大队列长度
    如果该队列配置了dead-letter-exchange参数,指定了一个交换机,那么队列中的死信将会投递到该参数所指定的交换机中,该交换机被称为死信交换机,死信交换机中的消息被称为死信消息.
        
       延迟队列插件 rabbitmq-plugins enable rabbitmq_delayed_message_exchange 
          实现延迟订单,预约等场景问题
      
  
3. 消息堆积问题,如何解决百万消息积压,无法及时消费的问题 ?
    消息堆积问题 -> 惰性队列
      增加消费者,提高消费速度
      在消费者内开启多进程,多线程加快消息处理速度
      扩大队列容积,提高消息堆积上限
    
    惰性队列的特点
        接收到消息后直接存入磁盘而非内存
        消费者消费消息时才会从磁盘中读取中加载到内存
        支持数百万条的消息存储
        
    设置一个队列为惰性队列时,只要在声明队列时,指定x-queue-mode参数为lazy即可。
    也可以通过命令将一个运行中的队列修改为惰性队列
    rabbitmqctl set_policy Lazy "^lazy-queue$" '{"queue-mode":"lazy"}' --apply-to queues
    
4. 高可用问题,如何解决单点MQ故障而导致的不可用问题 ? 
    MQ集群:
        普通集群：分布式集群，将队列分散到集群的各个节点，从而提高整个集群的并发能力
            * 会在集群的各个节点共享部分数据,包括交换机,队列元信息.但不包括队列中的消息
            * 当访问集群某个节点时,如果队列不在该节点,会从数据所在节点传递到当前节点并返回
            * 队列所在的节点宕机,队列中的消息就会丢失
           
           需要在 rabbit2 和 rabbit3 容器中分别执行以上命令，将它们加入到 rabbit1 的集群中
               rabbitmqctl stop_app
               rabbitmqctl join_cluster rabbit@rabbit1   
                    示例: rabbitmqctl join_cluster rabbit@主节点主机名      节点2终端执行
                          rabbitmqctl join_cluster rabbit@主节点主机名     节点3终端执行
               rabbitmqctl start_app

            
        镜像集群：主从集群,普通集群的基础上,添加了备份功能,提高集群的数据可用性 
            * 交换机,队列,队列中的消息会在各个mq的镜像节点之间同步备份
            * 创建队列的节点称为该队列的主节点,备份到的其他节点叫做该队列的镜像节点
            rabbitmqctl set_policy ha-all "^two\." '{"ha-mode":"exactly","ha-params":2,"ha-sync-mode":"automatic"}' 
        
        仲裁队列：Raft协议确保主从数据的一致性
            * 与镜像队列一样,都是主从模式,支持主从数据同步
            * 使用简单,没有复杂的配置  队列类型必须是 Quorum
            * 主从同步基于Raft协议,强一致











