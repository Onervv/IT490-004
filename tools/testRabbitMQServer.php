#!/usr/bin/php
<?php
// includes now consolidated
require_once __DIR__ . '/../includes/path.inc';
require_once __DIR__ . '/../includes/get_host_info.inc';
require_once __DIR__ . '/../includes/rabbitMQLib.inc';

function doLogin($username, $password)
{
  error_log("doLogin called with user={$username}");

  if (empty($username) || empty($password)) {
    return array('status' => 'error', 'message' => 'username or password empty');
  }

  // Database connection - adjust credentials/DSN to your environment
  $dsn = 'mysql:host=127.0.0.1;dbname=testdb;charset=utf8mb4';
  $dbUser = 'testUser';
  $dbPass = '12345';

  try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if (!$row) {
      return array('status' => 'error', 'message' => 'username not found');
    }

    if (password_verify($password, $row['password_hash'])) {
      // Successful login - you can return user info or token as needed
      return array('status' => 'success', 'user_id' => $row['id']);
    } else {
      return array('status' => 'error', 'message' => 'invalid credentials');
    }

  } catch (PDOException $e) {
    error_log("doLogin DB error: " . $e->getMessage());
    return array('status' => 'error', 'message' => 'database error');
  }
}

/**
 * Register a new user.
 * Returns array('status'=>'success','user_id'=>.. ) on success or
 * array('status'=>'error','message'=>...) on failure.
 */
function doRegister($username, $password)
{
  error_log("doRegister called with user={$username}");

  if (empty($username) || empty($password)) {
    return array('status' => 'error', 'message' => 'username or password empty');
  }

  $dsn = 'mysql:host=127.0.0.1;dbname=testdb;charset=utf8mb4';
  $dbUser = 'testUser';
  $dbPass = '12345';

  try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check if username already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
      return array('status' => 'error', 'message' => 'username taken');
    }

    // Hash the password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insert = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, NOW())');
    $insert->execute([$username, $hash]);
    $userId = $pdo->lastInsertId();

    return array('status' => 'success', 'user_id' => $userId);

  } catch (PDOException $e) {
    error_log("doRegister DB error: " . $e->getMessage());
    return array('status' => 'error', 'message' => 'database error');
  }
}

function requestProcessor($request)
{
  error_log("testRabbitMQServer requestProcessor received: " . json_encode($request));
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

// use config from config directory
$server = new rabbitMQServer(__DIR__ . '/../config/testRabbitMQ.ini','testServer');

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

