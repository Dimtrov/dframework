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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.1
 */

namespace dFramework\core\loader;

use dFramework\core\http\Request;
use dFramework\core\http\Response;
use dFramework\core\http\Uri;
use dFramework\core\output\Cache;
use dFramework\core\output\Language;
use dFramework\core\utilities\Helpers;

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
     * @return DI\Container
     */
    public static function container()
    {
        return Injector::container();
    }

    /**
	 * The Request class models an HTTP request.
     * 
     * @param boolean $shared
     * @return dFramework\core\http\Request
     */
    public static function request(bool $shared = true)
    {
        if (true === $shared) 
        {
            return Injector::singleton(Request::class);
        }

        return Injector::factory(Request::class);
    }
    /**
	 * The Response class models an HTTP response.
     * 
     * @param boolean $shared
     * @return dFramework\core\http\Response
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
	 * The URI class provides a way to model and manipulate URIs.
     * 
     * @param boolean $shared
     * @return dFramework\core\http\Uri
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
     * @return dFramework\core\output\Cache
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
     * @return dFramework\core\utilities\Helpers
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
}
