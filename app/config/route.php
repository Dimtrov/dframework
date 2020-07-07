<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2
 */

/*
| -------------------------------------------------------------------
| ROUTE SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the routing settings of your application.
|
| For complete instructions please consult the 'Route Configuration' in User Guide.
*/


/**
 * default_controller
 *    This key indicates which controller class should be loaded if the
 *       URI contains no data. In the above example, the "Home" class would be loaded.
 * 
 * @var string
 */
$route['default_controller'] = 'Home';
/**
 * default_method 
 *    Cette cle definie la methode a utilliser par defaut en cas d'absence de segment definissant 
 *      la methode dans l'URL.
 * 
 * @var string
 */
$route['default_method'] = 'index';
/**
 * auto_route
 *    Cette cle specifie si dFramework va tenter de faire correspondre un controleur/methode a l'URL 
 *       demandee en cas d'absence de route correspondante ou stopper le processus.
 * 
 * @var bool
 */
$route['auto_route'] = true;
/**
 * placeholders
 *    Cette cle contient les caracteres generiques correspondant a des expressions regulieres qui peuvent 
 *      etre utilisees dans la definition des routes
 * 
 * @var array
 */
$route['placeholders'] = [
    'alpha'    => '[a-zA-Z]+',
    'alphanum' => '[a-zA-Z0-9]+',
    'any'      => '[^ /]+',
    'num'      => '[0-9]+',
    'slug'     => '[a-z0-9-]+',
];


/**
 * ============ DEFINISSEZ VOS REGLES DE ROUTAGE ICI ================
 */

$route['/'] = 'Home::index';

$route['blog/joe/(:num)/(:alpha)'] = ['Home::users', null, 'blog'];



/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('route');