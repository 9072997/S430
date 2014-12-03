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

CREATE TABLE pages (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	weight INT,
	query TEXT,
	prow INT,
	ptable VARCHAR(255)
);

CREATE TABLE columns (
	id SERIAL PRIMARY KEY,
	page VARCHAR(255)
		REFERENCES pages (name)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	coutput INT,
	cname VARCHAR(255),
	weight INT,
	cmodify TEXT
);

CREATE TABLE users (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	password VARCHAR(255)
);
