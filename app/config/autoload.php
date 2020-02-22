<?php

/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the mail library is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Libraries
| 2. Helpers
| 3. Models
|
*/


/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in system/libraries/ or your
| app/libraries/ directory, with the addition of the
| 'database' library, which is somewhat of a special case.
|
| Prototype:
|
|	$config['libraries'] = array('captcha', 'mail', 'crypto');
|
| You can also supply an alternative library name to be assigned
| in the controller:
|
|	$config['libraries'] = array('api' => 'a');
*/
$autoload['libraries'] = [];

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$config['helpers'] = array('url', 'file');
*/
$autoload['helpers'] = [];

/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$config['model'] = array('FirstModel', 'Second');
|
| You can also supply an alternative model name to be assigned
| in the controller:
|
|	$config['model'] = array('FirstModel' => 'first');
*/
$autoload['models'] = [];


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('autoload');