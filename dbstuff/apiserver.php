#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$client = new rabbitMQClient("testRabbitMQ.ini", "APIServer");

//This creates a connection to the database and if connection fails, print an error
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

//this handles storing artists that are received from the API
function storeartists($artists)
{
    $db = db();
    if (!$db || empty($artists))
	    return array("status"=>"fail");
    foreach ($artists as $artist)
    {
	$stmt = $db->prepare("Insert into artists(`rank`, name, listeners, play_count, url) VALUES(?,?,?,?,?)");
	$stmt->bind_param("isiss", $artist['rank'], $artist['name'], $artist['listeners'], $artist['play_count'], $artist['url']);
        $stmt->execute();
    }
}
 function artistinfo($request)
 {
 $db = db();
 // checks db and payload
 if (!$db || empty($request['data']['payload']))
 {
 echo "FAILED: Database connection failed OR payload is empty.\n";
 return array("status"=>"fail", "message"=>"No artist info to store"); 
}
 // gets artists
$artists_list = $request['data']['payload'];
 $fetched_at = $request['data']['fetched_at']; 
// inserts info into artistinto table
 $stmt = $db->prepare("INSERT INTO artist_info (name, listeners, play_count, bio, url, fetched_at) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
listeners = VALUES(listeners), play_count = VALUES(play_count), bio = VALUES(bio), fetched_at = VALUES(fetched_at)");
 if (!$stmt)
 {
 echo "MYSQL PREPARE ERROR: " . $db->error . "\n"; return array("status"=>"fail", "message"=>"SQL Prepare Failed"); 
}
 $success_count = 0;
 foreach ($artists_list as $artist)
 {
 $stmt->bind_param("siisss", $artist['name'], $artist['listeners'], $artist['play_count'], $artist['bio'], $artist['url'], $fetched_at );
 if (!$stmt->execute()) 
{
 echo "MYSQL EXECUTE ERROR on artist " . $artist['name'] . ": " . $stmt->error . "\n";
}
else { $success_count++;
}
} 
}

function getartistinfo($name)
{
	$db = db();
	if(!$db || $name == "")
		return array("status"=>"fail);
	$stmt = $db->prepare(select name, listeners, play_count, bio, url, fetched_at from artist_info where name=?");
	$stmt->bind_param("s", $name);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
$this->routing_key = $this->machine[$server]["ROUTING_KEY"];
	if (!$row)
		return array("status"=>"fail", "message"=>"Artist not found");
	return array("status"=>"ok", "data"=>$row);

}
$request = array();
$request['type'] = "many_artists";
$response = $client->send_request($request);
print_r($response);
artistinfo($response);

echo "testRabbitMQServer BEGIN".PHP_EOL;
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
