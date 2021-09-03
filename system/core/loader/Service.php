<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

namespace dFramework\core\loader;

use dFramework\core\db\Database;
use dFramework\core\db\query\Builder;
use dFramework\core\debug\Timer;
use dFramework\core\debug\Toolbar;
use dFramework\core\event\EventManager;
use dFramework\core\exception\Logger;
use dFramework\core\http\Input;
use dFramework\core\http\Negotiator;
use dFramework\core\http\Redirection;
use dFramework\core\http\Response;
use dFramework\core\http\ResponseEmitter;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Uri;
use dFramework\core\output\Cache;
use dFramework\core\output\Language;
use dFramework\core\output\View;
use dFramework\core\router\RouteCollection;
use dFramework\core\router\Router;
use dFramework\core\utilities\Helpers;
use DI\NotFoundException;

/**
 * Service
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by dFramework to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This is used in place of a Dependency Injection container primarily
 * due to its simplicity, which allows a better long-term maintenance
 * of the applications built on top of CodeIgniter. A bonus side-effect
 * is that IDEs are able to determine what class you are calling
 * whereas with DI Containers there usually isn't a way for them to do this.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.2.1
 * @file        /system/core/loader/Service.php
 */
class Service
{
    /**
     * @return Injector
     */
    public static function injector()
    {
        return Injector::instance();
    }

    /**
     * @return \DI\Container
     */
    public static function container()
    {
        return Injector::container();
    }


