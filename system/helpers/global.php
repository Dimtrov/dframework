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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

use dFramework\core\Config;
use dFramework\core\exception\Errors;
use dFramework\core\http\Input;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Uri;
use dFramework\core\loader\Load;
use dFramework\core\loader\Service;
use dFramework\core\security\Session;
use dFramework\core\utilities\Helpers;
use Kint\Kint;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Http\Message\ResponseInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * dFramework System Helpers
 *
 * @package		dFramework
 * @subpackage	Helpers
 * @category	Global
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
     * @param mixed $default
     * @return string Environment variable setting.
     */
    function env(string $key, $default = null)
    {
        return Helpers::env($key, $default);
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
     * @return mixed
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
     * @param array|null $data
     * @param array|null $filters
     * @return mixed
     */
    function cookie($index = null, ?array $data = null, ?array $filters = [])
    {
        return Input::instance()->cookie($index, $data, $filters);
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


if (!function_exists('on_dev'))
{
	/**
	 * On dev environment
	 *
	 * Test to see if we are in development environment
	 *
	 * @return 	bool
	 */
	function on_dev() : bool
	{
		$env = config('general.environment');

		return in_array($env, ['dev', 'development']);
	}
}

if (!function_exists('is_cli'))
{
	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return 	bool
	 */
	function is_cli() : bool
	{
		return (PHP_SAPI === 'cli' OR defined('STDIN'));
	}
}

if (!function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string $version
	 * @return	bool
	 */
	function is_php(string $version) : bool
	{
		return Helpers::is_php($version);
	}
}

if (!function_exists('is_windows'))
{
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function is_windows() : bool
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}

if (!function_exists('is_https'))
{
    /**
     * Determines if the application is accessed via an encrypted * (HTTPS) connection.
     *
     * @return	bool
     */
    function is_https() : bool
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
    function is_localfile(string $name) : bool
    {
        return Helpers::is_localfile($name);
    }
}

if (!function_exists('is_online'))
{
    /**
     * Test if a application is running in local or online
     *
     * @return bool
     */
    function is_online() : bool
    {
        return Helpers::is_online();
    }
}

if (!function_exists('is_connected'))
{
    /**
     * Test if user has an acctive internet connection.
     *
     * @return bool
     */
    function is_connected() : bool
    {
        return Helpers::is_connected();
    }
}

if (!function_exists('is_ajax_request'))
{
    /**
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
     *
     * @return    bool
     */
    function is_ajax_request() : bool
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
	 * @param	string|null	$protocol
	 * @return	string
	 */
    function site_url($uri = '', ?string $protocol = NULL) : string
    {
        return Helpers::site_url($uri, $protocol);
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
     * @param	string|null	$protocol
     * @return	string
     */
    function base_url($uri = '', ?string $protocol = NULL) : string
    {
        return Helpers::base_url($uri, $protocol);
    }
}

if (!function_exists('current_url'))
{
    /**
	 * Current URL
	 *
	 * Returns the full URL (including segments) of the page where this
	 * function is placed
	 *
	 * @param boolean $returnObject True to return an object instead of a strong
	 *
	 * @return string|\dFramework\core\http\Uri
	 */
	function current_url(bool $returnObject = false, ?ServerRequest $request = null)
	{
        $request ??= Service::request();
        $path = $request->getPath();

        $base_url = dirname(substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME']))));
        $path = trim(str_replace($base_url, '', $path), '/');

        // Ajouter des chaine de requêtes et des fragments
        if ($query = $request->getUri()->getQuery()) {
            $path .= '?' . $query;
        }
        if ($fragment = $request->getUri()->getFragment()) {
            $path .= '#' . $fragment;
        }

        $uri = Config::getUri($path);

        return $returnObject ? $uri : (string)$uri->setQuery('');
	}
}

if (!function_exists('previous_url'))
{
	/**
	 * Returns the previous URL the current visitor was on. For security reasons
	 * we first check in a saved session variable, if it exists, and use that.
	 * If that's not available, however, we'll use a sanitized url from $_SERVER['HTTP_REFERER']
	 * which can be set by the user so is untrusted and not set by certain browsers/servers.
	 *
	 * @param boolean $returnObject
	 *
	 * @return \dFramework\core\http\Uri|mixed|string
	 */
	function previous_url(bool $returnObject = false)
	{
		// Grab from the session first, if we have it,
		// since it's more reliable and safer.
		// Otherwise, grab a sanitized version from $_SERVER.
		$referer = $_SESSION['_df_previous_url'] ?? null;
		if (false === filter_var($referer, FILTER_VALIDATE_URL))
		{
			$referer = Service::request()->getHeaderLine('HTTP_REFERER');
		}

		$referer = $referer ?? site_url('/');

		return $returnObject ? Service::uri($referer) : $referer;
	}
}

if (!function_exists('link_active'))
{
    /**
     * Lien actif dans la navbar
     * Un peut comme le router-active-link de vuejs
     *
     * @param string|string[] $path
     */
    function link_active($path, string $active_class = 'active', bool $exact = false): string
    {
        $base_url        = dirname(substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME']))));
        $current_section = trim(str_replace($base_url, '', $_SERVER['REQUEST_URI']), '/');

        $is_active   = false;
        $current_url = trim(current_url(false), '/');

        foreach ((array) $path as $value)
        {
            if ($current_section === $value)
			{
                $is_active = true;
                break;
            }
            if (! $exact && preg_match('#^' . $value . '/?#i', $current_section))
            {
                $is_active = true;
                break;
            }
            if (trim(link_to($value), '/') === $current_url)
            {
                $is_active = true;
                break;
            }
        }

        return $is_active ? $active_class : '';
    }
}

if (!function_exists('redirect'))
{
    /**
     * Redirect user
     *
     * @param    string $uri
     * @param    string $method
     * @param    int|null $code
     * @return    void
     */
    function redirect(string $uri = '', string $method = 'location', ?int $code = 302)
    {
        Service::response()->redirect($uri, $method, $code);
    }
}

if (! function_exists('redirection'))
{
	/**
	 * Convenience method that works with the current global $request and
	 * $router instances to redirect using named/reverse-routed routes
	 * to determine the URL to go to. If nothing is found, will treat
	 * as a traditional redirect and pass the string in, letting
	 * $redirection->redirect() determine the correct method and code.
	 *
	 * If more control is needed, you must use $response->redirect explicitly.
	 *
	 * @param string $uri
	 *
	 * @return \dFramework\core\Http\Redirection|void
	 */
	function redirection(string $uri = null)
	{
		$redirection = Service::redirection();

		if (! empty($uri))
		{
			return $redirection->route($uri);
		}

		return $redirection;
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
	 * @return string
	 */
	function link_to(string $method, ...$params) : string
	{
		$url = Service::routes()->reverseRoute($method, ...$params);
        if (empty($url))
		{
			$url = '';
		}
		return site_url($url);
	}
}

if (!function_exists('clean_url'))
{
    /**
     * @param string $url
     * @return string
     */
    function clean_url(string $url) : string
    {
        return Helpers::clean_url($url);
    }
}


// =========================== FONCTIONS DE PREVENTION D'ATTAQUE =========================== //


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
        return Helpers::esc($data, $context, $encoding);
	}
}

if (!function_exists('h')) {
    /**
     * Convenience method for htmlspecialchars.
     *
     * @param mixed $text Text to wrap through htmlspecialchars. Also works with arrays, and objects.
     *    Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
     *    implement a `__toString` method. Otherwise the class name will be used.
     *    Other scalar types will be returned unchanged.
     * @param bool $double Encode existing html entities.
     * @param string|null $charset Character set to use when escaping. Defaults to config value in `mb_internal_encoding()`
     * or 'UTF-8'.
     * @return mixed Wrapped text.
     */
    function h($text, bool $double = true, ?string $charset = null)
    {
		return Helpers::h($text, $double, $charset);
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
        return Helpers::purify($dirty_html, $config);
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
	function remove_invisible_characters(string $str, bool $url_encoded = true) : string
	{
		return Helpers::remove_invisible_characters($str, $url_encoded);
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
	function stringify_attributes($attributes, bool $js = false) : string
	{
        return Helpers::stringify_attributes($attributes, $js);
	}
}


// ================================= FONCTIONS DE DEBOGAGE ================================= //


if (!function_exists('deprecationWarning'))
{
    /**
     * Helper method for outputting deprecation warnings
     *
     * @param string $message The message to output as a deprecation warning.
     * @param int $stackFrame The stack frame to include in the error. Defaults to 1
     *   as that should point to application/plugin code.
     * @return void
     */
    function deprecationWarning(string $message, int $stackFrame = 1)
    {
        if (!(error_reporting() & E_USER_DEPRECATED))
		{
            return;
        }

        $trace = debug_backtrace();
        if (isset($trace[$stackFrame]))
		{
            $frame = $trace[$stackFrame];
            $frame += ['file' => '[internal]', 'line' => '??'];

            $message = sprintf(
                '%s - %s, line: %s' . "\n" .
                ' You can disable deprecation warnings by setting `Error.errorLevel` to' .
                ' `E_ALL & ~E_USER_DEPRECATED` in your config/app.php.',
                $message,
                $frame['file'],
                $frame['line']
            );
        }

        trigger_error($message, E_USER_DEPRECATED);
    }
}

if (!function_exists('logger'))
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
	 * @param string|int $level
	 * @param string     $message
	 * @param array|null $context
	 *
	 * @return \dFramework\core\exception\Logger|mixed
	 */
	function logger($level = null, ?string $message = null, ?string $file = null, ?int $line = null)
	{
		$logger = Service::logger();

		if (!empty($level) AND !empty($message))
		{
			return $logger->write($level, $message, $file, $line);
		}

		return $logger;
	}
}

if (!function_exists('pr'))
{
    /**
     * print_r() convenience function.
     *
     * In terminals this will act similar to using print_r() directly, when not run on cli
     * print_r() will also wrap <pre> tags around the output of given variable. Similar to debug().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     */
    function pr($var)
    {
        $template = (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') ? '<pre class="pr">%s</pre>' : "\n%s\n\n";
        printf($template, trim(print_r($var, true)));

        return $var;
    }
}

if (!function_exists('pj'))
{
    /**
     * json pretty print convenience function.
     *
     * In terminals this will act similar to using json_encode() with JSON_PRETTY_PRINT directly, when not run on cli
     * will also wrap <pre> tags around the output of given variable. Similar to pr().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     * @see pr()
     */
    function pj($var)
    {
        $template = (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') ? '<pre class="pj">%s</pre>' : "\n%s\n\n";
        printf($template, trim(json_encode($var, JSON_PRETTY_PRINT)));

        return $var;
    }
}

if (!function_exists('triggerWarning'))
{
    /**
     * Triggers an E_USER_WARNING.
     *
     * @param string $message The warning message.
     * @return void
     */
    function triggerWarning(string $message)
    {
        $stackFrame = 1;
        $trace = debug_backtrace();
        if (isset($trace[$stackFrame]))
		{
            $frame = $trace[$stackFrame];
            $frame += ['file' => '[internal]', 'line' => '??'];
            $message = sprintf(
                '%s - %s, line: %s',
                $message,
                $frame['file'],
                $frame['line']
            );
        }
        trigger_error($message, E_USER_WARNING);
    }
}

if (!function_exists('vd'))
{
	/**
	 * Shortcut to ref, HTML mode
	 *
	 * @param   mixed $args
	 * @return  void|string
	 */
	function vd()
	{
		$params = func_get_args();
		return 	Helpers::r(...$params);
  	}
}

if (!function_exists('vdt'))
{
	/**
	 * Shortcut to ref, plain text mode
	 *
	 * @param   mixed $args
	 * @return  void|string
	 */
	function vdt()
	{
		$params = func_get_args();
		return 	Helpers::rt(...$params);
  	}
}


// ================================= FONCTIONS DIVERSES ================================= //


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
            $request->getUri()->getPath(), // Absolute URIs should use a "/" for an empty path
            $request->getUri()->getQuery(),
            $request->getUri()->getFragment()
		);

		// Set an HSTS header
		$response->header('Strict-Transport-Security', 'max-age=' . $duration);
		$response->redirect($uri);
		exit(1);
	}
}

if (!function_exists('getTypeName'))
{
    /**
     * Returns the objects class or var type of it's not an object
     *
     * @param mixed $var Variable to check
     * @return string Returns the class name or variable type
     */
    function getTypeName($var) : string
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }
}

if (!function_exists('ip_address'))
{
    /**
     * Return IP Address of current user
     *
     * @return    string
     */
    function ip_address() : string
    {
        return (string) Service::request()->clientIp();
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
	function is_really_writable(string $file) : bool
	{
		return Helpers::is_really_writable($file);
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

if (!function_exists('namespaceSplit'))
{
    /**
     * Split the namespace from the classname.
     *
     * Commonly used like `list($namespace, $className) = namespaceSplit($class);`.
     *
     * @param string $class The full class name, ie `Cake\Core\App`.
     * @return array Array with 2 indexes. 0 => namespace, 1 => classname.
     */
    function namespaceSplit(string $class) : array
    {
        $pos = strrpos($class, '\\');
        if ($pos === false)
		{
            return ['', $class];
        }

        return [substr($class, 0, $pos), substr($class, $pos + 1)];
    }

}

if (!function_exists('view_exist'))
{
    /**
     * Verifie si un fichier de vue existe. Utile pour limiter les failles include
     *
     * @param string $name
     * @param string $ext
     * @return boolean
     */
    function view_exist(string $name, string $ext = '.php') : bool
    {
		$ext = str_replace('.', '', $ext);
		$name = str_replace(VIEW_DIR, '', $name);
		$name = preg_match('#\.'.$ext.'$#', $name) ? $name : $name.'.'.$ext;

        return is_file(VIEW_DIR.rtrim($name, DS));
    }
}

if (!function_exists('view'))
{
	/**
     * Charge une vue
     *
     * @param string $view
     * @param array|null $data
     * @param array|null $options
     * @param array|null $config
     * @return \dFramework\core\output\View
     */
    function view(string $view, ?array $data = [], ?array $options = [], ?array $config = [])
    {
        $object = Service::viewer(false);
		$object->addData($data)->addConfig($config)->setOptions($options);

        return $object->display($view);
    }
}

if (!function_exists('flash'))
{
    /**
     * Fournisseur d'acces rapide a la classe PHP Flash
     *
     * @return FlashMessages|string
     */
    function flash()
    {
		/** @var FlashMessages $flash  */
		$flash = service(FlashMessages::class);

		$params = func_get_args();
		$type = array_shift($params);

		if (!empty($type))
		{
			if (empty($params))
			{
				if ($type === 'all')
				{
					$type = null;
				}
				return $flash->display($type, false);
			}

			$message = array_shift($params);

			return $flash->add($message, $type, ...$params);
		}

		return $flash;
    }
}

if (!function_exists('geo_ip'))
{
	/**
	 * Recuperation des coordonnees (pays, ville, etc) d'un utilisateur en fonction de son ip
	 *
	 * @param string|null $ip
	 * @return array|null
	 */
	function geo_ip(?string $ip = null) : ?array
	{
		return json_decode(file_get_contents('http://ip-api.com/json/'.$ip), true);
	}
}

if (!function_exists('to_stream'))
{
	/**
	 * Create a new stream based on the input type.
	 *
	 * Options is an associative array that can contain the following keys:
	 * - metadata: Array of custom metadata.
	 * - size: Size of the stream.
	 *
	 * @param resource|string|null|int|float|bool|\Psr\Http\Message\StreamInterface|callable|\Iterator $resource Entity body data
	 * @param array $options  Additional options
	 *
	 * @uses GuzzleHttp\Psr7\stream_for
	 * @return \Psr\Http\Message\StreamInterface
	 * @throws \InvalidArgumentException if the $resource arg is not valid.
	 */
	function to_stream($resource = '', array $options = [])
	{
		return stream_for($resource, $options);
	}
}

if (! function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('with'))
{
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}
