<?php
require_once  '../vendor/autoload.php';

use Elasticsearch\ClientBuilder;

// Elasticsearch 服务器的地址和端口
$hosts = [  '127.0.0.1:9200' ];
$client = ClientBuilder::create()->setHosts($hosts)->build();

$params = [
    'index' => 'my_index',
    'id' => '1',
    'body' => [
        'title' => 'Test Document',
        'content' => 'This is a test document.'
    ]
];

$response = $client->index($params);
print_r($response);