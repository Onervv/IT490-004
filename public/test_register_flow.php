<?php
/**
 * Test the registration flow
 * Simulates what the frontend register.js would do
 */
header('Content-Type: application/json');

// Check AMQP
if (!extension_loaded('amqp')) {
    echo json_encode(['error' => 'AMQP not installed']);
    exit;
}

// Load includes
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

// Create client
try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
    echo json_encode(['status' => 'success', 'message' => 'RabbitMQ client created', 'broker_host' => $client->BROKER_HOST]);
} catch (Throwable $e) {
    echo json_encode(['error' => 'Failed to create client: ' . $e->getMessage()]);
}
?>
