<?php 
/*  
	Make db connection for the query builder
	See: https://github.com/usmanhalalit/pixie#connection
*/
new Pixie\Connection('mysql', array(
	'driver'    => 'mysql', // Db driver
	'host'      => get_config('database.host'),
	'database'  => get_config('database.name'),
	'username'  => get_config('database.username'),
	'password'  => get_config('database.password')
), 'QB');