<?php

define('BASEPATH', dirname(SYST_DIR).DS);

define('ROOTPATH', dirname(WEBROOT).DS);


if (!defined('CONTROLLER_DIR'))
{
	/**
	 * Controllers directory path
	 */
	define('CONTROLLER_DIR', APP_DIR . 'controllers' . DS);
}

if (!defined('ENTITY_DIR'))
{
	/**
	 * Entites directory path
	 */
	define('ENTITY_DIR', APP_DIR . 'entities' . DS);
}

if (!defined('HELPER_DIR'))
{
	/**
	 * Helpers directory path
	 */
	define('HELPER_DIR', APP_DIR . 'helpers' . DS);
}

if (!defined('LIBRARY_DIR'))
{
	/**
	 * Libraries directory path
	 */
	define('LIBRARY_DIR', APP_DIR . 'libraries' . DS);
}

if (!defined('MIDDLEWARE_DIR'))
{
	/**
	 * Middlewares directory path
	 */
	define('MIDDLEWARE_DIR', APP_DIR . 'middlewares' . DS);
}

if (!defined('MODEL_DIR'))
{
	/**
	 * Models directory path
	 */
	define('MODEL_DIR', APP_DIR . 'models' . DS);
}

if (!defined('RESOURCE_DIR'))
{
	/**
	 * Resources directory path
	 */
	define('RESOURCE_DIR', APP_DIR . 'resources' . DS);
}

if (!defined('SERVICE_DIR'))
{
	/**
	 * Services directory path
	 */
	define('SERVICE_DIR', APP_DIR . 'services' . DS);
}


if (!defined('LOG_DIR'))
{
	/**
	 * Application logs files storage path
	 */
	define('LOG_DIR', STORAGE_DIR . 'logs' . DS);
}

if (!defined('DATABASE_DIR'))
{
	/**
	 * Database storage directory path
	 */
	define('DATABASE_DIR', STORAGE_DIR . 'database' . DS);
}

if (!defined('DB_MIGRATION_DIR'))
{
	/**
	 * Database migrations storage path
	 */
	define('DB_MIGRATION_DIR', DATABASE_DIR . 'migrations' . DS);
}

if (!defined('DB_SEED_DIR'))
{
	/**
	 * Database seeds storage path
	 */
	define('DB_SEED_DIR', DATABASE_DIR . 'seeds' . DS);
}

if (!defined('DB_DUMP_DIR'))
{
	/**
	 * Database backup storage path
	 */
	define('DB_DUMP_DIR', DATABASE_DIR . 'dump' . DS);
}

if (!defined('DB_CACHE_DIR'))
{
	/**
	 * Database cache directory path
	 */
	define('DB_CACHE_DIR', DATABASE_DIR . 'cache' . DS);
}

if (!defined('VIEW_DIR'))
{
	/**
	 * Views directory path
	 */
	define('VIEW_DIR', APP_DIR . 'views' . DS);
}

if (!defined('LAYOUT_DIR'))
{
	/**
	 * Layouts directory path
	 */
	define('LAYOUT_DIR', VIEW_DIR . 'layouts' . DS);
}

/**
 * Defines a constant for framework's classes directories
 */
define("CLASSES", serialize([SYST_DIR . 'core']));
