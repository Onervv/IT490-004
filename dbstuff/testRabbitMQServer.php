#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function db()
{
	$db = new mysqli("100.116.117.114", "IT490", "IT490Password", "IT490");
	if ($db->connect_errno)
	{
	  echo "DB connection failed";
	  return null;
	}

	return $db;
}

function doRegister($username,$password)
{
    $db = db();
    if (!$db || $username == "" || $password == "")
	    return array("status"=>"fail");
    $stmt = $db->prepare("Select userid from users where username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0)
	    return array("status"=>"fail","message"=>"Username already taken");
    $hashed = password_hash($password,PASSWORD_BCRYPT);
    $stmt = $db->prepare("Insert into users(username,password_hash) Values(?,?)");
    $stmt->bind_param("ss",$username,$hashed);
    return $stmt->execute() ? array("status"=>"ok") : array("status"=>"fail");
}

function doLogin($username,$password)
{
    $db = db();
    if(!$db || $username == "" || $password == "")
	    return array("status"=>"fail");
	    // lookup username in databas
    $stmt = $db->prepare("Select userid, password_hash from users where username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    // check password
    if (!$row || !password_verify($password, $row["password_hash"]))
	    return array("status"=>"fail");
    $sessionId = bin2hex(random_bytes(32));
    $stmt2 = $db->prepare("Insert into sessions(userid, sessionkey_hash, expires_at) Values(?,?,Date_Add(now(),interval 2 hour))");
    $sessionHash = hash("sha256", $sessionId);
    $userId = $row["userid"];
    $stmt2->bind_param("is", $userId, $sessionHash);
    $stmt2->execute();
    return array("status"=>"ok","session_key"=>$sessionId,"user_id"=>$userId,"username"=>$username);
}

function doValidate($sessionId)
{
    $db = db();
    if (!$db || $sessionId == "")
	return array("status"=>"fail");
    $stmt = $db->prepare("select 1 from sessions where sessionkey_hash=? and expires_at > now() limit 1");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    return $stmt->get_result()->num_rows == 1 ? array("status"=>"ok") : array("status"=>"fail");
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "register":
      return doRegister($request['username'],$request['password']);	    
    case "login": 
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['session_key']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer2");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

