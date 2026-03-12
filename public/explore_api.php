<?php
/**
 * explore_api.php
 * ---------------
 * GET endpoint that fetches paginated artist data via RabbitMQ.
 *
 * Query params:
 *   offset  (int, default 0)
 *   limit   (int, default 20, max 100)
 *   search  (string, optional keyword filter)
 *
 * Returns JSON: { status, artists[], total, offset, limit }
 */
header('Content-Type: application/json');

if (!extension_loaded('amqp')) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'AMQP extension not installed']);
    exit(0);
}

require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

$offset = max(0, intval($_GET['offset'] ?? 0));
$limit  = min(500, max(1, intval($_GET['limit']  ?? 20)));
$search = trim($_GET['search'] ?? '');

try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to initialize RabbitMQ client']);
    exit(0);
}

$request = [
    'type'   => 'get_artists',
    'offset' => $offset,
    'limit'  => $limit,
    'search' => $search
];

try {
    $response = $client->send_request($request);
    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'backend failure']);
}
exit(0);
