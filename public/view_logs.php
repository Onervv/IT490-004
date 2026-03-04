<?php
// Show recent PHP error log entries related to login
$log_files = [
    '/var/log/php-fpm/error.log',
    '/var/log/php-fpm/www-error.log',
    '/var/log/php.log',
    '/var/log/apache2/error.log',
];

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>PHP Error Log</title></head><body>";
echo "<h1>PHP Error Log Viewer</h1>";

foreach ($log_files as $file) {
    if (file_exists($file)) {
        echo "<h2>$file</h2>";
        $contents = file_get_contents($file);
        $lines = explode("\n", $contents);
        // Get last 50 lines and filter for "login" or recent errors
        $recent = array_slice($lines, -100);
        $filtered = array_filter($recent, function($line) {
            return stripos($line, 'login') !== false || 
                   stripos($line, 'amqp') !== false ||
                   stripos($line, 'error') !== false ||
                   stripos($line, 'warning') !== false;
        });
        echo "<pre style='background:#f0f0f0;padding:10px;'>";
        echo htmlspecialchars(implode("\n", array_slice($filtered, -50)));
        echo "</pre>";
    }
}

echo "</body></html>";
?>
