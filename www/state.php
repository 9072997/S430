<?php
	require_once(dirname(__FILE__) . '/../includes/db.inc.php');
	$day = ((abs(intval($_GET['day']))+1)%5)-1; // sanitize day = 1,2,3
	$match = db1("SELECT MIN(d${day}match) AS match FROM teams WHERE d${day}score IS NULL")->match;
	$data['match']=$match;
	if($match > 1) { // previous match exists
		$red = db1("SELECT name, d${day}score AS score FROM teams WHERE d${day}match=? AND d${day}table='red'", $match-1);
		$blue = db1("SELECT name, d${day}score AS score FROM teams WHERE d${day}match=? AND d${day}table='blue'", $match-1);
		$data['previous']['red']=$red->name . ': ' . $red->score;
		$data['previous']['blue']=$blue->name . ': ' . $blue->score;
	} else { // no previous match
		$data['previous']['red']='??????:???';
		$data['previous']['blue']='??????:???';
	}
	$red = db1("SELECT name, d${day}ready AS ready FROM teams WHERE d${day}match=? AND d${day}table='red'", $match);
	$blue = db1("SELECT name, d${day}ready AS ready FROM teams WHERE d${day}match=? AND d${day}table='blue'", $match);
	$data['now']['red']['name'] = $red->name;
	$data['now']['blue']['name'] = $blue->name;
	$data['now']['red']['ready'] = $red->ready;
	$data['now']['blue']['ready'] = $blue->ready;
	if(db1("SELECT COUNT(1) as count FROM teams WHERE d${day}match>? LIMIT 1", $match)->count > 0) { // next match exists
		$data['next']['red'] = db1("SELECT name FROM teams WHERE d${day}match=? AND d${day}table='red'", $match+1)->name;
		$data['next']['blue'] = db1("SELECT name FROM teams WHERE d${day}match=? AND d${day}table='blue'", $match+1)->name;
	} else { // no previous match
		$data['next']['red'] = '??????';
		$data['next']['blue'] = '??????';
	}
	echo json_encode((object)$data);
?>
