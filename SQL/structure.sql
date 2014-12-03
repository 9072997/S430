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
	d1match TIME,
	d1table tablecolor,
	d1score INT,
	UNIQUE (d1match, d1table),
	d2match TIME,
	d2table tablecolor,
	d2score INT,
	UNIQUE (d2match, d2table),
	d3match TIME,
	d3table tablecolor,
	d3score INT,
	UNIQUE (d3match, d3table)
);
