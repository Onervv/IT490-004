#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($request)
{
    $username = $request['username'];
    $password = $request['password'];

    var_dump($username);
    var_dump($password);

    $dbrequest = array();
    $dbrequest['type'] = $request['type'];
    $dbrequest['username'] = $username;
    $dbrequest['password'] = $password;

    //if(isset($client)){
    //$response = $client->send_request($dbrequest);
    //echo "sending request to listener".PHP_EOL;
    //return $response;
    //}

    //$query = "INSERT INTO users"
    //$mydb = 0;
    //if(isset($mydb))
    //{
    //$stmt = $mydb->prepare("INSERT INTO users(username, password_hash) VALUES(?, ?)");
    //$stmt->bind_param("ss", $username, $password);
    //$stmt->execute();
    //}

    // lookup username in databas
    // check password
    return array("returnCode" => '0', 'message'=>"Server received request and processed");
    //return true;
    //return false if not valid
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      echo "doing login stuff".PHP_EOL;
      return doLogin($request);
    case "validate_session":
      echo "Validate some stuff".PHP_EOL;
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer2");

//$mydb = new mysqli('100.116.117.114:3306', 'backendvm','backend123!','IT490');

echo "testRabbitMQServer BEGIN".PHP_EOL;

//$client = new rabbitMQClient("testRabbitMQ.ini","testServer3");

$server->process_requests('requestProcessor');

echo "testRabbitMQServer END".PHP_EOL;
exit();
?>


