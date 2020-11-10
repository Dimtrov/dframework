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
 *  @version    3.2.1
 */

use dFramework\core\Config;
use dFramework\core\exception\Errors;
use dFramework\core\http\Input;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Uri;
use dFramework\core\loader\Load;
use dFramework\core\loader\Service;
use dFramework\core\router\Router;
use dFramework\core\security\Session;
use Kint\Kint;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Http\Message\ResponseInterface;

/**
 * dFramework System Helpers
 *
 * @package		dFramework
 * @subpackage	Helpers
 * @category	Helpers
 * @since 		1.0
 */


// ================================= FONCTIONS D'ACCESSIBILITE ================================= //

if (!function_exists('env')) 
{

    /**
     * Gets an environment variable from available sources, and provides emulation
     * for unsupported or inconsistent environment variables
     *
     * @param string $key Environment variable name.
     * @return string Environment variable setting.
     */
    function env(string $key)
    {
        return Service::helpers()->env($key);
    }
}

if (!function_exists('esc'))
{
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 * @return string|array
	 */
    function esc($data, ?string $context = 'html', ?string $encoding = null)
	{
        return Service::helpers()->esc($data, $context, $encoding);
	}
}

if (!function_exists('helper'))
{
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   1. app/Helpers
	 *   2. {namespace}/Helpers
	 *   3. system/Helpers
	 *
	 * @param  string|array $filenames
	 */
	function helper($filenames)
	{
        Load::helper($filenames);
	}
}

if (! function_exists('service'))
{
	/**
	 * Allows cleaner access to the Services Config file.
	 * Always returns a SHARED instance of the class, so
	 * calling the function multiple times should always
	 * return the same instance.
	 *
	 * These are equal:
	 *  - $cache = service('cache')
	 *  - $cache = \dFramework\core\loader\Service::cache();
	 *
	 * @param string $name
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	function service(string $name, ...$params)
	{
		return Service::$name(...$params);
	}
}

if (! function_exists('single_service'))
{
	/**
	 * Allow cleaner access to a Service.
	 * Always returns a new instance of the class.
	 *
	 * @param string     $name
	 * @param array|null $params
	 *
	 * @return mixed
	 */
	function single_service(string $name, ...$params)
	{
		// Ensure it's NOT a shared instance
		array_push($params, false);

		return Service::$name(...$params);
	}
}

if (! function_exists('show404'))
{
    /**
     * Show a 404 Page Not Found in browser
     *
     * @param string $message
     * @param string $heading
     * @param array $params
     * @return void
     */
	function show404(string $message = 'The page you requested was not found.', string $heading = 'Page Not Found', array $params = [])
	{
		return Errors::show404($message, $heading, $params);
	}
}

if (! function_exists('config'))
{
    /**
     * GET/SET App config
     *
     * @param string $config
     * @param mixed $value
     * @param bool $force_set
     * @return void
     */
	function config(string $config, $value = null, $force_set = false)
	{
        if (!empty($value) OR (empty($value) AND true == $force_set)) 
        {
            Config::set($config, $value);
        }
        
        return Config::get($config);
    }
}


// ================================= FONCTIONS DES MANIPULATION DE DONNEES ================================= //

if (!function_exists('cookie'))
{
    /**
     * Get/Set cookie
     *
     * @param mixed|null $index
     * @param mixed|null $value
     * @param array|null $filters
     * @return mixed
     */
    function cookie($index = null, $value = null, ?array $filters = [])
    {
        return Input::instance()->cookie($index, $value, $filters);
    }
}

if (!function_exists('get'))
{
    /**
     * Get/Set a query string parameters ($_GET)
     *
     * @param mixed|null $index
     * @param mixed|null $value
     * @param array|null $filters
     * @return mixed
     */
    function get($index = null, $value = null, ?array $filters = [])
    {
        return Input::instance()->get($index, $value, $filters);
    }
}

if (!function_exists('input'))
{
    /**
     * Get value of an request variable ($_REQUEST)
     *
     * @param mixed|null $index
     * @param array|null $filters
     * @return mixed
     */
    function input($index = null, ?array $filters = [])
    {
        return Input::instance()->var($index, $filters);
    }
}

if (!function_exists('post'))
{
    /**
     * Get/Set a form value ($_POST)
     *
     * @param mixed|null $index
     * @param mixed|null $value
     * @param array|null $filters
     * @return mixed
     */
    function post($index = null, $value = null, ?array $filters = [])
    {
        return Input::instance()->post($index, $value, $filters);
    }
}

