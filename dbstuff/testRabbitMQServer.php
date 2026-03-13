#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


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

//This function is for registration
function doRegister($username,$password)
{
   //if username and password are left empty it fails
    $db = db();
    if (!$db || $username == "" || $password == "")
	    return array("status"=>"fail");
    //checks if username is taken
    $stmt = $db->prepare("Select userid from users where username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0)
	    return array("status"=>"fail","message"=>"Username already taken");
   
   //hashes the password 
    $hashed = password_hash($password,PASSWORD_BCRYPT);
    $stmt = $db->prepare("Insert into users(username,password_hash) Values(?,?)");
    $stmt->bind_param("ss",$username,$hashed);
   //status returns as ok if it's a success and fail if it's a failure
    return $stmt->execute() ? array("status"=>"ok") : array("status"=>"fail");
}
//function is for login
function doLogin($username,$password)
{
    $db = db();
    if(!$db || $username == "" || $password == "")
	    return array("status"=>"fail");
	    // lookup username in database
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
//is for validation
function doValidate($sessionId)
{
    $db = db();
    if (!$db || $sessionId == "")
        return array("status"=>"fail");
    $hash = hash("sha256", $sessionId);
    $stmt = $db->prepare("SELECT s.userid, u.username FROM sessions s JOIN users u ON s.userid = u.userid WHERE s.sessionkey_hash=? AND s.expires_at > now() LIMIT 1");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row)
        return array("status"=>"fail");
    return array("status"=>"ok", "user_id"=>$row['userid'], "username"=>$row['username']);
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
    return array("status"=>"ok");
}

function artistinfo($artists)
{
	$db = db();
	if (!$db || empty($artists))
		return array("status"=>"fail");
	foreach ($artists as $artist)
	{
		$stmt = $db->prepare("Insert into artist_info(name, listeners, play_count, bio, url, fetched_at) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param("siisss", $atist['name'], $artist['listeners'], $artist['play_count'], $arist['bio'], $artist['url'], $artist['fetched_at']);
		if (!stmt->execute())
			return array("status"=>"fail");
	}
	return array("status"=>"fail");
}

function getartistinfo($name)
{
	$db = db();
	if(!$db || $name == "")
		return array("status"=>"fail);
	$stmt = $db->prepare(select name, listeners, play_count, bio, url, fetched_at from artist_info where name=?");
	$stmt->bind_param("s". $name);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if (!$row)
		return array("status"=>"fail", "message"=>"Artist not found");
	return array("status"=>"ok", "data"=>$row);
}

function etallartists($offset, $limit, $search)
{
	return array("status"=>"ok", "artists"=>array(), "total"=>0, "offset"=>$offset, "limit"=>$limit);
}

function getallartists($offset, $limit, $search)
{

        $db= db();
        if (!$db) return array("status"=>"fail", "message"=>"DB connection failed");
        $offset = max(0, intval($offset));
        $limit = min(500, max(1, intval($limit)));
        if (!empty($search))
        {
                $pattern = '%' . $db->real_escape_string($search) . '%';
                $countResult = $db->query("SELECT COUNT(*) AS total FROM artist_info WHERE name LIKE '$pattern'  OR bio LIKE '$pattern'");
                $sql = "SELECT id, name, listeners, play_count, bio, url, fetched_at FROM artist_info WHERE name LIKE '$pattern' OR bio LIKE '$pattern' ORDER BY listeners DESC LIMIT $limit OFFSET $offset";
        }
        else
        {
                $countResult = $db->query("SELECT COUNT(*) AS total FROM artist_info");
                $sql = "SELECT id, name, listeners, play_count, bio, url, fetched_at FROM artist_info ORDER BY listeners DESC LIMIT $limit OFFSET $offset";
          
        }
        
        $total = intval($countResult->fetch_assoc()['total']);
      
        $result = $db->query($sql);
        $artists = array();
        while ($row = $result->fetch_assoc())
        {
                $artists[] = $row;
        }

        return array("status"=>"ok", "artists"=>$artists, "total"=>$total, "offset"=>$offset, "limit"=>$limit);
}

function doCreateReview($sessionKey, $subject, $rating, $reviewText)
{
    $session = doValidate($sessionKey);
    if ($session['status'] !== 'ok')
        return array('status' => 'error', 'message' => 'not authenticated');

    if (empty($subject) || empty($reviewText) || $rating < 1 || $rating > 5)
        return array('status' => 'error', 'message' => 'subject, rating (1-5), and review text are required');

    $db = db();
    if (!$db)
        return array('status' => 'error', 'message' => 'database connection failed');

    $userId   = $session['user_id'];
    $username = $session['username'];

    $stmt = $db->prepare('INSERT INTO reviews (userid, username, subject, rating, review_text) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt)
        return array('status' => 'error', 'message' => 'database error: ' . $db->error);

    $stmt->bind_param('issis', $userId, $username, $subject, $rating, $reviewText);
    if (!$stmt->execute())
        return array('status' => 'error', 'message' => 'failed to create review');

    $reviewId = $db->insert_id;
    return array('status' => 'ok', 'review_id' => $reviewId, 'message' => 'review created');
}

function doGetMyReviews($sessionKey)
{
    $session = doValidate($sessionKey);
    if ($session['status'] !== 'ok')
        return array('status' => 'error', 'message' => 'not authenticated');

    $db = db();
    if (!$db)
        return array('status' => 'error', 'message' => 'database connection failed');

    $userId = $session['user_id'];
    $stmt = $db->prepare('SELECT review_id, userid, username, subject, rating, review_text, created_at FROM reviews WHERE userid = ? ORDER BY created_at DESC');
    if (!$stmt)
        return array('status' => 'error', 'message' => 'database error');

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = array();
    while ($row = $result->fetch_assoc())
        $reviews[] = $row;

    return array('status' => 'ok', 'reviews' => $reviews);
}

function doGetAllReviews($search)
{
    $db = db();
    if (!$db)
        return array('status' => 'error', 'message' => 'database connection failed');

    if (!empty($search)) {
        $pattern = '%' . $search . '%';
        $stmt = $db->prepare('SELECT review_id, userid, username, subject, rating, review_text, created_at FROM reviews WHERE subject LIKE ? OR username LIKE ? OR review_text LIKE ? ORDER BY created_at DESC LIMIT 200');
        if (!$stmt)
            return array('status' => 'error', 'message' => 'database error');
        $stmt->bind_param('sss', $pattern, $pattern, $pattern);
    } else {
        $stmt = $db->prepare('SELECT review_id, userid, username, subject, rating, review_text, created_at FROM reviews ORDER BY created_at DESC LIMIT 200');
        if (!$stmt)
            return array('status' => 'error', 'message' => 'database error');
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = array();
    while ($row = $result->fetch_assoc())
        $reviews[] = $row;

    return array('status' => 'ok', 'reviews' => $reviews);
}

function doDeleteReview($sessionKey, $reviewId)
{
    $session = doValidate($sessionKey);
    if ($session['status'] !== 'ok')
        return array('status' => 'error', 'message' => 'not authenticated');

    if ($reviewId <= 0)
        return array('status' => 'error', 'message' => 'invalid review id');

    $db = db();
    if (!$db)
        return array('status' => 'error', 'message' => 'database connection failed');

    $userId = $session['user_id'];
    $stmt = $db->prepare('DELETE FROM reviews WHERE review_id = ? AND userid = ?');
    if (!$stmt)
        return array('status' => 'error', 'message' => 'database error');

    $stmt->bind_param('ii', $reviewId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows === 0)
        return array('status' => 'error', 'message' => 'review not found or not yours');

    return array('status' => 'ok', 'message' => 'review deleted');
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
    case "store_artists":
	    return storeartists($request['artists']);
    case "artist_info":
 	    return artistinfo($request); 
    case "many_artists":
	    return getartistinfo($request['name']);
    case "get_artists":
	    return getallartists($request['offset'] ?? 0, $request['limit'] ?? 20, $request['search'] ?? '');
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
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer2");
$Apiserver = new rabbitMQServer("testRabbitMQ.ini", "APIServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
$Apiserver->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

