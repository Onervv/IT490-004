<?php
// Test script to validate includes can load
ob_start();
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    die(json_encode(['error' => $errstr, 'file' => $errfile, 'line' => $errline, 'errno' => $errno]));
});

header('Content-Type: application/json');

// Check AMQP first
$status = [];
if (!extension_loaded('amqp')) {
    $status['amqp_loaded'] = false;
    $status['amqp_detail'] = 'AMQP extension not loaded';
} else {
    $status['amqp_loaded'] = true;
}

try {
    require_once __DIR__ . '/../includes/path.inc';
    $status['path_inc'] = 'loaded';
} catch (Throwable $e) {
    ob_clean();
    die(json_encode(['error' => 'path.inc failed', 'detail' => $e->getMessage()]));
}

try {
    require_once __DIR__ . '/../includes/get_host_info.inc';
    $status['get_host_info_inc'] = 'loaded';
} catch (Throwable $e) {
    ob_clean();
    die(json_encode(['error' => 'get_host_info.inc failed', 'detail' => $e->getMessage()]));
}

try {
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
    $status['rabbitMQLib_inc'] = 'loaded';
} catch (Throwable $e) {
    ob_clean();
    die(json_encode(['error' => 'rabbitMQLib.inc failed', 'detail' => $e->getMessage()]));
}

ob_clean();
$status['message'] = 'All includes loaded successfully';
echo json_encode(['status' => 'success', 'details' => $status]);
?>
