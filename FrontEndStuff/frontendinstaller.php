#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client2 = new rabbitMQClient("testRabbitMQ.ini", "testServer3");

$request = array();
$request['type'] = "frontendinstaller";

$response = $client2->send_request($request);

$safe_version = escapeshellarg($response);

$output = shell_exec("/home/it490-vm/DeployStuff/IT490-004/FrontEndStuff/frontendinstall.sh $safe_version");

echo "client sent response: ".PHP_EOL;

echo "\n\n";

echo "listener END".PHP_EOL;
exit();
