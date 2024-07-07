<?php

require 'vendor/autoload.php';

$client = new GuzzleHttp\Client(['base_uri' => 'http://httpbin.org/']);
$response = $client->request('GET', 'get');

header('Content-Type: application/json');
echo $response->getBody();