<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\data;

use dFramework\core\Config;
use dFramework\core\exception\Exception;
use dFramework\core\utilities\Tableau;
use dFramework\core\utilities\Utils;
/**
 * Request
 *
 * A class that helps wrap Request information and particulars about a single request.
 * Provides methods commonly used to introspect on the request headers and request body.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Data
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.1
 * @credit      CakeRequest (http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/data/Request.php
 */

class Request
{
    /**
     * Array of POST data. Will contain form data as well as uploaded files.
     * Inputs prefixed with 'data' will have the data prefix removed. If there is
     * overlap between an input prefixed with data and one without, the 'data' prefixed
     * value will take precedence.
     *
     * @var array
     */
    public $data = array();

    /**
     * Array of querystring arguments
     *
     * @var array
     */
    public $query = array();

    /**
     * The URL string used for the request.
     *
     * @var string
     */
    public $url;

    /**
     * Base URL path.
     *
     * @var string
     */
    public $base = false;

    /**
     * webroot path segment for the request.
     *
     * @var string
     */
    public $webroot = '/';

    /**
     * The full address to the current request
     *
     * @var string
     */
    public $here = null;

    /**
     * The built in detectors used with `is()` can be modified with `addDetector()`.
     *
     * There are several ways to specify a detector, see CakeRequest::addDetector() for the
     * various formats and ways to define detectors.
     *
     * @var array
     */
    protected $_detectors = array(
        'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
        'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
        'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
        'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
        'head' => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
        'options' => array('env' => 'REQUEST_METHOD', 'value' => 'OPTIONS'),
        'ssl' => array('env' => 'HTTPS', 'value' => 1),
        'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
        'flash' => array('env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/'),
        'mobile' => array('env' => 'HTTP_USER_AGENT', 'options' => array(
            'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone', 'iPad',
            'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi', 'PalmOS', 'PalmSource',
            'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\\.Browser',
            'webOS', 'Windows CE', 'Windows Phone OS', 'Xiino'
        )),
        'requested' => array('param' => 'requested', 'value' => 1)
    );

    /**
     * Copy of php://input. Since this stream can only be read once in most SAPI's
     * keep a copy of it so users don't need to know about that detail.
     *
     * @var string
     */
    protected $_input = '';

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->_base();

        $url = $this->_url();
        if ($url[0] === '/')
        {
            $url = substr($url, 1);
        }
        $this->url = $url;

