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

namespace dFramework\core\router;

use dFramework\core\Config;
use dFramework\core\exception\RouterException;
use dFramework\core\http\ServerRequest;
use dFramework\core\loader\Service;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Parses the request URL into controller, action, and parameters. Uses the connected routes
 * to match the incoming URL string to parameters that will allow the request to be dispatched. Also
 * handles converting parameter lists into URL strings, using the connected routes. Routing allows you to decouple
 * the way the world interacts with your application (URLs) and the implementation (controllers and actions).
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Router
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       2.0
 * @credit		CodeIgniter 4.0 (CodeIgniter\Router\Router)
 * @file        /system/core/router/Router.php
 */
class Router
{
    /**
	 * @var RouteCollection
	 */
	protected $collection;
    /**
     * @var ServerRequest
     */
    protected $request;

	/**
	 * The route that was matched for this request.
	 *
	 * @var array|null
	 */
	protected $matchedRoute = null;

	/**
	 * The options set for the matched route.
	 *
	 * @var array|null
	 */
	protected $matchedRouteOptions = null;

	/**
	 * The locale that was detected in a route.
	 *
	 * @var string
	 */
	protected $detectedLocale = null;

	/**
	 * The middleware info from Route Collection
	 * if the matched route should be filtered.
	 *
	 * @var string|array
	 */
	protected $middlewareInfo;


	/**
	 * Sub-directory that contains the requested controller class.
	 * Primarily used by 'autoRoute'.
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * The name of the controller class.
	 *
	 * @var string
	 */
	protected $controller;
	/**
	 * @var string
	 */
	protected $controllerFile = '';
	/**
	 * The name of the method to use.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * An array of binds that were collected
	 * so they can be sent to closure routes.
	 *
	 * @var array
	 */
	protected $params = [];


	//--------------------------------------------------------------------

	/**
	 * Stores a reference to the RouteCollection object.
	 *
	 * @param RouteCollection $routes
	 * @param ServerRequestInterface   $request
	 */
	public function __construct(RouteCollection $routes, ServerRequest $request)
	{
        $this->collection = $routes;
    	$this->request = $request;

		$this->setController($this->collection->defaultController());
		$this->setMethod($this->collection->defaultMethod());

		$this->collection->HTTPVerb($this->request->getMethod() ?? strtolower($_SERVER['REQUEST_METHOD']));
	}


	/**
	 * Returns the name of the matched controller.
	 *
	 * @return mixed
	 */
	public function controllerName()
	{
		return str_replace('-', '', $this->controller);
	}
	/**
	 * Set controller name
	 *
	 * @param string $name
	 * @return void
	 */
	private function setController(string $name)
	{
		$this->controller = $this->makeController($name);
	}
	private function makeController(string $name) : string
	{
		return preg_replace(
			['#Controller$#', '#'.config('general.url_suffix').'$#i'],
			'',
			ucfirst($name)
		) . 'Controller';
	}
	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 *
	 * @return mixed
	 */
	public function methodName(): string
	{
		return str_replace('-', '_', $this->method);
	}
	private function setMethod(string $name)
	{
		$this->method = preg_replace('#'.config('general.url_suffix').'$#i', '', strtolower($name));
	}

