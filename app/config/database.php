<?php

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
|	['sgbd'] The database driver. e.g.: mysql.
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
    'dbms'      => 'mysql',
    'port'      => '3306',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test',
    'debug'     => 'auto',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => '',
    'options'   => [
        'column_case' => 'inherit'
    ]
];

/*
$database['second_db'] = [
    'dbms'      => 'mysql',
    'port'      => '3306',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'dot',
    'debug'     => 'auto',
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