<?php
	require_once(dirname(__FILE__) . '/../includes/db.inc.php');
	header('Content-Type: application/json');
	$day = ((abs(intval($_GET['day']))+1)%5)-1; // sanitize day = 1,2,3
	$teams = db('SELECT * FROM teams');
	usort($teams, function($a, $b) use ($day) {
		$score = 0;
		
		$tableprop = 'd' . $day . 'table';
		if($a->$tableprop == 'red') {
			$score -= 1;
		} elseif($a->$tableprop == 'blue') {
			$score += 1;
		}
		
		$matchprop = 'd' . $day . 'match';
		if(preg_replace("/[^0-9]/", "", $a->$matchprop) < preg_replace("/[^0-9]/", "", $b->$matchprop)) {
			$score -= 2;
		} elseif(preg_replace("/[^0-9]/", "", $a->$matchprop) > preg_replace("/[^0-9]/", "", $b->$matchprop)) {
			$score += 2;
		}
		
		return $score;
	}); // sort by match no. / table (this could be done in sql)
	$start = abs(intval($_GET['start']))%count($teams);
	while($start--) { // rotate the array 'start' elements
		$teams[] = array_shift($teams);
	}
	echo json_encode($teams);
?>
