#!/usr/bin/php
<?php
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

function requestProcessor($request)
{
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
    default:
      return array("returnCode" => '0', 'message'=>"Server received request and processed");
  }
}

// use config from config directory
$server = new rabbitMQServer(__DIR__ . '/../config/testRabbitMQ.ini','testServer');

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

