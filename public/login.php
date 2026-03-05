<?php
header('Content-Type: application/json');

if (!extension_loaded('amqp')) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'AMQP extension not installed']);
    exit(0);
}

require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

if (!isset($_POST) || empty($_POST)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'no POST data']);
    exit(0);
}

$type     = $_POST['type'] ?? null;
$username = $_POST['uname'] ?? null;
$password = $_POST['pword'] ?? null;

if (!$type || !$username || !$password) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'missing credentials']);
    exit(0);
}

try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to initialize RabbitMQ client']);
    exit(0);
}

$request = [
    'type'     => $type,
    'username' => $username,
    'password' => $password
];

try {
    $response = $client->send_request($request);
    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'backend failure']);
}
exit(0);
?>