<?php
// Simple AMQP test
$status = [];

$status['amqp_loaded'] = extension_loaded('amqp');
$status['amqp_loaded_extensions'] = extension_loaded('amqp');

// Check loaded extensions for anything AMQP-related
$extensions = get_loaded_extensions();
$amqp_related = array_filter($extensions, function($e) {
    return stripos($e, 'amqp') !== false || stripos($e, 'rabbit') !== false;
});

$status['amqp_related_extensions'] = $amqp_related;
$status['total_extensions'] = count($extensions);
$status['php_version'] = phpversion();

header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT);
?>
