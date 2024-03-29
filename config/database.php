<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - database.php
 */

use Illuminate\Support\Str;

return [

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => "neo_ooh",

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => [
		'neo_ooh' => [
			'driver'         => 'mysql',
			'host'           => env('DB_OOH_HOST'),
			'port'           => env('DB_OOH_PORT'),
			'database'       => env('DB_OOH_DATABASE'),
			'username'       => env('DB_OOH_USERNAME'),
			'password'       => env('DB_OOH_PASSWORD'),
			'charset'        => 'utf8mb4',
			'collation'      => 'utf8mb4_unicode_ci',
			'prefix'         => '',
			'prefix_indexes' => true,
			'strict'         => false,
			'engine'         => null,
			'options'        => extension_loaded('pdo_mysql') ? array_filter([
				                                                                 PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
			                                                                 ]) : [],
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer body of commands than a typical key-value system
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => [

		'client' => env('REDIS_CLIENT', 'phpredis'),

		'options' => [
			'cluster' => env('REDIS_CLUSTER', 'redis'),
			'prefix'  => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
		],

		'default' => [
			'host'     => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD'),
			'port'     => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_QUEUES_DB', '0'),
		],

		'cache' => [
			'host'     => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD'),
			'port'     => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_CACHE_DB', '1'),
		],

		'geocoding' => [
			'host'     => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD'),
			'port'     => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_GEOCODING_DB', '2'),
		],

		'dynamics' => [
			'host'     => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD'),
			'port'     => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_DYNAMICS_DB', '3'),
		],
	],

];
