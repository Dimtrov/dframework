<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT)
 *
 * @package	dFramework
 * @author	Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Data
 *
 * Pre-processes global input data for security
 *
 * @class       Data
 * @package		dFramework
 * @subpackage	Core
 * @category    Data
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/data
 * @file        /system/core/data/Data.php
 */

namespace dFramework\core\data;

use dFramework\core\Config;
use dFramework\core\security\Xss;


class Data
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
     * @see	CI_Input::input_stream()
     * @var	array
     */
    protected $_input_stream;

    /**
     * @var Xss
     */
    private $xss;

    public function __construct()
    {
        $this->xss = new Xss();
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
        if(!empty($value)) {
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
        if(!empty($value)) {
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
    public function post_get($index, ?array $filter = [])
    {
        return isset($_POST[$index]) ? $this->post($index, null, $filter) : $this->get($index, null, $filter);
    }

    /**
     * Fetch an item from GET data with fallback to POST
     *
     * @param    string $index Index for item to be fetched from $_GET or $_POST
     * @param array|null $filter
     * @return    mixed
     */
    public function get_post($index, ?array $filter = [])
    {
        return isset($_GET[$index]) ? $this->get($index, null, $filter) : $this->post($index, null, $filter);
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
     * @param null $index
     * @param null $value
     * @param array|null $filter
     * @return mixed
     */
    public function session($index = null, $value = null, ?array $filter = [])
    {
        if(!empty($value)) {
            $_SESSION['df_session'][$index] = $value;
        }
        return $this->_fetch_from_array($_SESSION['df_session'], $index, $filter);
    }

    /**
     * @param string ...$index
     */
    public function free_session(string ...$index)
    {
        foreach ($index As $value) {
            unset($_SESSION['df_session'][$value]);
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
        if(!empty($data) AND is_array($data) AND isset($data['value']) AND !empty($index) AND is_string($index))
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
        if (!is_numeric($expire)) {
            $expire = time() - 86500;
        }
        else if($expire != -1) {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }
        setcookie($prefix.$name, $value, (int) $expire, $path, $domain, $secure, $httponly);
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
    {// If $index is NULL, it means that the whole $array is requested
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
        return $this->xss->clean($value, array_merge([
            'prevent_basic_xss', 'filter_sanitize'
        ], $filter));
    }


}