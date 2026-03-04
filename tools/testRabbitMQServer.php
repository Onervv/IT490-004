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
    error_log("Database connection failed: " . $conn->connect_error);
    return null;
  }
  return $conn;
}

function doLogin($username, $password)
{
  error_log("doLogin called with user={$username}");

  if (empty($username) || empty($password)) {
    return array('status' => 'error', 'message' => 'username or password empty');
  }

  $db = getDBConnection();
  if (!$db) {
    return array('status' => 'error', 'message' => 'database connection failed');
  }

  $stmt = $db->prepare('SELECT userid, password_hash FROM users WHERE username = ? LIMIT 1');
  if (!$stmt) {
    error_log("doLogin prepare failed: " . $db->error);
    return array('status' => 'error', 'message' => 'database error');
  }

  $stmt->bind_param('s', $username);
  if (!$stmt->execute()) {
    error_log("doLogin execute failed: " . $stmt->error);
    return array('status' => 'error', 'message' => 'database error');
  }

  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    return array('status' => 'error', 'message' => 'username not found');
  }

  $row = $result->fetch_assoc();
  if (password_verify($password, $row['password_hash'])) {
    return array('status' => 'ok', 'user_id' => $row['userid']);
  }

  return array('status' => 'error', 'message' => 'invalid credentials');
}

/**
 * Register a new user.
 * Returns array('status'=>'ok') on success or
 * array('status'=>'fail','message'=>...) on failure.
 */
function doRegister($username, $password)
{
  error_log("doRegister called with user={$username}");
  
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

function requestProcessor($request)
{
  error_log("testRabbitMQServer requestProcessor received: " . json_encode($request));
  echo "received request".PHP_EOL;
  var_dump($request);
  
  if(!isset($request['type']))
  {
    error_log("ERROR: request missing 'type' field. Keys: " . implode(',', array_keys($request)));
    return "ERROR: unsupported message type";
  }
  
  error_log("Processing request type: " . $request['type']);
  
  switch ($request['type'])
  {
    case "login":
      error_log("Routing to doLogin");
      return doLogin($request['username'],$request['password']);
    case "register":
      error_log("Routing to doRegister");
      return doRegister($request['username'],$request['password']);
    case "validate_session":
      error_log("Routing to doValidate");
      return doValidate($request['sessionId']);
    default:
      error_log("Unknown request type: " . $request['type']);
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

