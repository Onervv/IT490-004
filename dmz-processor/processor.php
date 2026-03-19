#!/usr/bin/php
<?php

require_once __DIR__ . '/../get_host_info.inc';
require_once __DIR__ . '/../rabbitMQLib.inc';

$API = "b97a6678535cddda9458dcee24dddac2";
$URL = "http://ws.audioscrobbler.com/2.0/";


function lastfm($url, $api_key, $method, $extras = []) { //call api
 $query = array_merge([
	 "method"  => $method,
       	 "api_key" => $api_key,
       	 "format"  => "json", ],
	 $extras);

 $endpoint = $url . "?" . http_build_query($query); //creates url

 $data = file_get_contents($endpoint);

  return json_decode($data, true); //returns data
}

function many_artists($url, $api) { //gets info for 300 artists
 echo "300 Artists \n";

 $top_artists = lastfm($url, $api, "chart.getTopArtists", ["limit" => 300]);

 if (!$top_artists || !isset($top_artists["artists"]["artist"])) { //error checking
	echo "No Artists Data \n";
        return null;
    }

 $results = [];
 foreach ($top_artists["artists"]["artist"] as $artist) { //loop through each artist
	$name = $artist["name"] ?? "";

	$artist_info = lastfm($url, $api, "artist.getInfo", ["artist" => $name]); //gets info

	if (!$artist_info || !isset($artist_info["artist"])) { //skips if no data
		echo "No data for: $name \n";
		continue;
	}

	$results[] = [
		"name"       => $artist_info["artist"]["name"],
		"listeners"  => (int)($artist_info["artist"]["stats"]["listeners"]),
		"play_count" => (int)($artist_info["artist"]["stats"]["playcount"]),
		"bio"        => trim(strip_tags($artist_info["artist"]["bio"]["summary"])),
		"url"        => $artist_info["artist"]["url"],
//		"mbid" => $info["artist"]["mbid"],
	];
 }
 echo "DONE \n";
 return [
	"type"       => "many_artists",
        "fetched_at" => date("Y-m-d"),
        "payload"    => $results
    ];
}

function requests($request) { //handles the requests
 global $API, $URL;

 if (!isset($request['type'])) { //error checking
        return ["status" => "error", "message" => "unsupported type"];

} elseif ($request["type"] == "many_artists") {
    	$data = many_artists($URL, $API);
    	return ["status" => "success", "data" => $data];

} else { //error checking
        echo "Unknown type: " . $request['type'] . "\n";
	}
}

$server = new rabbitMQServer(__DIR__ . '/../testRabbitMQ.ini', 'testServer3'); //connection to the broker
echo "DMZ START\n";
$server->process_requests('requests');

exit();
?>
