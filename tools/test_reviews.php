#!/usr/bin/php
<?php
/**
 * End-to-end test for the reviews feature via RabbitMQ.
 * Runs from the frontend VM, sends messages to the DB VM (100.88.147.61 via Tailscale).
 *
 * Tests: register → login → create_review → get_my_reviews → get_all_reviews → delete_review
 */

require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

$client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');

$testUser = 'reviewtester_' . rand(1000, 9999);
$testPass = 'testpass123';

echo "=== REVIEWS E2E TEST ===\n";
echo "Test user: $testUser\n\n";

// ──  Register ──────────────────────────────────────
echo "1) Register... ";
$res = $client->send_request([
  'type'     => 'register',
  'username' => $testUser,
  'password' => $testPass,
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
print_r($res);
if ($res['status'] !== 'ok') { echo "Cannot continue without registration.\n"; exit(1); }

// ── Login ─────────────────────────────────────────
echo "\n2) Login... ";
$res = $client->send_request([
  'type'     => 'login',
  'username' => $testUser,
  'password' => $testPass,
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
print_r($res);
if ($res['status'] !== 'ok') { echo "Cannot continue without login.\n"; exit(1); }
$sessionKey = $res['session_key'];
echo "Session key obtained.\n";

// ──  Create a review ──────────────────────────────
echo "\n3) Create review... ";
$res = $client->send_request([
  'type'        => 'create_review',
  'session_key' => $sessionKey,
  'subject'     => 'Radiohead',
  'rating'      => 5,
  'review_text' => 'One of the greatest bands of all time!',
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
print_r($res);
$reviewId = $res['review_id'] ?? 0;

// ──  Create a second review ───────────────────────
echo "\n4) Create second review... ";
$res = $client->send_request([
  'type'        => 'create_review',
  'session_key' => $sessionKey,
  'subject'     => 'Kendrick Lamar',
  'rating'      => 4,
  'review_text' => 'DAMN was a masterpiece.',
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
print_r($res);

// ──  Get my reviews ───────────────────────────────
echo "\n5) Get my reviews... ";
$res = $client->send_request([
  'type'        => 'get_my_reviews',
  'session_key' => $sessionKey,
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
echo "Found " . count($res['reviews'] ?? []) . " review(s).\n";
print_r($res);

// ──  Get all reviews ──────────────────────────────
echo "\n6) Get all reviews... ";
$res = $client->send_request([
  'type'   => 'get_all_reviews',
  'search' => '',
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
echo "Total reviews in DB: " . count($res['reviews'] ?? []) . "\n";

// ──  Get all reviews with search filter ───────────
echo "\n7) Search reviews for 'Radiohead'... ";
$res = $client->send_request([
  'type'   => 'get_all_reviews',
  'search' => 'Radiohead',
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
echo "Matching reviews: " . count($res['reviews'] ?? []) . "\n";

// ──  Delete the first review ──────────────────────
echo "\n8) Delete review (id=$reviewId)... ";
$res = $client->send_request([
  'type'        => 'delete_review',
  'session_key' => $sessionKey,
  'review_id'   => $reviewId,
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
print_r($res);

// ──  Confirm deletion ─────────────────────────────
echo "\n9) Confirm deletion (get_my_reviews)... ";
$res = $client->send_request([
  'type'        => 'get_my_reviews',
  'session_key' => $sessionKey,
]);
echo ($res['status'] === 'ok' ? 'PASS' : 'FAIL') . "\n";
echo "Remaining reviews: " . count($res['reviews'] ?? []) . "\n";

// ──  Edge case: create review without auth ───────
echo "\n10) Create review with bad session key (should fail)... ";
$res = $client->send_request([
  'type'        => 'create_review',
  'session_key' => 'invalid_key_12345',
  'subject'     => 'Hacker',
  'rating'      => 1,
  'review_text' => 'Should not work',
]);
echo ($res['status'] === 'error' ? 'PASS (correctly rejected)' : 'FAIL (should have been rejected)') . "\n";
print_r($res);

echo "\n=== ALL TESTS COMPLETE ===\n";
