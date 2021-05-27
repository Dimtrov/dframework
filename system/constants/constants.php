<?php

define('BASEPATH', dirname(SYST_DIR).DS);

define('ROOTPATH', dirname(WEBROOT).DS);

/**
 * Application modules directories
 */


/**
 * Controllers directory path
 */
if (!defined('CONTROLLER_DIR'))
{
	define('CONTROLLER_DIR', APP_DIR . 'controllers' . DS);
}
/**
 * Entites directory path
 */
if (!defined('ENTITY_DIR'))
{
	define('ENTITY_DIR', APP_DIR . 'entities' . DS);
}
/**
 * Helpers directory path
 */
if (!defined('HELPER_DIR'))
{
	define('HELPER_DIR', APP_DIR . 'helpers' . DS);
}
/**
 * Libraries directory path
 */
if (!defined('LIBRARY_DIR'))
{
	define('LIBRARY_DIR', APP_DIR . 'libraries' . DS);
}
/**
 * Middlewares directory path
 */
if (!defined('MIDDLEWARE_DIR'))
{
	define('MIDDLEWARE_DIR', APP_DIR . 'middlewares' . DS);
}
/**
 * Models directory path
 */
if (!defined('MODEL_DIR'))
{
	define('MODEL_DIR', APP_DIR . 'models' . DS);
}
/**
 * Ressources directory path
 */
if (!defined('RESOURCE_DIR'))
{
	define('RESOURCE_DIR', APP_DIR . 'resources' . DS);
}
/**
 * Migrations directory path
 */
if (!defined('MIGRATION_DIR'))
{
	define('MIGRATION_DIR', RESOURCE_DIR . 'database' . DS . 'migrations' . DS);
}
/**
 * Views directory path
 */
if (!defined('VIEW_DIR'))
{
	define('VIEW_DIR', APP_DIR . 'views' . DS);
}
/**
 * Layouts directory path
 */
if (!defined('LAYOUT_DIR'))
{
	define('LAYOUT_DIR', VIEW_DIR . 'layouts' . DS);
}


/**
 * Defines a constant for framework's classes directories
 */
define("CLASSES", serialize([SYST_DIR . 'core']));
