<?php
/**
 * Logout handler - invalidates session in database via RabbitMQ
 */
header('Content-Type: application/json');

$sessionKey = $_POST['session_key'] ?? '';

if (!empty($sessionKey)) {
    require_once __DIR__ . '/../includes/path.inc';
    require_once __DIR__ . '/../includes/get_host_info.inc';
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
    
    try {
        $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
        $request = [
            'type' => 'logout',
            'session_key' => $sessionKey
        ];
        $client->send_request($request);
    } catch (Exception $e) {
        // Ignore errors - we're logging out anyway
    }
}

echo json_encode(['status' => 'ok']);
exit(0);
?>
