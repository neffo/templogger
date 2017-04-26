<?php

function pushbullet_notify($title = "test", $body = "test", $target = PUSHBULLET_TARGETS) {
	// mostly borrowed from "pushnotify" bash script (of which the most important bit is here:
	// echo $DATA | curl -u tXsHRjiFERdKbpvjBb1BCoLOTaG4J7lX: -X POST https://api.pushbullet.com/v2/pushes --header 'Content-Type: application/json' --data-binary @-

	$type = "note";
	$source_iden = PUSHBULLET_SOURCE;
	$url = "https://api.pushbullet.com/v2/pushes";

	$json_data = json_encode(array("type" => $type, "title" => $title, "body" => "$body", "device_iden" => $target, "source_device_iden" => $source_iden ));
	
	$username = PUSHBULLET_USER_SECRET;
	
	$process = curl_init($url);
	
	curl_setopt($process, CURLOPT_POST, 1);
	curl_setopt($process, CURLOPT_POSTFIELDS, $json_data);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($process, CURLOPT_USERPWD, $username);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$data = curl_exec($process);
	
	echo "RETURNED: ".$data."\n";
	curl_close($process);	
}









?>