        $this->_processPost();
        $this->_processGet();
        $this->_processFiles();
        $this->here = $this->base . '/' . $this->url;
    }


    /**
     * Get the IP the client is using, or says they are using.
     *
     * @param bool $safe Use safe = false when you think the user might manipulate their HTTP_CLIENT_IP
     *   header. Setting $safe = false will also look at HTTP_X_FORWARDED_FOR
     * @return string The client IP.
     */
    public function clientIp($safe = true)
    {
        if (!$safe AND env('HTTP_X_FORWARDED_FOR'))
        {
            $ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
        }
        else
        {
            if (env('HTTP_CLIENT_IP'))
            {
                $ipaddr = env('HTTP_CLIENT_IP');
            }
            else
            {
                $ipaddr = env('REMOTE_ADDR');
            }
        }
        if (env('HTTP_CLIENTADDRESS'))
        {
            $tmpipaddr = env('HTTP_CLIENTADDRESS');

            if (!empty($tmpipaddr))
            {
                $ipaddr = preg_replace('/(?:,.*)/', '', $tmpipaddr);
            }
        }
        return trim($ipaddr);
    }

    /**
     * Returns the referer that referred this request.
     *
     * @param bool $local Attempt to return a local address. Local addresses do not contain hostnames.
     * @return string The referring address for this request.
     */
    public function referer($local = false)
    {
        $ref = env('HTTP_REFERER');

        $base = $this->webroot;
        if (!empty($ref) AND !empty($base))
        {
            if ($local AND strpos($ref, $base) === 0)
            {
                $ref = substr($ref, strlen($base));
                if ($ref[0] !== '/')
                {
                    $ref = '/' . $ref;
                }
                return $ref;
            }
            elseif (!$local)
            {
                return $ref;
            }
        }
        return '/';
    }

    /**
     * Check whether or not a Request is a certain type.
     *
     * Uses the built in detection rules as well as additional rules
     * defined with CakeRequest::addDetector(). Any detector can be called
     * as `is($type)` or `is$Type()`.
     *
     * @param string|array $type The type of request you want to check. If an array
     *   this method will return true if the request matches any type.
     * @return bool Whether or not the request is the type you are checking.
     */
    public function is($type)
    {
        if (is_array($type))
        {
            $result = array_map(array($this, 'is'), $type);
            return count(array_filter($result)) > 0;
        }
        $type = strtolower($type);
        if (!isset($this->_detectors[$type]))
        {
            return false;
        }
        $detect = $this->_detectors[$type];
        if (isset($detect['env']))
        {
            if (isset($detect['value']))
            {
                return \env($detect['env']) == $detect['value'];
            }
            if (isset($detect['pattern']))
            {
                return (bool)preg_match($detect['pattern'], env($detect['env']));
            }
            if (isset($detect['options']))
            {
                $pattern = '/' . implode('|', $detect['options']) . '/i';
                return (bool)preg_match($pattern, env($detect['env']));
            }
        }
        if (isset($detect['param']))
        {
            $key = $detect['param'];
            if (isset($detect['value']))
            {
                $value = $detect['value'];
                return isset($this->params[$key]) ? $this->params[$key] == $value : false;
            }
            if (isset($detect['options']))
            {
                return isset($this->params[$key]) ? in_array($this->params[$key], $detect['options']) : false;
            }
        }
        if (isset($detect['callback']) AND is_callable($detect['callback']))
        {
            return call_user_func($detect['callback'], $this);
        }
        return false;
    }

    /**
     * Check that a request matches all the given types.
     *
     * Allows you to test multiple types and union the results.
     * See CakeRequest::is() for how to add additional types and the
     * built-in types.
     *
     * @param array $types The types to check.
     * @return bool Success.
     * @see CakeRequest::is()
     */
    public function isAll(array $types)
    {
        $result = array_filter(array_map(array($this, 'is'), $types));
        return count($result) === count($types);
    }

    /**
     * Add a new detector to the list of detectors that a request can use.
     * There are several different formats and types of detectors that can be set.
     *
     * ### Environment value comparison
     *
     * An environment value comparison, compares a value fetched from `env()` to a known value
     * the environment value is equality checked against the provided value.
     *
     * e.g `addDetector('post', array('env' => 'REQUEST_METHOD', 'value' => 'POST'))`
     *
     * ### Pattern value comparison
     *
     * Pattern value comparison allows you to compare a value fetched from `env()` to a regular expression.
     *
     * e.g `addDetector('iphone', array('env' => 'HTTP_USER_AGENT', 'pattern' => '/iPhone/i'));`
     *
     * ### Option based comparison
     *
     * Option based comparisons use a list of options to create a regular expression. Subsequent calls
     * to add an already defined options detector will merge the options.
     *
     * e.g `addDetector('mobile', array('env' => 'HTTP_USER_AGENT', 'options' => array('Fennec')));`
     *
     * ### Callback detectors
     *
     * Callback detectors allow you to provide a 'callback' type to handle the check. The callback will
     * receive the request object as its only parameter.
     *
     * e.g `addDetector('custom', array('callback' => array('SomeClass', 'somemethod')));`
     *
     * ### Request parameter detectors
     *
     * Allows for custom detectors on the request parameters.
     *
     * e.g `addDetector('requested', array('param' => 'requested', 'value' => 1)`
     *
     * You can also make parameter detectors that accept multiple values
     * using the `options` key. This is useful when you want to check
     * if a request parameter is in a list of options.
     *
     * `addDetector('extension', array('param' => 'ext', 'options' => array('pdf', 'csv'))`
     *
     * @param string $name The name of the detector.
     * @param array $options The options for the detector definition. See above.
     * @return void
     */
    public function addDetector($name, $options)
    {
        $name = strtolower($name);
        if (isset($this->_detectors[$name]) && isset($options['options'])) {
            $options = Tableau::merge($this->_detectors[$name], $options);
        }
        $this->_detectors[$name] = $options;
    }

    /**
     * Get the value of the current requests URL. Will include named parameters and querystring arguments.
     *
     * @param bool $base Include the base path, set to false to trim the base path off.
     * @return string the current request URL including query string args.
     */
    public function here($base = true)
    {
        $url = $this->here;
        if (!empty($this->query))
        {
            $url .= '?' . http_build_query($this->query, null, '&');
        }
        if (!$base)
        {
            $url = preg_replace('/^' . preg_quote($this->base, '/') . '/', '', $url, 1);
        }
        return $url;
    }

    /**
     * Read an HTTP header from the Request information.
     *
     * @param string $name Name of the header you want.
     * @return mixed Either false on no header being set or the value of the header.
     */
    public static function header($name)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (!empty($_SERVER[$name]))
        {
            return $_SERVER[$name];
        }
        return false;
    }

    /**
     * Get the HTTP method used for this request.
     * There are a few ways to specify a method.
     *
     * - If your client supports it you can use native HTTP methods.
     * - You can set the HTTP-X-Method-Override header.
     * - You can submit an input with the name `_method`
     *
     * Any of these 3 approaches can be used to set the HTTP method used
     * by CakePHP internally, and will effect the result of this method.
     *
     * @return string The name of the HTTP method used.
     */
    public function method()
    {
        return env('REQUEST_METHOD');
    }

    /**
     * Get the host that the request was handled on.
     *
     * @param bool $trustProxy Whether or not to trust the proxy host.
     * @return string
     */
    public function host($trustProxy = false)
    {
        if ($trustProxy)
        {
            return env('HTTP_X_FORWARDED_HOST');
        }
        return env('HTTP_HOST');
    }

    /**
     * Get the domain name and include $tldLength segments of the tld.
     *
     * @param int $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *   While `example.co.uk` contains 2.
     * @return string Domain name without subdomains.
     */
    public function domain($tldLength = 1)
    {
        $segments = explode('.', $this->host());
        $domain = array_slice($segments, -1 * ($tldLength + 1));
        return implode('.', $domain);
    }

    /**
     * Get the subdomains for a host.
     *
     * @param int $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *   While `example.co.uk` contains 2.
     * @return array An array of subdomains.
     */
    public function subdomains($tldLength = 1)
    {
        $segments = explode('.', $this->host());
        return array_slice($segments, 0, -1 * ($tldLength + 1));
    }

    /**
     * Find out which content types the client accepts or check if they accept a
     * particular type of content.
     *
     * #### Get all types:
     *
     * `$this->request->accepts();`
     *
     * #### Check for a single type:
     *
     * `$this->request->accepts('application/json');`
     *
     * This method will order the returned content types by the preference values indicated
     * by the client.
     *
     * @param string $type The content type to check for. Leave null to get all types a client accepts.
     * @return mixed Either an array of all the types the client accepts or a boolean if they accept the
     *   provided type.
     */
    public function accepts($type = null)
    {
        $raw = $this->parseAccept();
        $accept = array();
        foreach ($raw as $types)
        {
            $accept = array_merge($accept, $types);
        }
        if ($type === null)
        {
            return $accept;
        }
        return in_array($type, $accept);
    }

    /**
     * Parse the HTTP_ACCEPT header and return a sorted array with content types
     * as the keys, and pref values as the values.
     *
     * Generally you want to use CakeRequest::accept() to get a simple list
     * of the accepted content types.
     *
     * @return array An array of prefValue => array(content/types)
     */
    public function parseAccept()
    {
        return $this->_parseAcceptWithQualifier($this->header('accept'));
    }

    /**
     * Get the languages accepted by the client, or check if a specific language is accepted.
     *
     * Get the list of accepted languages:
     *
     * {{{ CakeRequest::acceptLanguage(); }}}
     *
     * Check if a specific language is accepted:
     *
     * {{{ CakeRequest::acceptLanguage('es-es'); }}}
     *
     * @param string $language The language to test.
     * @return mixed If a $language is provided, a boolean. Otherwise the array of accepted languages.
     */
    public static function acceptLanguage($language = null)
    {
        $raw = self::_parseAcceptWithQualifier(self::header('Accept-Language'));
        $accept = array();
        foreach ($raw as $languages)
        {
            foreach ($languages As &$lang)
            {
                if (strpos($lang, '_'))
                {
                    $lang = str_replace('_', '-', $lang);
                }
                $lang = strtolower($lang);
            }
            $accept = array_merge($accept, $languages);
        }
        if ($language === null)
        {
            return $accept;
        }
        return in_array(strtolower($language), $accept);
    }

    /**
     * Provides a read accessor for `$this->query`. Allows you
     * to use a syntax similar to `CakeSession` for reading URL query data.
     *
     * @param string $name Query string variable name
     * @return mixed The value being read
     */
    public function query($name)
    {
        return Tableau::get($this->query, $name);
    }

    /**
     * Provides a read/write accessor for `$this->data`. Allows you
     * to use a syntax similar to `CakeSession` for reading post data.
     *
     * ## Reading values.
     *
     * `$request->data('Post.title');`
     *
     * When reading values you will get `null` for keys/values that do not exist.
     *
     * ## Writing values
     *
     * `$request->data('Post.title', 'New post!');`
     *
     * You can write to any value, even paths/keys that do not exist, and the arrays
     * will be created for you.
     *
     * @param string $name Dot separated name of the value to read/write, one or more args.
     * @return mixed|$this Either the value being read, or $this so you can chain consecutive writes.
     */
    public function data($name)
    {
        $args = func_get_args();
        if (count($args) === 2)
        {
            $this->data = Tableau::insert($this->data, $name, $args[1]);
            return $this;
        }
        return Tableau::get($this->data, $name);
    }

    /**
     * Read data from `php://input`. Useful when interacting with XML or JSON
     * request body content.
     *
     * Getting input with a decoding function:
     *
     * `$this->request->input('json_decode');`
     *
     * Getting input using a decoding function, and additional params:
     *
     * `$this->request->input('Xml::build', array('return' => 'DOMDocument'));`
     *
     * Any additional parameters are applied to the callback in the order they are given.
     *
     * @param string $callback A decoding callback that will convert the string data to another
     *     representation. Leave empty to access the raw input data. You can also
     *     supply additional parameters for the decoding callback using var args, see above.
     * @return mixed The decoded/processed request data.
     */
    public function input($callback = null)
    {
        $input = $this->_readInput();
        $args = func_get_args();
        if (!empty($args))
        {
            $callback = array_shift($args);
            array_unshift($args, $input);
            return call_user_func_array($callback, $args);
        }
        return $input;
    }

    /**
     * Allow only certain HTTP request methods. If the request method does not match
     * a 405 error will be shown and the required "Allow" response header will be set.
     *
     * Example:
     *
     * $this->request->allowMethod('post', 'delete');
     * or
     * $this->request->allowMethod(array('post', 'delete'));
     *
     * If the request would be GET, response header "Allow: POST, DELETE" will be set
     * and a 405 error will be returned.
     *
     * @param string|array $methods Allowed HTTP request methods.
     * @return void|bool true
     */
    public function allowMethod($methods)
    {
        if (!is_array($methods))
        {
            $methods = func_get_args();
        }
        foreach ($methods as $method)
        {
            if ($this->is($method)) {
                return true;
            }
        }
        Exception::show('The method\'s <b>'.strtoupper(implode(', ', $methods)).'</b> is not allowed');
    }



    /**
     * Missing method handler, handles wrapping older style isAjax() type methods
     *
     * @param string $name The method called
     * @param array $params Array of parameters for the method call
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $params)
    {
        if (strpos($name, 'is') === 0)
        {
            $type = strtolower(substr($name, 2));
            return $this->is($type);
        }
        throw new Exception('Method '.$name.' does not exist in Request Object');
    }

    /**
     * process the post data and set what is there into the object.
     * processed data is available at `$this->data`
     *
     * Will merge POST vars prefixed with `data`, and ones without
     * into a single array. Variables prefixed with `data` will overwrite those without.
     *
     * If you have mixed POST values be careful not to make any top level keys numeric
     * containing arrays. Hash::merge() is used to merge data, and it has possibly
     * unexpected behavior in this situation.
     *
     * @credit CakePHP
     * @return void
     */
    protected function _processPost()
    {
        if ($_POST)
        {
            $this->data = $_POST;
        } elseif (
            ($this->is('put') OR $this->is('delete')) AND
            strpos(env('CONTENT_TYPE'), 'application/x-www-form-urlencoded') === 0
        )
        {
            $data = $this->_readInput();
            parse_str($data, $this->data);
        }
        if (ini_get('magic_quotes_gpc') === '1')
        {
            $this->data = Utils::stripslashes_deep($this->data);
        }
        if (env('HTTP_X_HTTP_METHOD_OVERRIDE'))
        {
            $this->data['_method'] = env('HTTP_X_HTTP_METHOD_OVERRIDE');
        }
        $isArray = is_array($this->data);
        if ($isArray AND isset($this->data['_method']))
        {
            if (!empty($_SERVER))
            {
                $_SERVER['REQUEST_METHOD'] = $this->data['_method'];
            }
            else
            {
                $_ENV['REQUEST_METHOD'] = $this->data['_method'];
            }
            unset($this->data['_method']);
        }
        if ($isArray AND isset($this->data['data']))
        {
            $data = $this->data['data'];
            if (count($this->data) <= 1)
            {
                $this->data = $data;
            }
            else
            {
                unset($this->data['data']);
                $this->data = Tableau::merge($this->data, $data);
            }
        }
    }

    /**
     * Process the GET parameters and move things into the object.
     *
     * @credit CakePHP
     * @return void
     */
    protected function _processGet()
    {
        if (ini_get('magic_quotes_gpc') === '1')
        {
            $query = Utils::stripslashes_deep($_GET);
        }
        else
        {
            $query = $_GET;
        }

        $unsetUrl = '/' . str_replace('.', '_', urldecode($this->url));
        unset($query[$unsetUrl]);
        unset($query[$this->base . $unsetUrl]);
        if (strpos($this->url, '?') !== false)
        {
            list(, $querystr) = explode('?', $this->url);
            parse_str($querystr, $queryArgs);
            $query += $queryArgs;
        }
        if (isset($this->params['url']))
        {
            $query = array_merge($this->params['url'], $query);
        }
        $this->query = $query;
    }

    /**
     * Get the request uri. Looks in PATH_INFO first, as this is the exact value we need prepared
     * by PHP. Following that, REQUEST_URI, PHP_SELF, HTTP_X_REWRITE_URL and argv are checked in that order.
     * Each of these server variables have the base path, and query strings stripped off
     *
     * @credit CakePHP
     * @return string URI The CakePHP request path that is being accessed.
     */
    protected function _url()
    {
        if (!empty($_SERVER['PATH_INFO']))
        {
            return $_SERVER['PATH_INFO'];
        }
        elseif (isset($_SERVER['REQUEST_URI']) AND strpos($_SERVER['REQUEST_URI'], '://') === false)
        {
            $uri = $_SERVER['REQUEST_URI'];
        }
        elseif (isset($_SERVER['REQUEST_URI']))
        {
            $qPosition = strpos($_SERVER['REQUEST_URI'], '?');
            if ($qPosition !== false AND strpos($_SERVER['REQUEST_URI'], '://') > $qPosition)
            {
                $uri = $_SERVER['REQUEST_URI'];
            }
            else
            {
               $uri = substr($_SERVER['REQUEST_URI'], strlen(Config::get('general.base_url')));
            }
        }
        elseif (isset($_SERVER['PHP_SELF']) AND isset($_SERVER['SCRIPT_NAME']))
        {
            $uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
        }
        elseif (isset($_SERVER['HTTP_X_REWRITE_URL']))
        {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        elseif ($var = env('argv'))
        {
            $uri = $var[0];
        }

        $base = $this->base;

        if (strlen($base) > 0 AND strpos($uri, $base) === 0)
        {
            $uri = substr($uri, strlen($base));
        }
        if (strpos($uri, '?') !== false)
        {
            list($uri) = explode('?', $uri, 2);
        }
        if (empty($uri) OR $uri === '/' OR $uri === '//' OR $uri === '/index.php')
        {
            $uri = '/';
        }
        return $uri;
    }

    /**
     * Returns a base URL and sets the proper webroot
     *
     * If CakePHP is called with index.php in the URL even though
     * URL Rewriting is activated (and thus not needed) it swallows
     * the unnecessary part from $base to prevent issue #3318.
     *
     * @return string Base URL
     * @link https://cakephp.lighthouseapp.com/projects/42648-cakephp/tickets/3318
     */
    protected function _base()
    {
        $base_url = Config::get('general.base_url');

        if (!isset($base_url))
        {
            $base_url = $this->base;
        }
        if ($base_url !== false)
        {
            $this->webroot = $base_url . '/';
            return $this->base = $base_url;
        }
    }

    /**
     * Process $_FILES and move things into the object.
     *
     * @return void
     */
    protected function _processFiles()
    {
        if (isset($_FILES) AND is_array($_FILES))
        {
            foreach ($_FILES as $name => $data)
            {
                if ($name !== 'data')
                {
                    $this->params['form'][$name] = $data;
                }
            }
        }

        if (isset($_FILES['data']))
        {
            foreach ($_FILES['data'] as $key => $data)
            {
                $this->_processFileData('', $data, $key);
            }
        }
    }

    /**
     * Recursively walks the FILES array restructuring the data
     * into something sane and useable.
     *
     * @param string $path The dot separated path to insert $data into.
     * @param array $data The data to traverse/insert.
     * @param string $field The terminal field name, which is the top level key in $_FILES.
     * @return void
     */
    protected function _processFileData($path, $data, $field)
    {
        foreach ($data as $key => $fields)
        {
            $newPath = $key;
            if (!empty($path))
            {
                $newPath = $path . '.' . $key;
            }
            if (is_array($fields))
            {
                $this->_processFileData($newPath, $fields, $field);
            }
            else
            {
                $newPath .= '.' . $field;
                $this->data = Tableau::insert($this->data, $newPath, $fields);
            }
        }
    }

    /**
     * Parse Accept* headers with qualifier options.
     *
     * Only qualifiers will be extracted, any other accept extensions will be
     * discarded as they are not frequently used.
     *
     * @param string $header Header to parse.
     * @return array
     */
    protected static function _parseAcceptWithQualifier($header)
    {
        $accept = array();
        $header = explode(',', $header);
        foreach (array_filter($header) as $value)
        {
            $prefValue = '1.0';
            $value = trim($value);

            $semiPos = strpos($value, ';');
            if ($semiPos !== false)
            {
                $params = explode(';', $value);
                $value = trim($params[0]);
                foreach ($params as $param)
                {
                    $qPos = strpos($param, 'q=');
                    if ($qPos !== false)
                    {
                        $prefValue = substr($param, $qPos + 2);
                    }
                }
            }

            if (!isset($accept[$prefValue]))
            {
                $accept[$prefValue] = array();
            }
            if ($prefValue)
            {
                $accept[$prefValue][] = $value;
            }
        }
        krsort($accept);
        return $accept;
    }

    /**
     * Read data from php://input, mocked in tests.
     *
     * @return string contents of php://input
     */
    protected function _readInput()
    {
        if (empty($this->_input))
        {
            $fh = fopen('php://input', 'r');
            $content = stream_get_contents($fh);
            fclose($fh);
            $this->_input = $content;
        }
        return $this->_input;
    }

}
