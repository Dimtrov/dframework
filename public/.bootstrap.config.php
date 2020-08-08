<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.1
 */

/*
| -------------------------------------------------------------------
| BOOTSTRAPPING SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the boostrapping settings of your application.
|
| For complete instructions please consult the 'Bootstrap Configuration' in User Guide.
|
*/


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
$config = null;



/*
|--------------------------------------------------------------------------
|SYSTEM PATH
|--------------------------------------------------------------------------
|
| Specifie le repertoire où sont stockés les fichiers du framework
*/
$config['system_path'] = '../system';

/*
|--------------------------------------------------------------------------
|APPLICATION FOLDER
|--------------------------------------------------------------------------
|
| Specifie le repertoire où sont stockés les fichiers de votre application
*/
$config['application_folder'] = '../app';

/*
|--------------------------------------------------------------------------
|COMPOSER AUTOLOAD FILE
|--------------------------------------------------------------------------
|
| Specifie le repertoire votre dossier de dependances installées via composer "vendor"
*/
$config['composer_autoload_file'] = '../vendor';


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return $config;
