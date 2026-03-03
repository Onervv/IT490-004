<?php
// Includes the template's RabbitMQ libraries
// includes now live in ../includes
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

// always return JSON
header('Content-Type: application/json');

//  Catch the POST request from your main.js fetch() call
if (!isset($_POST) || empty($_POST)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'no POST data','detail'=>'POST array is empty or not set']);
    exit(0);
}

// Extract the variables sent from the frontend
error_log("login.php received POST: " . print_r($_POST, true));
// expected fields are 'type', 'uname', 'pword'
$type     = isset($_POST['type']) ? $_POST['type'] : null;
$username = isset($_POST['uname']) ? $_POST['uname'] : null;
$password = isset($_POST['pword']) ? $_POST['pword'] : null;

// validate required fields
if (!$type || !$username || !$password) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'missing credentials','detail'=>"type=$type, uname=$username, pword not shown"]);
    exit(0);
}

// Note: the RabbitMQ credentials stored in testRabbitMQ.ini are used
// to authenticate PHP to the broker itself.  They have NOTHING to do with
// application users; the username/password above are passed in the JSON
// payload and may later be checked against a database by the worker.

// default to the external host section
$rabbitSection = "testServer2"; 
// config files moved to ../config
try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', $rabbitSection);
    error_log("login.php using broker section: $rabbitSection");
} catch (Exception $e) {
    error_log("login.php failed to construct rabbitMQClient: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to initialize RabbitMQ client','detail'=>$e->getMessage()]);
    exit(0);
}

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

    // append the response to a local log file and keep it to 50 lines max
    // keep logs outside the web root
    $logfile = __DIR__ . '/../logs/rabbit_responses.log';
    $entry = date('c') . ' ' . json_encode($response);
    file_put_contents($logfile, $entry . PHP_EOL, FILE_APPEND);
    // trim file if it has grown too large
    $lines = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lines) > 50) {
        $keep = array_slice($lines, -50);
        file_put_contents($logfile, implode(PHP_EOL, $keep) . PHP_EOL);
    }

    echo json_encode($response);
} catch (Throwable $e) {
    // catch Error as well as Exception
    error_log("login.php RPC failed: " . $e->getMessage());
    http_response_code(500);
    // include exception detail in JSON for development (remove in production)
    echo json_encode(['status'=>'error','message'=>'backend failure','detail'=>$e->getMessage()]);
}
exit(0);
?>