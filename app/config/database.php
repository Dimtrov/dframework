<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */


$database['connection'] = env('db.connection', 'default');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Configuration' in User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dbsm'] The database driver. e.g.: mysql.
|			Currently supported: mysql, sqlite
|	['port'] The port of your database server. e.g: 3306
|	['host'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to

|	['debug'] auto/TRUE/FALSE - Whether database errors should be displayed.
|               if is set as 'auto', errors should be displayed when we are development environment only
|	['charset'] The character set used in communicating with the database
|	['collation'] The character collation used in communicating with the database
|	['prefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['options'] Set many options to connect on database
|       [column_case] : upper/lower/inherit - Specifie la casse des colonnes issues de la bd
|
*/


$database['default'] = [
    'driver'    => env('db.default.driver', 'pdomysql'),
    'port'      => '3306',
    'host'      => env('db.default.hostname', 'localhost'),
    'username'  => env('db.default.username', 'root'),
    'password'  => env('db.default.password', ''),
    'database'  => env('db.default.database', 'test'),
    'debug'     => 'auto',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => '',
    'options'   => [
        'column_case' => 'inherit',
        'enable_stats' => false,
        'enable_cache' => true,
    ]
];

/*
$database['second_db'] = [
    'driver'    => 'pdomysql',
    'port'      => '3306',
    'host'      => 'localhost',
    'username'  => 'user123',
    'password'  => 'pass123',
    'database'  => 'db_test',
    'debug'     => 'false',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => '',
    'options'   => [
        'column_case' => 'upper'
    ]
];*/


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('database');
