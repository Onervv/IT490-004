#!/usr/bin/php
<?php

// simulated RabbitMQ server for testing login requests; run with `php testRabbitMQServer.php`
// includes now consolidated
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

function getDBConnection()
{
  $host = '127.0.0.1';
  $user = 'testUser';
  $pass = '12345';
  $db = 'testdb';
  
  $conn = new mysqli($host, $user, $pass, $db);
  if ($conn->connect_error) {
    return null;
  }
  return $conn;
}

function doLogin($username, $password)
{
  if (empty($username) || empty($password)) {
    return array('status' => 'error', 'message' => 'username or password empty');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $stmt = $db->prepare('SELECT userid, password_hash FROM users WHERE username = ? LIMIT 1');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('s', $username);
  if (!$stmt->execute()) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    return array('status' => 'error', 'message' => 'username not found');
  }

  $row = $result->fetch_assoc();
  if (!password_verify($password, $row['password_hash'])) {
    return array('status' => 'error', 'message' => 'invalid credentials');
  }

  // Generate session key and store in database
  $sessionKey = bin2hex(random_bytes(32));
  $sessionKeyHash = hash('sha256', $sessionKey);
  $userId = $row['userid'];
  $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

  // Delete any existing sessions for this user
  $deleteStmt = $db->prepare('DELETE FROM sessions WHERE userid = ?');
  $deleteStmt->bind_param('i', $userId);
  $deleteStmt->execute();

  // Insert new session
  $insertStmt = $db->prepare('INSERT INTO sessions (userid, sessionkey_hash, expires_at) VALUES (?, ?, ?)');
  if (!$insertStmt) {
    return array('status' => 'error', 'message' => 'database error');
  }
  $insertStmt->bind_param('iss', $userId, $sessionKeyHash, $expiresAt);
  if (!$insertStmt->execute()) {
    return array('status' => 'error', 'message' => 'failed to create session');
  }

  return array('status' => 'ok', 'user_id' => $userId, 'session_key' => $sessionKey, 'username' => $username);
}

/**
 * Register a new user.
 * Returns array('status'=>'ok') on success or
 * array('status'=>'fail','message'=>...) on failure.
 */
function doRegister($username, $password)
{
  $db = getDBConnection();
  
  if (!$db || $username == "" || $password == "") {
    return array("status" => "fail", "message" => "Invalid credentials");
  }
  
  $stmt = $db->prepare("SELECT userid FROM users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    return array("status" => "fail", "message" => "Username already taken");
  }
  
  $hashed = password_hash($password, PASSWORD_BCRYPT);
  $stmt = $db->prepare("INSERT INTO users(username, password_hash) VALUES(?,?)");
  $stmt->bind_param("ss", $username, $hashed);
  
  return $stmt->execute() ? array("status" => "ok") : array("status" => "fail", "message" => "Database error");
}

/**
 * Validate a session key.
 * Returns array with status 'ok' and user info if valid, or 'error' if invalid/expired.
 */
function doValidate($sessionKey)
{
  if (empty($sessionKey)) {
    return array('status' => 'error', 'message' => 'no session key provided');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $sessionKeyHash = hash('sha256', $sessionKey);

  $stmt = $db->prepare('SELECT s.userid, s.expires_at, u.username FROM sessions s JOIN users u ON s.userid = u.userid WHERE s.sessionkey_hash = ? LIMIT 1');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('s', $sessionKeyHash);
  if (!$stmt->execute()) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    return array('status' => 'error', 'message' => 'invalid session');
  }

  $row = $result->fetch_assoc();
  
  // Check if session has expired
  if (strtotime($row['expires_at']) < time()) {
    // Delete expired session
    $deleteStmt = $db->prepare('DELETE FROM sessions WHERE sessionkey_hash = ?');
    $deleteStmt->bind_param('s', $sessionKeyHash);
    $deleteStmt->execute();
    return array('status' => 'error', 'message' => 'session expired');
  }

  return array('status' => 'ok', 'user_id' => $row['userid'], 'username' => $row['username']);
}

