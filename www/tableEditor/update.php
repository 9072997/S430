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
	
	ini_set('session.use_only_cookies',0);
	session_start();
	$_SESSION['password'] = $_POST['password'];
	
	require_once(dirname(__FILE__) . '/../../includes/db.inc.php');
	
	if(db1('SELECT EXISTS(SELECT 1 FROM users WHERE password=?) as match', $_POST['password'])->match) {
		if(isset($_POST['table'])) {
			$table = $_POST['table'];
			if(isset($_POST['row'])) {
				$row =  $_POST['row'];
				// DELETE
				if(db0('DELETE FROM ' . preg_replace('/[^a-z\d]+/i', '', $table) . ' WHERE id=?', $row) === false) {
					http_response_code(500); // DB Error
				}
			} else {
				// NEW
				if(db0('INSERT INTO ' . preg_replace('/[^a-z\d]+/i', '', $table) . ' DEFAULT VALUES') === false) {
					http_response_code(500); // DB Error
				}
			}
		} else {
			if(isset($_POST['row'])) {
				$row = $_POST['row'];
				$column = $_POST['column'];
				// UPDATE or INSERT
				$sql = db1('SELECT cmodify FROM columns WHERE id=?', $column)->cmodify;
				if(isset($_POST['value'])) {
					$value = $_POST['value'];
					foreach(explode(';', $sql) as $query) {
						if(!isset($dbQueries[$query])) {
							$dbQueries[$query] = $dbObject->prepare($query);
						}
						if(strpos($query, ':row') !== false) {
							$dbQueries[$query]->bindValue(':row', $row);
						}
						if(strpos($query, ':value') !== false) {
							$dbQueries[$query]->bindValue(':value', $value);
						}
						if($dbQueries[$query]->execute() === false) {
							http_response_code(500); // DB Error
						}
					}
				} else {
					$sql = str_replace(':value', 'NULL', $sql);
					foreach(explode(';', $sql) as $query) {
						if(!isset($dbQueries[$query])) {
							$dbQueries[$query] = $dbObject->prepare($query);
						}
						if(strpos($query, ':row') !== false) {
							$dbQueries[$query]->bindValue(':row', $row);
						}
						if($dbQueries[$query]->execute() === false) {
							http_response_code(500); // DB Error
						}
					}
				}
			}
		}
	} else {
		http_response_code(403); // FORBIDEN
	}
?>
