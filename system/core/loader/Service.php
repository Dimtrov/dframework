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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.3
 */

namespace dFramework\core\loader;

use dFramework\core\event\EventManager;
use dFramework\core\http\Input;
use dFramework\core\http\Redirection;
use dFramework\core\http\Response;
use dFramework\core\http\ResponseEmitter;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Uri;
use dFramework\core\output\Cache;
use dFramework\core\output\Language;
use dFramework\core\router\RouteCollection;
use dFramework\core\utilities\Helpers;
use dFramework\core\utilities\Timer;

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
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
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
     * Response Emitter to the browser
     *
     * @param boolean $shared
     * @return \dFramework\core\http\ResponseEmitter
     */
    public static function emitter(bool $shared = true)
    {
        if (true === $shared) 
        {
            return Injector::singleton(ResponseEmitter::class);
        }

        return Injector::factory(ResponseEmitter::class);
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
            return Injector::singleton(Input::class);
        }

        return Injector::factory(Input::class);
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
            return Injector::singleton(ServerRequest::class);
        }

        return Injector::factory(ServerRequest::class);
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
            return Injector::singleton(Response::class);
        }

        return Injector::factory(Response::class);
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
            return Injector::singleton(Redirection::class);
        }

        return Injector::factory(Redirection::class);
    }

    /**
	 * The URI class provides a way to model and manipulate URIs.
     * 
     * @param boolean $shared
     * @return \dFramework\core\http\Uri
     */
    public static function uri(bool $shared = true)
    {
        if (true === $shared) 
        {
            return Injector::singleton(Uri::class);
        }

        return Injector::factory(Uri::class);
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
            return Injector::singleton(Cache::class);
        }

        return Injector::factory(Cache::class);
    }

    /**
     * @param boolean $shared
     * @return \dFramework\core\utilities\Helpers
     */
    public static function helpers(bool $shared = true)
    {
        if (true === $shared) 
        {
            return Injector::singleton(Helpers::class);
        }

        return Injector::factory(Helpers::class);
    }

	/**
	 * Responsible for loading the language string translations.
	 *
	 * @param string  $locale
	 * @param bool $shared
	 * @return \dFramework\core\output\Language
	 */
	public static function language(string $locale = null, bool $shared = true)
	{
		if (true === $shared)
		{
            return Injector::singleton(Language::class)->setLocale($locale);
        }
        
        return Injector::factory(Language::class)->setLocale($locale);
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
            return Injector::singleton(RouteCollection::class);
        }

        return Injector::factory(RouteCollection::class);
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
            return Injector::singleton(EventManager::class);
        }

        return Injector::factory(EventManager::class);
    }

    /**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 *
	 * @param boolean $shared
	 *
	 * @return \dFramework\core\utilities\Timer
	 */
    public static function timer(bool $shared = true)
    {
        if (true === $shared) 
        {
            return Injector::singleton(Timer::class);
        }

        return Injector::factory(Timer::class);
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
            return Injector::factory($name, $arguments);
        }

        return Injector::singleton($name);
	}
}