/**
 * Invalidate a session (logout).
 */
function doLogout($sessionKey)
{
  if (empty($sessionKey)) {
    return array('status' => 'error', 'message' => 'no session key provided');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $sessionKeyHash = hash('sha256', $sessionKey);
  $stmt = $db->prepare('DELETE FROM sessions WHERE sessionkey_hash = ?');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('s', $sessionKeyHash);
  $stmt->execute();

  return array('status' => 'ok', 'message' => 'logged out');
}

/**
 * Fetch paginated artist data from the artist_info table.
 * Supports optional keyword search (matches name or bio).
 */
function doGetArtists($offset, $limit, $search)
{
  try {
    $db = getDBConnection();
    if (!$db) {
      return array('status' => 'error', 'message' => 'database connection failed');
    }

    $offset = max(0, intval($offset));
    $limit  = min(500, max(1, intval($limit)));

    if (!empty($search)) {
      $pattern = '%' . $search . '%';

      $countStmt = $db->prepare('SELECT COUNT(*) AS total FROM artist_info WHERE name LIKE ? OR bio LIKE ?');
      $countStmt->bind_param('ss', $pattern, $pattern);

      $stmt = $db->prepare('SELECT id, name, listeners, play_count, bio, url, fetched_at FROM artist_info WHERE name LIKE ? OR bio LIKE ? ORDER BY listeners DESC LIMIT ? OFFSET ?');
      $stmt->bind_param('ssii', $pattern, $pattern, $limit, $offset);
    } else {
      $countStmt = $db->prepare('SELECT COUNT(*) AS total FROM artist_info');

      $stmt = $db->prepare('SELECT id, name, listeners, play_count, bio, url, fetched_at FROM artist_info ORDER BY listeners DESC LIMIT ? OFFSET ?');
      $stmt->bind_param('ii', $limit, $offset);
    }

    if (!$stmt || !$countStmt) {
      return array('status' => 'error', 'message' => 'database error');
    }

    $countStmt->execute();
    $total = intval($countStmt->get_result()->fetch_assoc()['total']);

    $stmt->execute();
    $result = $stmt->get_result();
    $artists = array();
    while ($row = $result->fetch_assoc()) {
      $artists[] = $row;
    }

    $db->close();

    return array(
      'status'  => 'ok',
      'artists' => $artists,
      'total'   => $total,
      'offset'  => $offset,
      'limit'   => $limit
    );
  } catch (Throwable $e) {
    return array('status' => 'error', 'message' => 'server error: ' . $e->getMessage());
  }
}

/* ════════════════════════════════════════════════════════════════
 * REVIEWS  –  stored on the DB VM in the `reviews` table
 * ════════════════════════════════════════════════════════════════ */

/**
 * Create a new review.  Validates the session to identify the user,
 * then INSERTs into the reviews table on this DB VM.
 */
function doCreateReview($sessionKey, $subject, $rating, $reviewText)
{
  $session = doValidate($sessionKey);
  if ($session['status'] !== 'ok') {
    return array('status' => 'error', 'message' => 'not authenticated');
  }

  if (empty($subject) || empty($reviewText) || $rating < 1 || $rating > 5) {
    return array('status' => 'error', 'message' => 'subject, rating (1-5), and review text are required');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $userId   = $session['user_id'];
  $username = $session['username'];

  $stmt = $db->prepare('INSERT INTO reviews (userid, username, subject, rating, review_text) VALUES (?, ?, ?, ?, ?)');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error: ' . $db->error);
  }

  $stmt->bind_param('issis', $userId, $username, $subject, $rating, $reviewText);
  if (!$stmt->execute()) {
    return array('status' => 'error', 'message' => 'failed to create review');
  }

  $reviewId = $db->insert_id;
  $db->close();

  return array('status' => 'ok', 'review_id' => $reviewId, 'message' => 'review created');
}

/**
 * Return all reviews written by the authenticated user.
 */
function doGetMyReviews($sessionKey)
{
  $session = doValidate($sessionKey);
  if ($session['status'] !== 'ok') {
    return array('status' => 'error', 'message' => 'not authenticated');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $userId = $session['user_id'];

  $stmt = $db->prepare('SELECT review_id, userid, username, subject, rating, review_text, created_at FROM reviews WHERE userid = ? ORDER BY created_at DESC');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $result = $stmt->get_result();

  $reviews = array();
  while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
  }

  $db->close();
  return array('status' => 'ok', 'reviews' => $reviews);
}

/**
 * Return all reviews from every user.
 * Supports optional keyword search (matches subject, username, or review_text).
 */
function doGetAllReviews($search)
{
  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  if (!empty($search)) {
    $pattern = '%' . $search . '%';
    $stmt = $db->prepare(
      'SELECT review_id, userid, username, subject, rating, review_text, created_at
       FROM reviews
       WHERE subject LIKE ? OR username LIKE ? OR review_text LIKE ?
       ORDER BY created_at DESC LIMIT 200'
    );
    if (!$stmt) {
      return array('status' => 'error', 'message' => 'database error');
    }
    $stmt->bind_param('sss', $pattern, $pattern, $pattern);
  } else {
    $stmt = $db->prepare(
      'SELECT review_id, userid, username, subject, rating, review_text, created_at
       FROM reviews ORDER BY created_at DESC LIMIT 200'
    );
    if (!$stmt) {
      return array('status' => 'error', 'message' => 'database error');
    }
  }

  $stmt->execute();
  $result = $stmt->get_result();

  $reviews = array();
  while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
  }

  $db->close();
  return array('status' => 'ok', 'reviews' => $reviews);
}

