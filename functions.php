<?php

define("SYS_BUS_LOCATION","/sys/bus/w1/devices/"); // location of sensors data bus
define("DATA_DIR","/home/pi/public_html/templogger/data/"); // where we save rrd files, and where sensor config lives
define("BASE_DIR","/home/pi/public_html/templogger/"); // where we save rrd files, and where sensor config lives


function pushbullet_notify($title = "test", $body = "test", $target = PUSHBULLET_TARGETS) {
	// mostly borrowed from "pushnotify" bash script (of which the most important bit is here:
	// echo $DATA | curl -u $APIKEY: -X POST https://api.pushbullet.com/v2/pushes --header 'Content-Type: application/json' --data-binary @-

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

function create_rrd_database($sensor) {
	$options = array(
		"--step", "300",            // Use a step-size of 5 minutes
		"DS:temp:GAUGE:600:U:U", // raw sensor temperature
		"DS:ctemp:GAUGE:600:U:U", // with offset applied (if there is one)
		"RRA:AVERAGE:0.5:1:24192", // 12 weeks
		"RRA:AVERAGE:0.5:12:8760", // per hour for a year 1 year
		);
	$filename = DATA_DIR.$sensor.".rrd";
	$ok = rrd_create ( $filename, $options );
	
	if (!$ok) {
		 echo "<b>Creation error: </b>".rrd_error()."\n";
		 return false;
	}
	
	return true;
}

function update_rrd_database($sensor, $temp, $offset = 0) {
	$filename = DATA_DIR.$sensor.".rrd";
	$now = round(gettimeofday(true));
	
	if (!file_exists($filename)) {
		$ok = create_rrd_database($sensor);
		if (!$ok) {
			return false;
		}
	}
	
	$ctemp = $temp + $offset;
	$update = array("$now:$temp:$ctemp");
	
	$ok = rrd_update($filename, $update);
	
	return $ok;
}

function update_all_sensors ( ) {
	$dir = SYS_BUS_LOCATION;
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( substr ( $file , 0, 2) == "28" ) { // 28_* is a temp sensor
					$temp = read_sensor($dir.$file);
					if ($temp !== false && is_numeric($temp) ) {
						update_rrd_database($file, $temp);
					}
					else {
						echo "No temp data returned ($temp)\n";
					}
				}
			}
			closedir($dh);
		}
	}
}

function read_sensor($sensor) {
	echo "read_sensor($sensor)\n";
	
	$filename = $sensor."/w1_slave";
		
	$data = file_get_contents($filename);
	
	if ($data === false )
		return false;
	
	$lines = explode("\n", $data);
	//print_r($lines);
	
	if (substr($lines[0],-3) == "YES") { // CRC is ok
		$fields = explode(" ", $lines[1]);
		//print_r($fields);
		$temp = substr($fields[9],2,5)/1000;
		echo "temperature = $temp \n";
		return $temp;
	}
	else {
		echo "Bad CRC data\n";
		return false;
	}
}

function create_graph($files, $names, $title = "Temperature Data") {
	$colours = array(
		"#FF0000", // Red
		"#0000FF", // Blue
  	 	"#BBBB00", // Yellow
  	 	"#00FFFF", // Cyan / Aqua
  	 	"#C0C0C0", // Silver
  	 	"#808080", // Gray
  	 	"#800080", // Purple
  	 	"#000080", // Navy
  	 	"#00FF00", // Green
  	 	"#000000", // Black
	);
	//print_r($colours);
	$options = array(
		"--start=end-1d",
		"--title=$title",
		"--vertical-label=Degrees Celsius",
		"--width=700", "--height=500",
		"--imgformat=SVG",
		"--lower-limit=-20",
		"--upper-limit=30",
		"--step=60",
		"--slope-mode",
		"--right-axis=1:0",
		"--right-axis-label=Degrees Celsius",
		
	);
	$i = 0;
	foreach($files as $file) {
		// add to list
		$options[] = "DEF:temp$i=$file:temp:AVERAGE";
		$options[] = "CDEF:ttemp$i=temp$i,1,*";
		$options[] = "LINE1:ttemp$i".$colours[$i%count($colours)].":".$names[$i]; //." [#".($i+1)."]"; // note that we reuse colours
		$i++;
	}

	$ret = rrd_graph(BASE_DIR."temp.svg", $options);
	if (! $ret) {
		echo "<b>Graph error: </b>".rrd_error()."\n";
	}
}




?>
