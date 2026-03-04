<?php
// Quick test to verify rabbitMQLib syntax
try {
    require 'includes/rabbitMQLib.inc';
    echo "✓ rabbitMQLib.inc loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
