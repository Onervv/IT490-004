<?php
// helper files moved under ../includes
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';


// choose section via first argument or default to remote broker
$rabbitSection = isset($argv[3]) ? $argv[3] : "testServer2";
// config relocated
$client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', $rabbitSection);

// Get username and password from arguments
$username = isset($argv[1]) ? $argv[1] : "testuser";
$password = isset($argv[2]) ? $argv[2] : "testpass123";

$request = array();
$request['type'] = "register";
$request['username'] = $username;
$request['password'] = $password;
$request['message'] = "Register request from RPC CLI";

error_log("rpc/registerRequest sending: " . json_encode($request));

// send_request waits for a reply from the worker
$response = $client->send_request($request);

if (isset($response)) {
    echo json_encode(array('status'=>'sent','response'=>$response), JSON_PRETTY_PRINT);
} else {
    echo json_encode(array('status'=>'failed'));
}
