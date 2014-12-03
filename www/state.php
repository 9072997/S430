<?php
	require_once(dirname(__FILE__) . '/../includes/db.inc.php');
	header('Content-Type: application/json');
	$day = ((abs(intval($_GET['day']))+1)%5)-1; // sanitize day = 1,2,3
		
	$red = db1("SELECT name,d${day}score AS score FROM teams WHERE d${day}match>? AND d${day}table='red' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()+60*-10));
	$blue = db1("SELECT name,d${day}score AS score FROM teams WHERE d${day}match>? AND d${day}table='blue' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()+60*-10));
	$data['previous']['red']=$red->name . ': ' . $red->score;
	$data['previous']['blue']=$blue->name . ': ' . $blue->score;
	
	$red = db1("SELECT name FROM teams WHERE d${day}match>? AND d${day}table='red' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()+60*-5));
	$blue = db1("SELECT name FROM teams WHERE d${day}match>? AND d${day}table='blue' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()+60*-5));
	$data['now']['red'] = $red->name;
	$data['now']['blue'] = $blue->name;
	
	$red = db1("SELECT name FROM teams WHERE d${day}match>? AND d${day}table='red' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()));
	$blue = db1("SELECT name FROM teams WHERE d${day}match>? AND d${day}table='blue' ORDER BY d${day}match ASC LIMIT 1", date("H:i:s", time()));
	$data['next']['red'] = $red->name;
	$data['next']['blue'] = $blue->name;

	echo json_encode((object)$data);
?>
