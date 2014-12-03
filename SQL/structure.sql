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

CREATE TYPE tablecolor AS ENUM (
	'red',
	'blue'
);

CREATE TABLE teams (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	d1match INT,
	d1table tablecolor,
	d1score INT,
	d1ready BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (d1match, d1table),
	d2match INT,
	d2table tablecolor,
	d2score INT,
	d2ready BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (d2match, d2table),
	d3match INT,
	d3table tablecolor,
	d3score INT,
	d3ready BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (d3match, d3table)
);