/**
 * Delete a review.  Only the owner can delete their own review.
 */
function doDeleteReview($sessionKey, $reviewId)
{
  $session = doValidate($sessionKey);
  if ($session['status'] !== 'ok') {
    return array('status' => 'error', 'message' => 'not authenticated');
  }

  if ($reviewId <= 0) {
    return array('status' => 'error', 'message' => 'invalid review id');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $userId = $session['user_id'];

  $stmt = $db->prepare('DELETE FROM reviews WHERE review_id = ? AND userid = ?');
  if (!$stmt) {
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('ii', $reviewId, $userId);
  $stmt->execute();

  if ($stmt->affected_rows === 0) {
    return array('status' => 'error', 'message' => 'review not found or not yours');
  }

  $db->close();
  return array('status' => 'ok', 'message' => 'review deleted');
}

function requestProcessor($request)
{
  try {
    if(!isset($request['type']))
    {
      return "ERROR: unsupported message type";
    }
    
    switch ($request['type'])
    {
      case "login":
        return doLogin($request['username'],$request['password']);
      case "register":
        return doRegister($request['username'],$request['password']);
      case "validate_session":
        return doValidate($request['session_key']);
      case "logout":
        return doLogout($request['session_key']);
      case "get_artists":
        return doGetArtists(
          $request['offset'] ?? 0,
          $request['limit']  ?? 20,
          $request['search'] ?? ''
        );
      case "create_review":
        return doCreateReview(
          $request['session_key'],
          $request['subject']     ?? '',
          $request['rating']      ?? 0,
          $request['review_text'] ?? ''
        );
      case "get_my_reviews":
        return doGetMyReviews($request['session_key']);
      case "get_all_reviews":
        return doGetAllReviews($request['search'] ?? '');
      case "delete_review":
        return doDeleteReview(
          $request['session_key'],
          $request['review_id'] ?? 0
        );
      default:
        return array("returnCode" => '0', 'message'=>"Server received request and processed");
    }
  } catch (Throwable $e) {
    return array('status' => 'error', 'message' => 'requestProcessor exception: ' . $e->getMessage());
  }
}

// use config from config directory
$server = new rabbitMQServer(__DIR__ . '/../config/testRabbitMQ.ini','testServer');

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