if (!function_exists('server'))
{
    /**
     * Get value of an server variable ($_SERVER)
     *
     * @param mixed|null $index
     * @param array|null $filters
     * @return mixed
     */
    function server($index = null, ?array $filters = [])
    {
        return Input::instance()->server($index, $filters);
    }
}

if (!function_exists('session'))
{
    /**
     * Get/Set a session value ($_SESSION)
     *
     * @param mixed|null $index
     * @param mixed|null $value
     * @param array|null $filters
     * @return mixed
     */
    function session($index = null, $value = null, ?array $filters = [])
    {
        return Input::instance()->session($index, $value, $filters);
    }
}


// ================================= FONCTIONS D'ENVIRONNEMENT D'EXECUTION ================================= //

if (!function_exists('is_cli'))
{
	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return 	bool
	 */
	function is_cli()
	{
		return (PHP_SAPI === 'cli' OR defined('STDIN'));
	}
}

if (!function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string
	 * @return	bool
	 */
	function is_php($version)
	{
		return Service::helpers()->is_php($version);
	}
}

if (!function_exists('is_https'))
{
    /**
     * Determines if the application is accessed via an encrypted * (HTTPS) connection.
     *
     * @return	bool
     */
    function is_https()
    {
        return Service::request()->is('ssl');
    }
}

if (!function_exists('is_localfile'))
{
    /**
     * Verify if the file you want to access is a local file of your application or not
     *
     * @param string $name
     * @return	bool
     */
    function is_localfile(string $name)
    {
        return Service::helpers()->is_localfile($name);
    }
}

if (!function_exists('is_online'))
{
    /**
     * Test if a application is running in local or online
     * 
     * @return bool
     */
    function is_online()
    {
        return Service::helpers()->is_online();
    }
}

if (!function_exists('is_ajax_request')) 
{
    /**
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
     *
     * @return    bool
     */
    function is_ajax_request()
    {
        return Service::request()->is('ajax');
    }
}


// ================================= FONCTIONS DE MANIPULATION D'URL ================================= //

if (!function_exists('site_url'))
{
    /**
	 * Site URL
	 *
	 * Create a local URL based on your basepath. Segments can be passed via the
	 * first parameter either as a string or an array.
	 *
	 * @param	string	$uri
	 * @param	string	$protocol
	 * @return	string
	 */
    function site_url($uri = '', $protocol = NULL)
    {
        return Service::helpers()->site_url($uri, $protocol);
    }
}

if (!function_exists('base_url'))
{
    /**
     * Base URL
     *
     * Create a local URL based on your basepath.
     * Segments can be passed in as a string or an array, same as site_url
     * or a URL to a file can be passed in, e.g. to an image file.
     *
     * @param	string	$uri
     * @param	string	$protocol
     * @return	string
     */
    function base_url($uri = '', $protocol = NULL)
    {
        return Service::helpers()->base_url($uri, $protocol);
    }
}

if (!function_exists('current_url')) 
{
    /**
     * Current URL
     * Returns the full URL (including segments) of the page where this
	 * function is placed
     *
     * @param string $url
	 * @param bool $with_base
     * @return    string
     */
    function current_url($url = '', bool $with_base = true)
    {
		$current_url = Service::request()->getRequestTarget().$url;
		
		if (true === $with_base)
		{
			$current_url = site_url($current_url);
		}

		return $current_url;
    }
}

if (!function_exists('redirect')) 
{
    /**
     * Redirect user
     *
     * @param    string $uri
     * @param    int|null $code
     * @return    void
     */
    function redirect(string $uri = '', string $method = 'location', int $code = 302)
    {
        Service::response()->redirect($uri, $method, $code);
    }
}

if (!function_exists('link_to'))
{
	/**
	 * Given a controller/method string and any params,
	 * will attempt to build the relative URL to the
	 * matching route.
	 *
	 * NOTE: This requires the controller/method to
	 * have a route defined in the routes Config file.
	 *
	 * @param string $method
	 * @param array  ...$params
	 *
	 * @return false|string
	 */
	function link_to(string $method, ...$params)
	{
		return site_url(Service::routes()->reverseRoute($method, ...$params));
	}
}


if (!function_exists('clean_url')) 
{
    /**
     * @param string $url
     * @return string
     */
    function clean_url(string $url)
    {
        return Service::helpers()->clean_url($url);
    }
}


// ================================= FONCTIONS DIVERSES ================================= //