    /**
     * Query Builder
     *
     * @param string|null $group
     * @param boolean $shared
     * @return \dFramework\core\db\query\Builder
     */
    public static function builder(?string $group = null, bool $shared = true)
    {
        if (true === $shared)
        {
            return self::injector()->singleton(Builder::class);
        }

        return self::factory(Builder::class, [$group]);
    }
    /**
     * Database manager
     *
     * @param string|null $group
     * @param boolean $shared
     * @return \dFramework\core\db\Database
     */
    public static function database(?string $group = null, bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Database::class)->setGroup($group);
        }

        return self::factory(Database::class, [$group]);
    }

    /**
     * Response Emitter to the browser
     *
     * @param boolean $shared
     * @return \dFramework\core\http\ResponseEmitter
     */
    public static function emitter(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(ResponseEmitter::class);
        }

        return self::factory(ResponseEmitter::class);
    }

    /**
	 * The general Input class models an HTTP request.
     *
     * @param boolean $shared
     * @return \dFramework\core\http\Input
     */
    public static function input(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Input::class);
        }

        return self::factory(Input::class);
    }

    /**
	 * The general Input class models an HTTP request.
     *
     * @param ServerRequest|null $request
     * @param bool $shared
     * @return \dFramework\core\http\Negotiator
     */
    public static function negotiator(?ServerRequest $request = null, bool $shared = true)
    {
		if (empty($request))
		{
			$request = static::request(true);
		}
        if (true === $shared)
        {
            return self::singleton(Negotiator::class)->setRequest($request);
        }

        return self::factory(Negotiator::class, [$request]);
    }

    /**
	 * The Request class models an HTTP request.
     *
     * @param boolean $shared
     * @return \dFramework\core\http\ServerRequest
     */
    public static function request(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(ServerRequest::class);
        }

        return self::factory(ServerRequest::class);
    }

    /**
	 * The Response class models an HTTP response.
     *
     * @param boolean $shared
     * @return \dFramework\core\http\Response
     */
    public static function response(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Response::class);
        }

        return self::factory(Response::class);
    }

    /**
	 * The HTTP Redirection class.
     *
     * @param boolean $shared
     * @return \dFramework\core\http\Redirection
     */
    public static function redirection(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Redirection::class);
        }

        return self::factory(Redirection::class);
    }

    /**
	 * The URI class provides a way to model and manipulate URIs.
     *
     * @param string|null $uri
     * @param boolean $shared
     * @return \dFramework\core\http\Uri
     */
    public static function uri(?string $uri = null, bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Uri::class)->setURI($uri);
        }

        return self::factory(Uri::class, [$uri]);
    }

    /**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later
     *
     * @param boolean $shared
     * @return \dFramework\core\output\Cache
     */
    public static function cache(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Cache::class);
        }

        return self::factory(Cache::class);
    }

    /**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 *
	 * @param string  $view
	 *
	 * @return \dFramework\core\output\View
	 */
	public static function viewer(bool $shared = true)
	{
        if (true === $shared)
        {
            return self::singleton(View::class);
        }
		return self::factory(View::class);
	}

    /**
     * @param boolean $shared
     * @return \dFramework\core\utilities\Helpers
     */
    public static function helpers(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Helpers::class);
        }

        return self::factory(Helpers::class);
    }

	/**
	 * Responsible for loading the language string translations.
	 *
	 * @param string|null  $locale
	 * @param bool $shared
	 * @return \dFramework\core\output\Language
	 */
	public static function language(?string $locale = null, bool $shared = true)
	{
		if (true === $shared)
		{
            return self::singleton(Language::class)->setLocale($locale);
        }

        return self::factory(Language::class)->setLocale($locale);
    }

    /**
	 * The Routes service is a class that allows for easily building
	 * a collection of routes.
	 *
	 * @param bool $shared
	 * @return \dFramework\core\router\RouteCollection
	 */
    public static function routes(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(RouteCollection::class);
        }

        return self::factory(RouteCollection::class);
    }

    /**
	 * The Router class uses a RouteCollection's array of routes, and determines
	 * the correct Controller and Method to execute.
	 *
	 * @param \dFramework\core\router\RouteCollection $routes
	 * @param \dFramework\core\http\ServerRequest     $request
	 * @param boolean                                 $shared
	 *
	 * @return \dFramework\core\router\Router
	 */
	public static function router(?RouteCollection $routes = null, ?ServerRequest $request = null, bool $shared = true)
	{
		if (true === $shared)
		{
            return self::singleton(Router::class);
		}

		if (empty($routes))
		{
			$routes = static::routes(true);
		}
		if (empty($request))
		{
			$request = static::request(true);
		}

        return self::factory(Router::class, [$routes, $request]);
	}


    /**
     * Event Manager instance
     *
     * @param boolean $shared
     * @return \dFramework\core\event\EventManager
     */
    public static function event(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(EventManager::class);
        }

        return self::factory(EventManager::class);
    }

    /**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 *
	 * @param boolean $shared
	 *
	 * @return \dFramework\core\debug\Timer
	 */
    public static function timer(bool $shared = true)
    {
        if (true === $shared)
        {
            return self::singleton(Timer::class);
        }

        return self::factory(Timer::class);
    }

    /**
	 * Return the debug toolbar.
	 *
	 * @param boolean         $shared
	 *
	 * @return \dFramework\core\debug\Toolbar
	 */
	public static function toolbar(bool $shared = true)
	{
		if ($shared)
		{
            return self::singleton(Toolbar::class);
		}

		return self::factory(Toolbar::class);
	}

	/**
	 * Return the logger class.
	 *
	 * @param boolean         $shared
	 *
	 * @return \dFramework\core\exception\Logger
	 */
	public static function logger(bool $shared = true)
	{
		if ($shared)
		{
            return self::singleton(Logger::class);
		}

		return self::factory(Logger::class);
	}


    /**
	 * Provides the ability to perform case-insensitive calling of service
	 * names.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		if (method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		return self::discoverServices($name, $arguments);
    }

    /**
	 * Try to get service from container
	 *
	 * @param string $name
	 * @param array  $arguments
	 * @return mixed
	 */
	protected static function discoverServices(string $name, array $arguments)
	{
        $shared = array_pop($arguments);
        if ($shared !== true)
        {
			return self::discoverServiceFactory($name, $arguments);
        }

        return self::discoverServiceSingleton($name);
	}

	/**
	 * Try to find a service
	 *
	 * @param string $name
	 * @param string $arguments
	 */
	private static function discoverServiceFactory(string $name, array $arguments)
	{
		try {
			return self::factory($name, $arguments);
		}
		catch(NotFoundException $e) {
			try {
				return self::factory($name.'Service', $arguments);
			}
			catch(NotFoundException $ex) {
				throw $e;
			}
		}
	}

	/**
	 * Try to find a single service
	 *
	 * @param string $name
	 */
	private static function discoverServiceSingleton(string $name)
	{
		try {
			return self::singleton($name);
		}
		catch(NotFoundException $e) {
			try {
				return self::singleton($name.'Service');
			}
			catch(NotFoundException $ex) {
				throw $e;
			}
		}
	}


	/**
	 * Inject single instance of given class
	 *
	 * @param string $name
	 * @return mixed
	 */
	private static function singleton(string $name)
	{
		return self::injector()->get($name);
	}

	/**
	 * Inject new instance of given class
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function factory(string $name, array $arguments = [])
	{
		return self::injector()->make($name, $arguments);
	}
}
