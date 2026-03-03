<?php
// helper files moved under ../includes
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';


// choose section via first argument or default to remote broker
$rabbitSection = isset($argv[2]) ? $argv[2] : "testServer2";
// config relocated
$client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', $rabbitSection);
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "Login";
$request['username'] = "steve";
$request['password'] = "password";
$request['message'] = $msg;
error_log("rpc/loginRequest sending: " . json_encode($request));
// by default we publish and do not wait for reply; send request is for waiting for a reply  
$response = $client->send_request($request);
// $response = $client->publish($request);

if (isset($response)) {
    echo json_encode(array('status'=>'sent','response'=>$response));
} else {
    echo json_encode(array('status'=>'failed'));
}
