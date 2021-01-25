<?php

define('BASEPATH', dirname(SYST_DIR).DS);

define('ROOTPATH', dirname(WEBROOT).DS);

/**
 * Application modules directories
 */
define('CONTROLLER_DIR', APP_DIR . 'controllers' . DS);

define('ENTITY_DIR', APP_DIR . 'entities' . DS);

define('FILTER_DIR', APP_DIR . 'filters' . DS);

define('HELPER_DIR', APP_DIR . 'helpers' . DS);

define('LIBRARY_DIR', APP_DIR . 'libraries' . DS);

define('MODEL_DIR', APP_DIR . 'models' . DS);

define('RESOURCE_DIR', APP_DIR . 'resources' . DS);

define('VIEW_DIR', APP_DIR . 'views' . DS);

define('LAYOUT_DIR', VIEW_DIR . 'reserved' . DS . 'layouts' . DS);


/*
 * Database constants
*/
define('DF_FOBJ', PDO::FETCH_OBJ); // fetch_obj
define('DF_FARR', PDO::FETCH_ASSOC); // fetch_array
define('DF_FNUM', PDO::FETCH_NUM); // fetch_num
define('DF_FCLA', PDO::FETCH_CLASS); // fetch_class

/**
 * Defines a constant for framework's classes directories
 */
define("CLASSES", serialize([SYST_DIR . 'core']));