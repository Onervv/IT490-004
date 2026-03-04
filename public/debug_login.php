<?php
// Direct test of the login endpoint
echo "Testing login endpoint...\n";
echo "POST to login.php with test credentials\n";

$data = [
    'type' => 'login',
    'uname' => 'testuser',
    'pword' => 'testpass'
];

$ch = curl_init('http://localhost/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "Curl Error: $error\n";
}
echo "Response length: " . strlen($response) . " bytes\n";
echo "First 500 chars of response:\n";
echo substr($response, 0, 500) . "\n";

// Try to parse as JSON
if ($response) {
    $json = json_decode($response, true);
    if ($json) {
        echo "JSON parsed successfully:\n";
        print_r($json);
    } else {
        echo "Failed to parse response as JSON\n";
        echo "Response as-is: " . json_encode($response) . "\n";
    }
}
?>
