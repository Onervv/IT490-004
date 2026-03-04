<?php
// Write diagnostic info to a file that we can read
$output = [];

$output[] = "=== LOGIN.PHP DIAGNOSTIC ===";
$output[] = date('Y-m-d H:i:s');
$output[] = "";

// Check AMQP
$output[] = "AMQP Extension: " . (extension_loaded('amqp') ? 'YES' : 'NO');

// Try to load includes
$output[] = "\n=== Loading Includes ===";

try {
    require_once __DIR__ . '/../includes/path.inc';
    $output[] = "✓ path.inc loaded";
} catch (Throwable $e) {
    $output[] = "✗ path.inc failed: " . $e->getMessage();
    file_put_contents(__DIR__ . '/../logs/login_test.log', implode("\n", $output));
    exit;
}

try {
    require_once __DIR__ . '/../includes/get_host_info.inc';
    $output[] = "✓ get_host_info.inc loaded";
} catch (Throwable $e) {
    $output[] = "✗ get_host_info.inc failed: " . $e->getMessage();
    file_put_contents(__DIR__ . '/../logs/login_test.log', implode("\n", $output));
    exit;
}

try {
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
    $output[] = "✓ rabbitMQLib.inc loaded";
} catch (Throwable $e) {
    $output[] = "✗ rabbitMQLib.inc failed: " . $e->getMessage();
    file_put_contents(__DIR__ . '/../logs/login_test.log', implode("\n", $output));
    exit;
}

// Try to create rabbitMQClient
$output[] = "\n=== Creating RabbitMQ Client ===";
try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
    $output[] = "✓ rabbitMQClient created successfully";
    $output[] = "  BROKER_HOST: " . $client->BROKER_HOST;
} catch (Throwable $e) {
    $output[] = "✗ rabbitMQClient creation failed: " . $e->getMessage();
    file_put_contents(__DIR__ . '/../logs/login_test.log', implode("\n", $output));
    exit;
}

// Try to send a test request
$output[] = "\n=== Sending Test Request ===";
try {
    $request = [
        'type' => 'login',
        'username' => 'test',
        'password' => 'test',
        'message' => 'Diagnostic test'
    ];
    $output[] = "Sending: " . json_encode($request);
    
    $response = $client->send_request($request);
    $output[] = "✓ Received response: " . json_encode($response);
} catch (Throwable $e) {
    $output[] = "✗ send_request failed: " . $e->getMessage();
    $output[] = "  Type: " . get_class($e);
}

file_put_contents(__DIR__ . '/../logs/login_test.log', implode("\n", $output));
echo implode("\n", $output);
?>
