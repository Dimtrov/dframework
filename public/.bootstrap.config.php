<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
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


return [
    /*
    |--------------------------------------------------------------------------
    |SYSTEM PATH
    |--------------------------------------------------------------------------
    |
    | Specifie le repertoire où sont stockés les fichiers du framework
    */
    'system_path'            => '../system',
    
    /*
    |--------------------------------------------------------------------------
    |APPLICATION FOLDER
    |--------------------------------------------------------------------------
    |
    | Specifie le repertoire où sont stockés les fichiers de votre application
    */
    'application_folder'     => '../app',

    /*
    |--------------------------------------------------------------------------
    |COMPOSER AUTOLOAD FILE
    |--------------------------------------------------------------------------
    |
    | Specifie le repertoire votre dossier de dependances installées via composer "vendor"
    */
    'composer_autoload_file' => '../vendor'
];
