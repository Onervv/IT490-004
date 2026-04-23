#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function db()
{
    $db = new mysqli("127.0.0.1", "IT490", "ubuntu", "bundles");
    if ($db->connect_errno)
    {
      echo "DB connection failed";
      return null;
    }
    echo "Connected to DB";
    return $db;
}

function storeFrontendBundle()
{
    $db = db();
    $bundleName = "FrontendBundle";
    $stmt = $db->prepare("Select * FROM bundles WHERE versionnum = (SELECT MAX(versionnum) FROM bundles WHERE bundlename LIKE 'FrontendBundle')");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $row = (int)$row["versionnum"];
    echo $row;
    $bundleVer = $row + 1;
    if(!$db)
	return array("status"=>"fail");
    $stmt = $db->prepare("Insert into bundles(bundlename, versionnum) Values(?,?)");
    $stmt->bind_param("ss", $bundleName, $bundleVer);
    $stmt->execute();
    $safe_version = escapeshellarg($bundleVer);
    $output = shell_exec("/home/it490-vm/DeployStuff/IT490-004/TestExtract/frontsaver.sh $safe_version");
    echo $output;
    return "stored bundle";
}
function storeDMZBundle()
{
    $db = db();
    $bundleName = "DMZBundle";
    $stmt = $db->prepare("Select * FROM bundles WHERE versionnum = (SELECT MAX(versionnum) FROM bundles WHERE bundlename LIKE 'DMZBundle')");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $row = (int)$row["versionnum"];
    echo $row;
    $bundleVer = $row + 1;
    if(!$db)
        return array("status"=>"fail");
    $stmt = $db->prepare("Insert into bundles(bundlename, versionnum) Values(?,?)");
    $stmt->bind_param("ss", $bundleName, $bundleVer);
    $stmt->execute();
    $safe_version = escapeshellarg($bundleVer);
    $output = shell_exec("/home/it490-vm/DeployStuff/IT490-004/TestExtract/dmzsaver.sh $safe_version");
    echo $output;
    return "stored bundle";
}

function storeDBBundle()
{
    $db = db();
    $bundleName = "DataBaseBundle";
    $stmt = $db->prepare("Select * FROM bundles WHERE versionnum = (SELECT MAX(versionnum) FROM bundles WHERE bundlename LIKE 'DataBaseBundle')");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $row = (int)$row["versionnum"];
    echo $row;
    $bundleVer = $row + 1;
    if(!$db)
        return array("status"=>"fail");
    $stmt = $db->prepare("Insert into bundles(bundlename, versionnum) Values(?,?)");
    $stmt->bind_param("ss", $bundleName, $bundleVer);
    $stmt->execute();
    $safe_version = escapeshellarg($bundleVer);
    $output = shell_exec("/home/it490-vm/DeployStuff/IT490-004/TestExtract/dbsaver.sh $safe_version");
    echo $output;
    return "stored bundle";
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
    case "frontendbundle":
      return storeFrontendBundle();
    case "dmzbundle":
      return storeDMZBundle();
    case "dbbundle":
      return storeDBBundle();
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer3");

$server->process_requests('requestProcessor');
exit();
?>

