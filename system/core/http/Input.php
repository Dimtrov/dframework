<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.2
 */

namespace dFramework\core\http;

use dFramework\core\Config;
use dFramework\core\loader\Service;
use dFramework\core\security\Session;
use dFramework\core\security\Xss;
use dFramework\core\utilities\Str;

/**
 * Input
 *
 * Pre-processes global input data for security
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file        /system/core/http/Input.php
 */
class Input
{

    /**
     * Raw input stream data
     *
     * Holds a cache of php://input contents
     *
     * @var	string
     */
    protected $_raw_input_stream;

    /**
     * Parsed input stream data
     *
     * Parsed from php://input at runtime
     *
     * @var	array
     */
    protected $_input_stream;

    /**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers = array();

    private static $_instance;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_sanitize_globals();
    }
    /**
     * Get a single instance
     *
     * @return self
     */
    public static function instance() : self
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }



    /**
     * Fetch an item from the GET array
     *
     * @param    mixed $index Index for item to be fetched from $_GET
     * @param    mixed|null $value
     * @param array|null $filter
     * @return   mixed
     */
    public function get($index = null, $value = null, ?array $filter = [])
    {
        if (is_array($index))
        {
            return $this->_find_entries($_GET, $index, $filter);
        }

        if (!empty($value))
        {
            $_GET[$index] = $value;
        }

        return $this->_fetch_from_array($_GET, $index, $filter);
    }

    /**
     * Fetch an item from the POST array
     *
     * @param    mixed $index Index for item to be fetched from $_POST
     * @param    mixed|null $value
     * @param array|null $filter
     * @return    mixed
     */
    public function post($index = null, $value = null, ?array $filter = [])
    {
        if (is_array($index))
        {
            return $this->_find_entries($_POST, $index, $filter);
        }

        if (!empty($value))
        {
            $_POST[$index] = $value;
        }

        return $this->_fetch_from_array($_POST, $index, $filter);
    }

    /**
     * Fetch an item from POST data with fallback to GET
     *
     * @param    string $index Index for item to be fetched from $_POST or $_GET
     * @param array|null $filter
     * @return    mixed
     */
    public function post_get(string $index, ?array $filter = [])
    {
        return isset($_POST[$index])
            ? $this->post($index, null, $filter)
            : $this->get($index, null, $filter);
    }

    /**
     * Fetch an item from GET data with fallback to POST
     *
     * @param    string $index Index for item to be fetched from $_GET or $_POST
     * @param array|null $filter
     * @return    mixed
     */
    public function get_post(string $index, ?array $filter = [])
    {
        return isset($_GET[$index])
            ? $this->get($index, null, $filter)
            : $this->post($index, null, $filter);
    }

    /**
     * Fetch an item from the FILE array
     *
     * @param    mixed $index Index for item to be fetched from $_FILE
     * @param array|null $filter
     * @return    mixed
     */
    public function file($index = NULL, ?array $filter = [])
    {
        return $this->_fetch_from_array($_FILES, $index, $filter);
    }

    /**
     * Fetch an item from the php://input stream
     *
     * Useful when you need to access PUT, DELETE or PATCH request data.
     *
     * @param    string $index Index for item to be fetched
     * @param array|null $filter
     * @return    mixed
     */
    public function input_stream($index = NULL, ?array $filter = [])
    {
        // Prior to PHP 5.6, the input stream can only be read once,
        // so we'll need to check if we have already done that first.
        if ( ! is_array($this->_input_stream))
        {
            // $this->raw_input_stream will trigger __get().
            parse_str($this->_raw_input_stream, $this->_input_stream);
            is_array($this->_input_stream) OR $this->_input_stream = array();
        }
        return $this->_fetch_from_array($this->_input_stream, $index, $filter);
    }

    /**
     * Fetch an item from the SERVER array
     *
     * @param    mixed $index Index for item to be fetched from $_SERVER
     * @param array|null $filter
     * @return    mixed
     */
    public function server($index, ?array $filter = [])
    {
        return $this->_fetch_from_array($_SERVER, $index, $filter);
    }

    /**
     * Fetch an item from the REQUEST array
     *
     * @param    mixed $index Index for item to be fetched from $_SERVER
     * @param array|null $filter
     * @return    mixed
     */
    public function var($index, ?array $filter = [])
    {
        return $this->_fetch_from_array($_REQUEST, $index, $filter);
    }

    /**
     * @param null $index
     * @param null $value
     * @param array|null $filter
     * @return mixed
     */
    public function session($index = null, $value = null, ?array $filter = [])
    {
        if (is_array($index))
        {
            return $this->_find_entries((array) Session::get(), $index, $filter);
        }

        if ($value === false)
        {
            Session::set($index, null);
        }
        if(!empty($value))
        {
            Session::set($index, $value);
        }

        return Session::get($index);
    }

    /**
     * @param string ...$index
     */
    public function free_session(string ...$index)
    {
        foreach ($index As $value)
        {
            Session::destroy($value);
        }
    }


    /**
     * Fetch an item from the COOKIE array
     *
     * @param    mixed $index Index for item to be fetched from $_COOKIE
     * @param array|null $data
     * @param array|null $filter
     * @return    mixed
     */
    public function cookie($index = NULL, ?array $data = [], ?array $filter = [])
    {
        if (!empty($data) AND is_array($data) AND isset($data['value']) AND !empty($index) AND is_string($index))
        {
            $this->set_cookie(
                $index,
                $data['value'],
                (int) $data['expire'] ?? 0,
                $data['domain'] ?? '',
                $data['path'] ?? '/',
                $data['prefix'] ?? '',
                $data['secure'] ?? NULL,
                $data['httponly'] ?? NULL
            );
        }

        return $this->_fetch_from_array($_COOKIE, $index, $filter);
    }
    /**
     * Set cookie
     *
     * Accepts an arbitrary number of parameters (up to 7) or an associative
     * array in the first parameter containing all the values.
     *
     * @param	string|mixed[]	$name		Cookie name or an array containing parameters
     * @param	string		$value		Cookie value
     * @param	int		$expire		Cookie expiration time in seconds
     * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
     * @param	string		$path		Cookie path (default: '/')
     * @param	string		$prefix		Cookie name prefix
     * @param	bool		$secure		Whether to only transfer cookies via SSL
     * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
     * @return	void
     */
    public function set_cookie($name, $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL)
    {
        if (is_array($name))
        {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
            {
                if (isset($name[$item]))
                {
                    $$item = $name[$item];
                }
            }
        }
        $cookies_config = Config::get('data.cookies');

        if ($prefix === '' AND !empty($cookies_config['prefix'])) {
            $prefix = $cookies_config['prefix'];
        }
        if ($domain == '' AND !empty($cookies_config['domain'])) {
            $domain = $cookies_config['domain'];
        }
        if ($path === '/' AND !empty($cookies_config['path']) AND $cookies_config['path'] !== '/') {
            $path = $cookies_config['path'];
        }

        $secure = ($secure === NULL AND isset($cookies_config['secure']) AND $cookies_config['secure'] !== NULL)
            ? (bool) $cookies_config['secure']

            : (bool) $secure;
        $httponly = ($httponly === NULL AND isset($cookies_config['httponly']) AND $cookies_config['httponly'] !== NULL)
            ? (bool) $cookies_config['httponly']
            : (bool) $httponly;

        if (!is_numeric($expire))
        {
            $expire = time() - 86500;
        }
        else if($expire != -1)
        {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }

        setcookie($prefix.$name, $value, (int) $expire, $path, $domain, $secure, $httponly);
    }

	/**
	 * Request Headers
	 *
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	array
	 */
	public function request_headers($filter = [])
	{
		// If header is already defined, return it immediately
		if ( ! empty($this->headers))
		{
			return $this->_fetch_from_array($this->headers, NULL, $filter);
		}

		// In Apache, you can simply call apache_request_headers()
		if (function_exists('apache_request_headers'))
		{
			$this->headers = apache_request_headers();
		}
		else
		{
			isset($_SERVER['CONTENT_TYPE']) && $this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];

			foreach ($_SERVER as $key => $val)
			{
				if (sscanf($key, 'HTTP_%s', $header) === 1)
				{
					// take SOME_HEADER and turn it into Some-Header
					$header = str_replace('_', ' ', strtolower($header));
					$header = str_replace(' ', '-', ucwords($header));

					$this->headers[$header] = $_SERVER[$key];
				}
			}
		}

		return $this->_fetch_from_array($this->headers, NULL, $filter);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param	string		$index		Header name
	 * @param	array		$filter	    XSS filters
	 * @return	string|null	The requested header on success or NULL on failure
	 */
	public function get_request_header($index, array $filter = [])
	{
		static $headers;

		if (!isset($headers))
		{
			empty($this->headers) AND $this->request_headers();
			foreach ($this->headers as $key => $value)
			{
				$headers[strtolower($key)] = $value;
			}
		}

		$index = strtolower($index);

		if (!isset($headers[$index]))
		{
			return NULL;
		}

        return Xss::clean($headers[$index], $filter);
	}



    private function _find_entries(array $array, array $index, array $filter = [])
    {
        $val = [];
        foreach ($index As $v)
        {
            if (is_string($v))
            {
                $val[] = $this->_fetch_from_array($array, $v, $filter);
            }
        }
        return $val;
    }

    /**
     * Fetch from array
     *
     * Internal method used to retrieve values from global arrays.
     *
     * @param    array &$array $_GET, $_POST, $_COOKIE, $_SERVER, etc.
     * @param    mixed $index Index for item to be fetched from $array
     * @param array|null $filter
     * @return    mixed
     */
    private function _fetch_from_array(&$array, $index = NULL, ?array $filter = [])
    {
        // If $index is NULL, it means that the whole $array is requested
        isset($index) OR $index = array_keys($array);

        // allow fetching multiple keys at once
        if (is_array($index))
        {
            $output = array();
            foreach ($index as $key) {
                $output[$key] = $this->_fetch_from_array($array, $key);
            }
            return $output;
        }
        if (isset($array[$index])) {
            $value = $array[$index];
        }
        elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
        {
            $value = $array;
            for ($i = 0; $i < $count; $i++)
            {
                $key = trim($matches[0][$i], '[]');
                if ($key === '') {// Empty notation will return the value as array
                    break;
                }
                if (isset($value[$key])) {
                    $value = $value[$key];
                }
                else {
                    return NULL;
                }
            }
        }
        else {
            return NULL;
        }
        return Xss::clean($value, array_merge([
            'filter_sanitize'
        ], $filter));
    }

    /**
	 * Sanitize Globals
	 *
	 * Internal method serving for the following purposes:
	 *
	 *	- Unsets $_GET data, if query strings are not enabled
	 *	- Cleans POST, COOKIE and SERVER data
	 * 	- Standardizes newline characters to PHP_EOL
	 *
	 * @return	void
	 */
	private function _sanitize_globals()
	{
        if (is_array($_GET))
		{
			foreach ($_GET as $key => $val)
			{
				$_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Clean $_POST Data
		if (is_array($_POST))
		{
			foreach ($_POST as $key => $val)
			{
				$_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Clean $_COOKIE Data
		if (is_array($_COOKIE))
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset(
				$_COOKIE['$Version'],
				$_COOKIE['$Path'],
				$_COOKIE['$Domain']
			);

			foreach ($_COOKIE as $key => $val)
			{
				if (($cookie_key = $this->_clean_input_keys($key)) !== FALSE)
				{
					$_COOKIE[$cookie_key] = $this->_clean_input_data($val);
				}
				else
				{
					unset($_COOKIE[$key]);
				}
			}
		}

		// Sanitize PHP_SELF
		$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);
	}

    /**
	 * Clean Input Data
	 *
	 * Internal method that aids in escaping data and
	 * standardizing newline characters to PHP_EOL.
	 *
	 * @param	string|string[]	$str	Input string(s)
	 * @return	string|string[]
	 */
	private function _clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach (array_keys($str) as $key)
			{
				$new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($str[$key]);
			}
			return $new_array;
		}

		/* We strip slashes if magic quotes is on to keep things consistent

		   NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
		         it will probably not exist in future versions at all.
		*/
		$str = stripslashes($str);

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = Str::clean_string($str);
		}

		// Remove control characters
		$str = remove_invisible_characters($str, FALSE);

		return $str;
	}

	/**
	 * Clean Keys
	 *
	 * Internal method that helps to prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @param	string	$str	Input string
	 * @param	bool	$fatal	Whether to terminate script exection
	 *				or to return FALSE if an invalid
	 *				key is encountered
	 * @return	string|bool
	 */
	private function _clean_input_keys($str, $fatal = TRUE)
	{
		if ( ! preg_match('/^[a-z0-9:_\/|-]+$/i', $str))
		{
			if ($fatal === TRUE)
			{
				return FALSE;
			}

            $response = Service::response();
            $response->statusCode(503);
            $response->body('Disallowed Key Characters.');
            $response->send();
            exit(7); // EXIT_USER_INPUT
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			return Str::clean_string($str);
		}

		return $str;
	}
}
