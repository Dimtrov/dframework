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

namespace dFramework\core\debug\toolbar\collectors;

use dFramework\core\loader\Service;
use dFramework\core\router\Dispatcher;

/**
 * Routes
 *
 * Routes collector for debug toolbar
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Debug/toolbar
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit		CodeIgniter 4.0 - CodeIgniter\Debug\Toolbar\Collectors\Routes
 * @file		/system/core/debug/toolbar/collectors/Routes.php
 */
class Routes extends BaseCollector
{

	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Routes';

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public function display(): array
	{
		$rawRoutes = Service::routes(true);
		$router    = Service::router(null, null, true);

		/*
		 * Matched Route
		 */
		$route = $router->getMatchedRoute();

		$controllerName = Dispatcher::getClass().'Controller';
		$methodName = Dispatcher::getMethod();

		// Get our parameters
		// Closure routes
		if (is_callable($router->controllerName()))
		{
			$method = new \ReflectionFunction(!empty($controllerName) ? $controllerName : $router->controllerName());
		}
		else
		{
			try
			{
				$method = new \ReflectionMethod(!empty($controllerName) ? $controllerName : $router->controllerName(), !empty($methodName) ? $methodName : $router->methodName());
			}
			catch (\ReflectionException $e)
			{
				// If we're here, the method doesn't exist
				// and is likely calculated in _remap.
				$method = new \ReflectionMethod(!empty($controllerName) ? $controllerName : $router->controllerName(), '_remap');
			}	
		}

		$rawParams = $method->getParameters();

		$params = [];
		foreach ($rawParams as $key => $param)
		{
			$params[] = [
				'name'  => $param->getName(),
				'value' => $router->params()[$key] ??
					'&lt;empty&gt;&nbsp| default: ' . var_export($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null, true),
			];
		}

		$matchedRoute = [
			[
				'directory'  => $router->directory(),
				'controller' => !empty($controllerName) ? $controllerName : $router->controllerName(),
				'method'     => !empty($methodName) ? $methodName : $router->methodName(),
				'paramCount' => count($router->params()),
				'truePCount' => count($params),
				'params'     => $params ?? [],
			],
		];

		/*
		* Defined Routes
		*/
		$routes    = [];
		$methods    = [
			'get',
			'head',
			'post',
			'patch',
			'put',
			'delete',
			'options',
			'trace',
			'connect',
			'cli',
		];

		foreach ($methods as $method)
		{
			$raw = $rawRoutes->getRoutes($method, true);
			
			foreach ($raw as $route => $handler)
			{
				$tab = [
					'method' => strtoupper($method),
					'route' => $route,    
					'name' => '',
					'handler' => ''
				];
				// filter for strings, as callbacks aren't displayable
				if (is_string($handler))
				{
					$tab['handler'] = $handler;
				}
				if(is_array($handler))
				{
					$tab['handler'] = is_string($handler['handler']) ? $handler['handler'] : 'Closure';
					$tab['name'] = $handler['name'];
				}
				$routes[] = $tab;
			}
		}

		return [
			'matchedRoute' => $matchedRoute,
			'routes'       => $routes,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a count of all the routes in the system.
	 *
	 * @return integer
	 */
	public function getBadgeValue(): int
	{
		$rawRoutes = Service::routes(true);

		return count($rawRoutes->getRoutes());
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFDSURBVEhL7ZRNSsNQFIUjVXSiOFEcuQIHDpzpxC0IGYeE/BEInbWlCHEDLsSiuANdhKDjgm6ggtSJ+l25ldrmmTwIgtgDh/t37r1J+16cX0dRFMtpmu5pWAkrvYjjOB7AETzStBFW+inxu3KUJMmhludQpoflS1zXban4LYqiO224h6VLTHr8Z+z8EpIHFF9gG78nDVmW7UgTHKjsCyY98QP+pcq+g8Ku2s8G8X3f3/I8b038WZTp+bO38zxfFd+I6YY6sNUvFlSDk9CRhiAI1jX1I9Cfw7GG1UB8LAuwbU0ZwQnbRDeEN5qqBxZMLtE1ti9LtbREnMIuOXnyIf5rGIb7Wq8HmlZgwYBH7ORTcKH5E4mpjeGt9fBZcHE2GCQ3Vt7oTNPNg+FXLHnSsHkw/FR+Gg2bB8Ptzrst/v6C/wrH+QB+duli6MYJdQAAAABJRU5ErkJggg==';
	}
}
