<?php
/**
 * Comprehensive diagnostic script for the login system
 * Checks each component of the login pipeline
 */
header('Content-Type: application/json');

$diagnostics = [];

// 1. Check AMQP extension
$diagnostics['amqp'] = [
    'loaded' => extension_loaded('amqp'),
    'status' => extension_loaded('amqp') ? 'OK' : 'MISSING - Install php-amqp extension'
];

// 2. Check include files exist
$includes = [
    'path' => __DIR__ . '/../includes/path.inc',
    'get_host_info' => __DIR__ . '/../includes/get_host_info.inc',
    'rabbitMQLib' => __DIR__ . '/../includes/rabbitMQLib.inc'
];

$diagnostics['includes'] = [];
foreach ($includes as $name => $path) {
    $diagnostics['includes'][$name] = [
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0
    ];
}

// 3. Try to load includes
$diagnostics['include_loading'] = ['success' => true, 'errors' => []];
try {
    require_once __DIR__ . '/../includes/path.inc';
} catch (Throwable $e) {
    $diagnostics['include_loading']['success'] = false;
    $diagnostics['include_loading']['errors'][] = "path.inc: " . $e->getMessage();
}

try {
    require_once __DIR__ . '/../includes/get_host_info.inc';
} catch (Throwable $e) {
    $diagnostics['include_loading']['success'] = false;
    $diagnostics['include_loading']['errors'][] = "get_host_info.inc: " . $e->getMessage();
}

try {
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
} catch (Throwable $e) {
    $diagnostics['include_loading']['success'] = false;
    $diagnostics['include_loading']['errors'][] = "rabbitMQLib.inc: " . $e->getMessage();
}

// 4. Check config files
$configs = [
    'testRabbitMQ.ini' => __DIR__ . '/../config/testRabbitMQ.ini',
    'host.ini' => __DIR__ . '/../config/host.ini'
];

$diagnostics['config_files'] = [];
foreach ($configs as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $diagnostics['config_files'][$name] = [
        'exists' => $exists,
        'readable' => is_readable($path),
        'size' => $size,
        'empty' => ($size === 0)
    ];
}

// 5. Check logs directory
$log_dir = __DIR__ . '/../logs';
$diagnostics['logs'] = [
    'directory_exists' => is_dir($log_dir),
    'directory_writable' => is_writable($log_dir),
    'files' => []
];

if (is_dir($log_dir)) {
    $files = scandir($log_dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $path = $log_dir . '/' . $f;
            $diagnostics['logs']['files'][$f] = [
                'size' => filesize($path),
                'modified' => date('Y-m-d H:i:s', filemtime($path))
            ];
        }
    }
}

// 6. Try to create RabbitMQ client (if includes loaded)
if ($diagnostics['include_loading']['success']) {
    try {
        $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
        $diagnostics['rabbitMQ_client'] = [
            'created' => true,
            'broker_host' => $client->BROKER_HOST,
            'status' => 'OK'
        ];
    } catch (Throwable $e) {
        $diagnostics['rabbitMQ_client'] = [
            'created' => false,
            'error' => $e->getMessage(),
            'status' => 'FAILED'
        ];
    }
} else {
    $diagnostics['rabbitMQ_client'] = [
        'created' => false,
        'status' => 'SKIPPED - includes failed to load'
    ];
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
