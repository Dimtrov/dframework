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

/*
| -------------------------------------------------------------------
| ROUTE SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the routing settings of your application.
|
| For complete instructions please consult the 'Route Configuration' in User Guide.
*/

/**
 * Create a new instance of our RouteCollection class.
 *
 * NE PAS CHANGER CETTE VARIABLE CAR ELLE EST UTILISEE POUR  L'AMORCAGE DE VOTRE APPLICATION
 *
 * @var dFramework\core\router\RouteCollection
 */
$routes = dFramework\core\loader\Service::routes();


/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
/**
 * Set default Controller
 */
 $routes->defaultController('Home');
/**
 * Set default Method
 */
 $routes->defaultMethod('index');
/**
 * Enable auto route
 */
 $routes->autoRoute(true);


/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
