<?php
	/* FRCS
    Copyright (C) 2014 Jon Penn

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
	
	$dbServer = 'localhost';
	$dbUser = 'postgres';
	$dbPassword = 'CHANGEME';
	$dbName = 'score';
	
	require_once(dirname(__FILE__) . '/noCache.inc.php');
	
	$dbObject = new PDO("pgsql:host=$dbServer;dbname=$dbName", $dbUser, $dbPassword);
	$dbObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	function dbQuery($sql, $prams) { // caches perpared statements
		global $dbObject;
		global $dbQueries;
		if(!isset($dbQueries[$sql])) {
			$dbQueries[$sql] = $dbObject->prepare($sql);
		}
		if($dbQueries[$sql]->execute($prams)) {
			return $dbQueries[$sql];
		} else {
			return false;
		}
	}
	
	function db($sql) {
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			return $query->fetchAll(PDO::FETCH_OBJ);
		}
	}
	
	function dba($sql) {
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			return $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	function db0($sql) {
		$prams = func_get_args();
		array_shift($prams);
		return dbQuery($sql, $prams);
	}
	
	function db1($sql) {
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			return $query->fetchObject();
		}
	}
	
	function dba1($sql) {
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			return $query->fetch(PDO::FETCH_ASSOC);
		}
	}
		
	function db4js($sql) { // don't forget to ORDER BY somthing
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			return json_encode($query->fetchAll(PDO::FETCH_NUM));
		}
	}
	
	function dp($sql) {
		$prams = func_get_args();
		array_shift($prams);
		$query = dbQuery($sql, $prams);
		if($query === false) {
			return false;
		} else {
			echo $query->fetch(PDO::FETCH_NUM)[0];
			return true;
		}
	}
?>