if (!function_exists('dd'))
{
	/**
	 * Prints a Kint debug report and exits.
	 *
	 * @param array ...$vars
	 *
	 * @codeCoverageIgnore Can't be tested ... exits
	 */
	function dd(...$vars)
	{
        Kint::$aliases[] = 'dd';
		Kint::dump(...$vars);
		exit;
	}
}

if (! function_exists('force_https'))
{
	/**
	 * Used to force a page to be accessed in via HTTPS.
	 * Uses a standard redirect, plus will set the HSTS header
	 * for modern browsers that support, which gives best
	 * protection against man-in-the-middle attacks.
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
	 *
	 * @param integer           $duration How long should the SSL header be set for? (in seconds)
	 *                                    Defaults to 1 year.
	 * @param ServerRequest  $request
	 * @param dFramework\core\http\Response $response
	 *
	 * Not testable, as it will exit!
	 *
	 * @credit CodeIgniter 4.0.0
	 * @codeCoverageIgnore
	 */
	function force_https(int $duration = 31536000, ServerRequest $request = null, ResponseInterface $response = null)
	{
		if (is_null($request))
		{
			$request = Service::request();
		}
		if (is_null($response))
		{
			$response = Service::response();
		}

		if (is_cli() || $request->is('ssl'))
		{
			return;
		}

		// If the session library is loaded, we should regenerate
		// the session ID for safety sake.
		if (class_exists('Session', false))
		{
            Session::regenerate();
		}

        $baseURL = base_url();
        
		if (strpos($baseURL, 'http://') === 0)
		{
			$baseURL = (string) substr($baseURL, strlen('http://'));
		}

		$uri = Uri::createURIString(
            'https', 
            $baseURL, 
            $request->uri()->getPath(), // Absolute URIs should use a "/" for an empty path
            $request->uri()->getQuery(), 
            $request->uri()->getFragment()
		);

		// Set an HSTS header
		$response->header('Strict-Transport-Security', 'max-age=' . $duration);
		$response->redirect($uri);
		exit(1);
	}
}

if (!function_exists('ip_address')) 
{
    /**
     * Return IP Address of current user
     *
     * @return    string
     */
    function ip_address()
    {
        return Service::request()->clientIp();
    }
}

if (!function_exists('is_really_writable'))
{
	/**
	 * Tests for file writability
     *
     * @param string $file
	 * @return bool
     */
	function is_really_writable(string $file)
	{
		return Service::helpers()->is_really_writable($file);
	}
}

if (!function_exists('lang'))
{
	/**
	 * A convenience method to translate a string or array of them and format
	 * the result with the intl extension's MessageFormatter.
	 *
	 * @param string $line
	 * @param array     $args
	 * @param string    $locale
	 *
	 * @return string
	 */
	function lang(string $line, ?array $args = [], string $locale = null)
	{
		return Service::language($locale)->getLine($line, $args);
	}
}

if (!function_exists('log'))
{
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string     $level
	 * @param string     $message
	 * @param array|null $context
	 *
	 * @return mixed
	 */
	function log(string $level, string $message, array $context = [])
	{
		// @codeCoverageIgnoreStart
		//return Services::logger(true)
		//	->log($level, $message, $context);
		// @codeCoverageIgnoreEnd
	}
}

if (!function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function remove_invisible_characters(string $str, bool $url_encoded = true)
	{
		return Service::helpers()->remove_invisible_characters($str, $url_encoded);
	}
}

if (!function_exists('purify'))
{
	/**
     * Purify input using the HTMLPurifier standalone class.
     * Easily use multiple purifier configurations.
     *
     * @param string|string[]
     * @param string|false
     * @return string|string[]
     */
    function purify($dirty_html, $config = false)
    {
        return Service::helpers()->purify($dirty_html, $config);
	}
}

if (!function_exists('stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * @param mixed   $attributes string, array, object
	 * @param bool $js
	 * @return string
	 */
	function stringify_attributes($attributes, bool $js = false)
	{
        return Service::helpers()->stringify_attributes($attributes, $js);
	}
}

if (!function_exists('view_exist'))
{
    /**
     * Verifie si un fichier de vue existe. Utile pour limiter les failles include
     *
     * @param string $name
     * @return boolean
     */
    function view_exist(string $name) : bool
    {
		$name = preg_match('#\.php$#', $name) ? $name : $name.'.php';
        
        return is_file(VIEW_DIR.$name);
    }
}

if (!function_exists('flash'))
{
    /**
     * Fournisseur d'acces rapide a la classe PHP Flash
     *
     * @return FlashMessages
     */
    function flash() : FlashMessages
    {
		return New FlashMessages;
    }
}
