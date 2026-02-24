<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


// choose section via first argument or default to remote broker
$rabbitSection = isset($argv[2]) ? $argv[2] : "testServer2";
$client = new rabbitMQClient("testRabbitMQ.ini", $rabbitSection);
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
// by default we publish and do not wait for reply; uncomment to block
// $response = $client->send_request($request);
$response = $client->publish($request);

if (isset($response)) {
    echo json_encode(array('status'=>'sent','response'=>$response));
} else {
    echo json_encode(array('status'=>'failed'));
}
