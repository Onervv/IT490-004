<?php
/**
 * reviews_api.php
 * ---------------
 * Proxy endpoint that forwards review requests to the DB VM via RabbitMQ.
 * All review data is stored on the remote DB VM — nothing is saved locally.
 *
 * POST params:
 *   action      – create_review | get_my_reviews | get_all_reviews | delete_review
 *   session_key – current user session
 *   (+ action-specific fields)
 *
 * Returns JSON from the DB VM server.
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit(0);
}

require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

$action     = $_POST['action']      ?? '';
$sessionKey = $_POST['session_key']  ?? '';

if (empty($sessionKey)) {
    echo json_encode(['status' => 'error', 'message' => 'not authenticated']);
    exit(0);
}

// Connect to the remote RabbitMQ broker (testServer2 → DB VM)
try {
    $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to RabbitMQ']);
    exit(0);
}

switch ($action) {

    case 'create_review':
        $request = [
            'type'        => 'create_review',
            'session_key' => $sessionKey,
            'subject'     => trim($_POST['subject']     ?? ''),
            'rating'      => intval($_POST['rating']    ?? 0),
            'review_text' => trim($_POST['review_text'] ?? '')
        ];
        break;

    case 'get_my_reviews':
        $request = [
            'type'        => 'get_my_reviews',
            'session_key' => $sessionKey
        ];
        break;

    case 'get_all_reviews':
        $request = [
            'type'        => 'get_all_reviews',
            'session_key' => $sessionKey,
            'search'      => trim($_POST['search'] ?? '')
        ];
        break;

    case 'delete_review':
        $request = [
            'type'        => 'delete_review',
            'session_key' => $sessionKey,
            'review_id'   => intval($_POST['review_id'] ?? 0)
        ];
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'unknown action']);
        exit(0);
}

// Send request through RabbitMQ to the DB VM and wait for the response
try {
    $response = $client->send_request($request);
    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'backend failure']);
}
exit(0);
