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


function top_tracks($url, $api) { //get the top tracks
 echo "Top Tracks \n";

 $data = lastfm($url, $api, "chart.getTopTracks") ; //chart.getTopTracks

 if (!$data || !isset($data["tracks"]["track"])) { //checks for error
 	echo "No Track Data";
        return null;
    }

 $tracks = [];
 foreach ($data["tracks"]["track"] as $i => $track) { //puts them into a list
 	$tracks[] = [
		"rank" => $i + 1,
            	"track_name" => $track["name"],
            	"artist" => $track["artist"]["name"],
            	"play_count" => (int)($track["playcount"] ),
            	"listeners" => (int)($track["listeners"] ),
		"url" => $track["url"],
//		"mbid" => $track["mbid"],
		];
    }

 echo "DONE \n";
 return [ //returns formatted list
	 "type" => "top_tracks",
         "fetched_at" => date("Y-m-d"),
         "payload" => $tracks
	];
}

function top_artists($url, $api) { //gets the top artists
 echo "Top Artists \n";

 $data = lastfm($url, $api, "chart.getTopArtists"); //chart.getTopArtists

 if (!$data || !isset($data["artists"]["artist"])) { //error checking
	echo "No Artists Data \n";
        return null;
    }

 $artists = [];
 foreach ($data["artists"]["artist"] as $i => $artist) { //puts them into a list
	$artists[] = [
		"rank" => $i + 1,
            	"name" => $artist["name"],
            	"listeners"  => (int)($artist["listeners"] ?? 0),
            	"play_count" => (int)($artist["playcount"] ?? 0),
//		"mbid" => $artist["mbid"],
	];
    }

 echo "DONE \n";
 return [ //returns formatted list
	"type" => "top_artists",
        "fetched_at" => date("Y-m-d"),
        "payload" => $artists
    ];
}

/*function artist_info($url, $api, $name) { //gets the information on an artist
 echo "Artist Info \n";

 $data = lastfm($url, $api, "artist.getInfo", ["artist" => $name]); //artist.getInfo

 if (!$data || !isset($data["artist"])) { //error checking
       	echo "No Data for Artist: $name\n";
        return null;
	}

 echo "DONE \n";
 return [ //returns formatted info
	"type" => "artist_info",
        "fetched_at" => date("Y-m-d"),
        "payload" => [
            "name" => $data["artist"]["name"] ?? "",
            "listeners" => (int)($data["artist"]["stats"]["listeners"] ),
            "play_count" => (int)($data["artist"]["stats"]["playcount"] ),
            "bio" => trim(strip_tags($data["artist"]["bio"]["summary"] )),
	    "url" => $data["artist"]["url"],
	    "mbid" => $data["artist"]["mbid"],
        ]
    ];
}
*/

function many_artists($url, $api) { //gets info for 300 artists
 echo "300 Artists \n";

 $data = lastfm($url, $api, "chart.getTopArtists", ["limit" => 300]);

 if (!$data || !isset($data["artists"]["artist"])) { //error checking
	echo "No Artists Data \n";
        return null;
    }

 $results = [];
 foreach ($data["artists"]["artist"] as $artist) { //loop through each artist
	$name = $artist["name"] ?? "";

	$info = lastfm($url, $api, "artist.getInfo", ["artist" => $name]); //gets info

	if (!$info || !isset($info["artist"])) { //skips if no data
		echo "No data for: $name \n";
		continue;
	}

	$results[] = [
		"name"       => $info["artist"]["name"],
		"listeners"  => (int)($info["artist"]["stats"]["listeners"]),
		"play_count" => (int)($info["artist"]["stats"]["playcount"]),
		"bio"        => trim(strip_tags($info["artist"]["bio"]["summary"])),
		"url"        => $info["artist"]["url"],
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
	}

 if($request['type'] == "weekly_charts") { //calls functions for the weekly charts
	$tracks  = top_tracks($URL, $API);
       	$artists = top_artists($URL, $API);
        	return [
                	"status"  => "success",
               		"tracks"  => $tracks,
                	"artists" => $artists];

//    } else if ($request["type"] == "artist") { //calls function in charge of artists
  //      $name = $request["name"] ?? "";

//	if ($name == "") { //error checking
  //              return ["status" => "error", "message" => "Missing artist name."]; }

//        $data = artist_info($URL, $API, $name);
  //      return ["status" => "success", "data" => $data];

    } else if ($request["type"] == "many_artists") {
    	$data = many_artists($URL, $API);
    	return ["status" => "success", "data" => $data];}

      else { //error checking
        echo "Unknown type: " . $request['type'] . "\n"; }
}

$server = new rabbitMQServer(__DIR__ . '/../testRabbitMQ.ini', 'testServer3'); //connection to the broker
echo "DMZ START\n";
$server->process_requests('requests');

exit();
?>
