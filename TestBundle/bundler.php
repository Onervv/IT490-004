#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client2 = new rabbitMQClient("testRabbitMQ.ini", "testServer3");

if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "ok dude yea sure";
}

$request = array();
$request['message'] = "hello world";
$request['type'] = "bundle";

$output = shell_exec('/home/it490-vm/bundler.sh');

print_r($output);

$response = $client2->send_request($request);

echo "client sent response: ".PHP_EOL;

print_r($response);

echo "\n\n";

echo "listener END".PHP_EOL;
exit();
