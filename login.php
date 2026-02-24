<?php
// Includes the template's RabbitMQ libraries
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//  Catch the POST request from your main.js fetch() call
if (!isset($_POST) || empty($_POST)) {
    $msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
    echo json_encode($msg);
    exit(0);
}

// Extract the variables sent from the frontend
error_log("login.php received POST: " . print_r($_POST, true));
$type     = $_POST['type'];
$username = $_POST['uname'];
$password = $_POST['pword'];

// default to the external host section
$rabbitSection = "testServer2"; 
$client = new rabbitMQClient("testRabbitMQ.ini", $rabbitSection);
error_log("login.php using broker section: $rabbitSection");

$request = [
    'type'     => $type,
    'username' => $username,
    'password' => $password,
    'message'  => 'Login request initiated from web frontend'
];
error_log("login.php prepared request for section $rabbitSection: " . json_encode($request));

try {
    // perform RPC and capture whatever the server returns
    $response = $client->send_request($request);
    // log the raw response for debugging
    error_log("login.php received response: " . json_encode($response));
    echo json_encode($response);
} catch (Exception $e) {
    error_log("login.php RPC failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
exit(0);
?>