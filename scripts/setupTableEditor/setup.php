#!/usr/bin/env php
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
	
	require_once(dirname(__FILE__) . '/../../includes/db.inc.php');
	
	if((isset($argv[1]) && $argv[1] == 'new') | db1('SELECT NOT EXISTS(SELECT 1 from INFORMATION_SCHEMA.TABLES WHERE table_schema=\'public\') as match')->match) {
		db0('DROP SCHEMA public CASCADE');
		db0('CREATE SCHEMA public');
		$dbObject->exec(file_get_contents(dirname(__FILE__) . '/../../SQL/tableEditor.sql'));
		$dbObject->exec(file_get_contents(dirname(__FILE__) . '/../../SQL/structure.sql'));
		db0('INSERT INTO users(name, password) VALUES (\'admin\', \'frc\')'); // sets default password to frc, you should change is as soon as setup is done
	} else {
		db0('TRUNCATE columns');
		db0('DELETE FROM pages');
	}
	require_once(dirname(__FILE__) . '/generateSchema.php');
	db0('UPDATE pages SET query=\'SELECT id, name, \'\'HIDDEN\'\' FROM users\' WHERE ptable=\'users\''); // prevents passwords from becomeing visible
?>