	public function controllerFile() : string
	{
		return $this->controllerFile;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the binds that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * instance->method(...$params).
	 *
	 * @return mixed
	 */
	public function params(): array
	{
		return $this->params;
	}
	/**
	 * Returns the middleware info for the matched route, if any.
	 *
	 * @return string|array
	 */
	public function getMiddleware()
	{
		return $this->middlewareInfo;
	}
	/**
	 * Returns the name of the sub-directory the controller is in,
	 * if any. Relative to APPPATH.'Controllers'.
	 *
	 * Only used when auto-routing is turned on.
	 *
	 * @return string
	 */
	public function directory(): string
	{
		return !empty($this->directory) ? $this->directory : '';
	}

	/**
	 * Returns the routing information that was matched for this
	 * request, if a route was defined.
	 *
	 * @return array|null
	 */
	public function getMatchedRoute()
	{
		return $this->matchedRoute;
	}
	/**
	 * Returns all options set for the matched route
	 *
	 * @return array|null
	 */
	public function getMatchedRouteOptions()
	{
		return $this->matchedRouteOptions;
	}

	/**
	 * Returns true/false based on whether the current route contained
	 * a {locale} placeholder.
	 *
	 * @return boolean
	 */
	public function hasLocale()
	{
		return (bool) $this->detectedLocale;
	}
	/**
	 * Returns the detected locale, if any, or null.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->detectedLocale;
	}


	//--------------------------------------------------------------------

	/**
	 * @param string|null $uri
	 *
	 * @return mixed|string
	 */
	public function handle(string $uri = null)
	{
		if (empty($uri))
		{
			$uri = '/';
		}

		if ($this->checkRoutes($uri))
		{
			if ($this->collection->isFiltered($this->matchedRoute[0]))
			{
				$this->middlewareInfo = $this->collection->getFilterForRoute($this->matchedRoute[0]);
			}

			if (is_string($this->controller))
			{
				$file = CONTROLLER_DIR . $this->controllerFile . DS . $this->controller . '.php';

				if (!is_file($file))
				{
					RouterException::except(
						'Controller not found',
						'Can\'t load controller <b>'.preg_replace('#Controller$#', '',$this->controllerFile.'\\'.$this->controller).'</b>.
						The file &laquo; '.$file.' &raquo; do not exist',
						404
					);
				}

				$this->controllerFile = $file;
				include_once $this->controllerFile;
			}

			return $this->controller;
		}

		// Still here? Then we can try to match the URI against
		// Controllers/directories, but the application may not
		// want this, like in the case of API's.
		if (! $this->collection->autoRoute())
		{
			RouterException::except(
				'Aucune route trouvée',
				'Nous n\'avons trouvé aucune route correspondante à l\'URI &laquo; '.$uri.' &raquo;',
				404
			);
		}

		$this->autoRoute($uri);

		return $this->controllerName();
	}

    /**
	 * Compares the uri string against the routes that the
	 * RouteCollection class defined for us, attempting to find a match.
	 * This method will modify $this->controller, etal as needed.
	 *
	 * @param string $uri The URI path to compare against the routes
	 *
	 * @return boolean Whether the route was matched or not.
	 */
	protected function checkRoutes(string $uri): bool
	{
		$routes = $this->collection->getRoutes($this->collection->HTTPVerb());

		// Don't waste any time
		if (empty($routes))
		{
			return false;
		}
		// Loop through the route array looking for wildcards
		foreach ($routes as $key => $val)
		{
			$key = $key === '/'
			? $key
				: trim($key, '/ ');

				// Are we dealing with a locale?
			if (strpos($key, '{locale}') !== false)
			{
				$localeSegment = array_search('{locale}', preg_split('/[\/]*((^[a-zA-Z0-9])|\(([^()]*)\))*[\/]+/m', $key));

				// Replace it with a regex so it
				// will actually match.
				$key = str_replace('{locale}', '[^/]+', $key);
			}

			$key = preg_replace_callback('#{(.+)}#U', function($match) {
				preg_match('#{(?:[a-z]+)\|(.*)}#i', $match[0], $m);

				return '(' . ($m[1] ?? '[^/]+') .')';
			}, $key);

			// Does the RegEx match?
			if (preg_match('#^' . $key . '$#', $uri, $matches))
			{
				// Is this route supposed to redirect to another?
				if ($this->collection->isRedirect($key))
				{
					$val = is_string($val) ? [$val => $val] : $val;
					Service::redirection()->to(site_url(key($val)), $this->collection->redirectCode($key));
				}
				// Store our locale so CodeIgniter object can
				// assign it to the Request.
				if (isset($localeSegment))
				{
					// The following may be inefficient, but doesn't upset NetBeans :-/
					$temp                 = (explode('/', $uri));
					$this->detectedLocale = $temp[$localeSegment];
					unset($localeSegment);
				}

				// Remove the original string from the matches array
				array_shift($matches);

				$this->params = $matches;

				// Are we using Closures? If so, then we need
				// to collect the params into an array
				// so it can be passed to the controller method later.
				if (! is_string($val) AND is_callable($val))
				{
					$this->controller = $val;

					$this->matchedRoute = [
						$key,
						$val,
					];

					$this->matchedRouteOptions = $this->collection->routesOptions($key);

					return true;
				}

				// Are we using the default method for back-references?

				// Support resource route when function with subdirectory
				// ex: $routes->resource('Admin/Admins');
				if (strpos($val, '$') !== false AND strpos($key, '(') !== false AND strpos($key, '/') !== false)
				{
					$replacekey = str_replace('/(.*)', '', $key);
					$uri =
					$val        = preg_replace('#^' . $key . '$#', $val, $uri);
					//	$val        = str_replace($replacekey, str_replace('/', '\\', $replacekey), $val);

					/**
					 * @update
					 * @date 06-09-2020
					 * @author Dimitri Sitchet Tomkeu <dst@email.com>
					 */
					$parts = explode('::', $val);
					$controller = str_replace('/', '\\', array_shift($parts));
					$method = implode('', $parts);
					$val = $controller.'::'.$method;
				}
				elseif (strpos($val, '$') !== false AND strpos($key, '(') !== false)
				{
					$val = preg_replace('#^' . $key . '$#', $val, $uri);
				}
				elseif (strpos($val, '/') !== false)
				{
					$options = explode('::', $val);
					$controller = $options[0];
					$method = $options[1] ?? $this->collection->defaultMethod();

					// Only replace slashes in the controller, not in the method.
					$controller = str_replace('/', '\\', $controller);

					$val = $controller . '::' . $method;
				}

				$this->setRequest(explode('/', $val));

				$this->matchedRoute = [
					$key,
					$val,
				];

				$this->matchedRouteOptions = $this->collection->routesOptions($key);

				return true;
			}
		}

		return false   ;
	}

	/**
	 * Attempts to match a URI path against Controllers and directories
	 * found in APPPATH/Controllers, to find a matching route.
	 *
	 * @param string $uri
	 */
	public function autoRoute(string $uri)
	{
		$uri = trim($uri, '/');

		$segments = $this->validateRequest(explode('/', $uri));

		// If we don't have any segments left - try the default controller;
		// WARNING: Directories get shifted out of the segments array.
		if (empty($segments))
		{
			$this->setDefaultController();
		}
		// If not empty, then the first segment should be the controller
		else
		{
			$this->setController(array_shift($segments));
		}

		// Use the method name if it exists.
		// If it doesn't, no biggie - the default method name
		// has already been set.
		if (! empty($segments))
		{
			$this->setMethod(array_shift($segments));
		}

		if (! empty($segments))
		{
			$this->params = $segments;
		}

		// Load the file so that it's available for dFramework.
		$file = str_replace('/', DS, CONTROLLER_DIR . $this->directory . $this->controllerName() . '.php');

		if (!is_file($file))
		{
			RouterException::except(
				'Controller file not found',
                'Impossible de charger le controleur <b>'.str_replace('Controller', '', $this->controllerName()).'</b> souhaité.
                <br/>
                Le fichier &laquo; '.$file.' &raquo; n\'existe pas'
			);
		}
		$this->controllerFile = $file;

		include_once $file;
	}

	/**
	 * Attempts to validate the URI request and determine the controller path.
	 *
	 * @param array $segments URI segments
	 *
	 * @return array URI segments
	 */
	protected function validateRequest(array $segments): array
	{
		$segments = array_filter($segments, function ($segment) {
			return ! empty($segment) OR ($segment !== '0' OR $segment !== 0);
		});

		$segments = array_values($segments);
		$segments[0] = preg_replace('#'.Config::get('general.url_suffix').'$#i', '', $segments[0]);

		$c                  = count($segments);
		$directory_override = isset($this->directory);

		// Loop through our segments and return as soon as a controller
		// is found or when such a directory doesn't exist
		while ($c-- > 0)
		{
			$test = $this->directory . $this->makeController($segments[0]);

			if (!is_file(CONTROLLER_DIR . $test . '.php') AND $directory_override === false AND is_dir(CONTROLLER_DIR . $this->directory . strtolower($segments[0])))
			{
				$this->setDirectory(array_shift($segments), true);
				continue;
			}

			return $segments;
		}

		// This means that all segments were actually directories
		return $segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the sub-directory that the controller is in.
	 *
	 * @param string|null   $dir
	 * @param boolean|false $append
	 */
	public function setDirectory(string $dir = null, bool $append = false)
	{
		if (empty($dir))
		{
			$this->directory = null;
			return;
		}

		$dir = strtolower($dir);

		if ($append !== true OR empty($this->directory))
		{
			$this->directory = str_replace('.', '', trim($dir, '/')) . '/';
		}
		else
		{
			$this->directory .= str_replace('.', '', trim($dir, '/')) . '/';
		}
	}

	/**
	 * Returns the 404 Override settings from the Collection.
	 * If the override is a string, will split to controller/index array.
	 */
	public function get404Override()
	{
		$route = $this->collection->override_404();

		if (is_string($route))
		{
			$routeArray = explode('::', $route);

			return [
				$routeArray[0], // Controller
				$routeArray[1] ?? 'index',   // Method
			];
		}

		if (is_callable($route))
		{
			return $route;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Set request route
	 *
	 * Takes an array of URI segments as input and sets the class/method
	 * to be called.
	 *
	 * @param array $segments URI segments
	 */
	protected function setRequest(array $segments = [])
	{
		// If we don't have any segments - try the default controller;
		if (empty($segments))
		{
			$this->setDefaultController();

			return;
		}

		list($controller, $method) = array_pad(explode('::', $segments[0]), 2, null);

		$controller = explode('\\', $controller);

		$this->setController(array_pop($controller));

		$this->controllerFile = implode(DS, $controller);

		// $this->method already contains the default method name,
		// so don't overwrite it with emptiness.
		if (! empty($method))
		{
			$this->setMethod($method);
		}

		array_shift($segments);

		if (!empty($segments))
		{
			$this->params = $segments;
		}
	}

	/**
	 * Sets the default controller based on the info set in the RouteCollection.
	 */
	protected function setDefaultController()
	{
		if (empty($this->controller))
		{
			RouterException::except(
				'Missing default route',
				'Unable to determine what should be displayed. A default route has not been specified in the routing file.'
			);
		}

		// Is the method being specified?
		if (sscanf($this->controller, '%[^/]/%s', $class, $this->method) !== 2)
		{
			$this->method = 'index';
		}

		if (! is_file(CONTROLLER_DIR . $this->directory . $this->makeController($class) . '.php'))
		{
			return;
		}

		$this->setController($class);
	}
}
