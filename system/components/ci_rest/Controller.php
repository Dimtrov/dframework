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

namespace dFramework\components\ci_rest;

/**
 * dFramework Rest Controller
 * 
 * A fully RESTful server implementation for dFramework (inspired by CodeIgniter) using one library, one config file and one controller.
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Rest
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.0
 * @credit      CI Rest Server - by Chris Kacerguis <chriskacerguis@gmail.com> - https://github.com/chriskacerguis/ci-restserver
 * @file        /system/components/rest/Controller.php
 */

 
use dFramework\core\exception\Exception;
use dFramework\core\Controller As dF_Controller;
use dFramework\core\loader\Load;
use dFramework\core\route\Dispatcher;
use dFramework\core\security\Xss;
use dFramework\core\utilities\Uri;
use dFramework\core\db\Query;
use dFramework\core\Config;
use stdClass;


class Controller extends dF_Controller
{
    /**
     * This defines the rest format
     * Must be overridden it in a controller so that it is set.
     *
     * @var string|null
     */
    protected $rest_format = null;

    /**
     * Defines the list of method properties such as limit, log and level.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * List of allowed HTTP methods.
     *
     * @var array
     */
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

    /**
     * Contains details about the REST API
     * Fields: db, ignore_limits, key, level, user_id
     * Note: This is a dynamic object (stdClass).
     *
     * @var object
     */
    protected $rest = null;

    /**
     * The arguments for the GET request method.
     *
     * @var array
     */
    protected $_get_args = [];

    /**
     * The arguments for the POST request method.
     *
     * @var array
     */
    protected $_post_args = [];

    /**
     * The arguments for the PUT request method.
     *
     * @var array
     */
    protected $_put_args = [];

    /**
     * The arguments for the DELETE request method.
     *
     * @var array
     */
    protected $_delete_args = [];

    /**
     * The arguments for the PATCH request method.
     *
     * @var array
     */
    protected $_patch_args = [];

    /**
     * The arguments for the HEAD request method.
     *
     * @var array
     */
    protected $_head_args = [];

    /**
     * The arguments for the OPTIONS request method.
     *
     * @var array
     */
    protected $_options_args = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    protected $_query_args = [];

    /**
     * The arguments from GET, POST, PUT, DELETE, PATCH, HEAD and OPTIONS request methods combined.
     *
     * @var array
     */
    protected $_args = [];

    /**
     * The insert_id of the log entry (if we have one).
     *
     * @var string
     */
    protected $_insert_id = '';

    /**
     * If the request is allowed based on the API key provided.
     *
     * @var bool
     */
    protected $_allow = true;

    /**
     * The LDAP Distinguished Name of the User post authentication.
     *
     * @var string
     */
    protected $_user_ldap_dn = '';

    /**
     * The start of the response time from the server.
     *
     * @var number
     */
    protected $_start_rtime;

    /**
     * The end of the response time from the server.
     *
     * @var number
     */
    protected $_end_rtime;

    /**
     * List all supported methods, the first will be the default format.
     *
     * @var array
     */
    protected $_supported_formats = [
        'json'       => 'application/json',
        'array'      => 'application/json',
        'csv'        => 'application/csv',
        'html'       => 'text/html',
        'jsonp'      => 'application/javascript',
        'php'        => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml'        => 'application/xml',
    ];

    /**
     * Information about the current API user.
     *
     * @var object
     */
    protected $_apiuser;

    /**
     * Whether or not to perform a CORS check and apply CORS headers to the request.
     *
     * @var bool
     */
    protected $check_cors = null;

    /**
     * Enable XSS flag
     * Determines whether the XSS filter is always active when
     * GET, OPTIONS, HEAD, POST, PUT, DELETE and PATCH data is encountered
     * Set automatically based on config setting.
     *
     * @var bool
     */
    protected $_enable_xss = false;

    private $is_valid_request = true;

    /**
     * Common HTTP status codes and their respective description.
     *
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_INTERNAL_ERROR = 500;

    /**
     * @var Format
     */
    private $format;

    /**
     * @var bool
     */
    protected $auth_override;


    /**
     * @var
     */
    private $_config; 

    /**
     * @var
     */
    private $_lang;

    /**
     * Implemantation of dF_Controller abstract method
     */
    public function index() {}

    /**
     * Extend this function to apply additional checking early on in the process.
     *
     * @return void
     */
    protected function early_checks()
    {
    }

    /**
     * Constructor for the REST API.
     *
     */
    public function __construct()
    {
        parent::__construct();

        // Set the default value of global xss filtering. Only true
        $this->_enable_xss = true;

        // Don't try to parse template variables like {elapsed_time} and {memory_usage}
        // when output is displayed for not damaging data accidentally
        //$this->output->parse_exec_vars = false;

        // Load the rest.php configuration file
        $this->_config = Config::get('rest');

        // Log the loading time to the log table
        if ($this->_config['rest_enable_logging'] === true) 
        {
            // Start the timer for how long the request takes
            $this->_start_rtime = microtime(true);
        }

        // Determine supported output formats from configuration
        $supported_formats = $this->_config['rest_supported_formats'];

        // Validate the configuration setting output formats
        if (empty($supported_formats)) 
        {
            $supported_formats = [];
        }
        if (!is_array($supported_formats)) 
        {
            $supported_formats = [$supported_formats];
        }

        // Add silently the default output format if it is missing
        $default_format = $this->_get_default_output_format();
        if (!in_array($default_format, $supported_formats))
        {
            $supported_formats[] = $default_format;
        }

        // Now update $this->_supported_formats
        $this->_supported_formats = array_intersect_key($this->_supported_formats, array_flip($supported_formats));

        // Get the language
        $language = $this->_config['rest_language'] ?? 'en';
        // Load the language 
        Load::lang('component.rest', $this->_lang, $language, false);
        $this->_lang = (array) $this->_lang;
        
        // Initialise the rest object
        $this->rest = new stdClass();

        // Check to see if the current IP address is blacklisted
        if ($this->_config['rest_ip_blacklist_enabled'] === true) 
        {
            $this->_check_blacklist_auth();
        }

        // Determine whether the connection is HTTPS
        $this->request->ssl = is_https();

        // How is this request being made? GET, POST, PATCH, DELETE, INSERT, PUT, HEAD or OPTIONS
    //    $this->request->method = $this->_detect_method();

        // Check for CORS access request
        $check_cors = $this->_config['check_cors'];
        if ($check_cors === true) 
        {
            $this->_check_cors();
        }

        // Create an argument container if it doesn't exist e.g. _get_args
        if (isset($this->{'_'.strtolower($this->request->method()).'_args'}) === false) 
        {
            $this->{'_'.strtolower($this->request->method()).'_args'} = [];
        }

        // Set up the query parameters
        $this->_parse_query();

        // Set up the GET variables
        $this->_get_args = array_merge($this->_get_args, Uri::instance()->ruri_to_assoc());

        // Try to find a format for the request (means we have a request body)
        $this->request->format = $this->_detect_input_format();

        // Not all methods have a body attached with them
        $this->request->body = null;

        $this->{'_parse_'.strtolower($this->request->method())}();

        // Fix parse method return arguments null
        if ($this->{'_'.strtolower($this->request->method()).'_args'} === null) 
        {
            $this->{'_'.strtolower($this->request->method()).'_args'} = [];
        }

        // Which format should the data be returned in?
        $this->response->format = $this->_detect_output_format();

        // Which language should the data be returned in?
        $this->response->lang = $this->_detect_lang();

        // Now we know all about our request, let's try and parse the body if it exists
        if ($this->request->format AND $this->request->body)
        {
            $this->request->body = Format::factory($this->request->body, $this->request->format)->to_array();

            // Assign payload arguments to proper method container
            $this->{'_'.strtolower($this->request->method()).'_args'} = $this->request->body;
        }

        //get header vars
        $this->_head_args = $this->request->accepts();

        // Merge both for one mega-args variable
        $this->_args = array_merge(
            $this->_get_args,
            $this->_options_args,
            $this->_patch_args,
            $this->_head_args,
            $this->_put_args,
            $this->_post_args,
            $this->_delete_args,
            $this->{'_'.strtolower($this->request->method()).'_args'}
        );

        // Extend this function to apply additional checking early on in the process
        $this->early_checks();

        // Load DB if its enabled
        if ($this->_config['rest_database_group'] AND ($this->_config['rest_enable_keys'] OR $this->_config['rest_enable_logging'])) 
        {
            $this->rest->db = new Query($this->_config['rest_database_group']);
        }
        // Use whatever database is in use (isset returns FALSE)
        elseif (property_exists($this, 'db')) 
        {
            $this->rest->db = $this->db;
        }

        // Check if there is a specific auth type for the current class/method
        // _auth_override_check could exit so we need $this->rest->db initialized before
        $this->auth_override = $this->_auth_override_check();

        // Checking for keys? GET TO WorK!
        // Skip keys test for $config['auth_override_class_method']['class'['method'] = 'none'
        if ($this->_config['rest_enable_keys'] AND $this->auth_override !== true) 
        {
            $this->_allow = $this->_detect_api_key();
        }
        // Only allow ajax requests
        if ($this->request->is('ajax') === false AND $this->_config['rest_ajax_only']) 
        {
            // Display an error response
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_ajax_only'],
            ], self::HTTP_NOT_ACCEPTABLE);
        }

        // When there is no specific override for the current class/method, use the default auth value set in the config
        if ($this->auth_override === false AND
            (!($this->_config['rest_enable_keys'] AND $this->_allow === true) OR
                ($this->_config['allow_auth_and_keys'] === true AND $this->_allow === true))) 
        {
            switch (strtolower($this->_config['rest_auth'])) 
            {
                case 'basic':
                    $this->_prepare_basic_auth();
                    break;
                case 'digest':
                    $this->_prepare_digest_auth();
                    break;
                case 'session':
                    $this->_check_php_session();
                    break;
            }
        }
    }

    /**
     * Does the auth stuff.
     */
    private function do_auth($method = false)
    {
        // If we don't want to do auth, then just return true
        if ($method === false) 
        {
            return true;
        }
        if (file_exists(__DIR__.DS.'auth-'.$method.'.php')) 
        {
            include __DIR__.DS.'auth-'.$method.'.php';
        }
    }

    /**
     * De-constructor.
     *
     * @author Chris Kacerguis
     *
     * @return void
     */
    public function __destruct()
    {
        // Log the loading time to the log table
        if ($this->_config['rest_enable_logging'] === true) 
        {
            // Get the current timestamp
            $this->_end_rtime = microtime(true);

            $this->_log_access_time();
        }
    }

    /**
     * Requests are not made to methods directly, the request will be for
     * an "object". This simply maps the object and method to the correct
     * Controller method.
     *
     * @param string $object_called
     * @param array  $arguments     The arguments passed to the controller method
     *
     * @throws Exception
     */
    public function _remap($object_called, $arguments = [])
    {
        // Should we answer if not over SSL?
        if ($this->_config['force_https'] AND $this->request->ssl === false) 
        {
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_unsupported'],
            ], self::HTTP_FORBIDDEN);
        }

        // Remove the supported format from the function name e.g. index.json => index
        $object_called = preg_replace('/^(.*)\.(?:'.implode('|', array_keys($this->_supported_formats)).')$/', '$1', $object_called);

        $controller_method = $object_called.'_'.$this->request->method;
        // Does this method exist? If not, try executing an index method
        if (!method_exists($this, $controller_method)) 
        {
            $controller_method = 'index_'.$this->request->method;
            array_unshift($arguments, $object_called);
        }

        // Do we want to log this method (if allowed by config)?
        $log_method = !(isset($this->methods[$controller_method]['log']) AND $this->methods[$controller_method]['log'] === false);

        // Use keys for this method?
        $use_key = !(isset($this->methods[$controller_method]['key']) AND $this->methods[$controller_method]['key'] === false);

        // They provided a key, but it wasn't valid, so get them out of here
        if ($this->_config['rest_enable_keys'] AND $use_key AND $this->_allow === false) 
        {
            if ($this->_config['rest_enable_logging'] AND $log_method) 
            {
                $this->_log_request();
            }
            // fix cross site to option request error
            if ($this->request->method == 'options')
            {
                exit;
            }

            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => sprintf($this->_lang['text_rest_invalid_api_key'], $this->rest->key),
            ], self::HTTP_FORBIDDEN);
        }

        // Check to see if this key has access to the requested controller
        if ($this->_config['rest_enable_keys'] AND $use_key AND empty($this->rest->key) === false AND $this->_check_access() === false) 
        {
            if ($this->_config['rest_enable_logging'] AND $log_method) 
            {
                $this->_log_request();
            }

            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_api_key_unauthorized'],
            ], self::HTTP_UNAUTHORIZED);
        }
        // Sure it exists, but can they do anything with it?
        if (!method_exists($this, $controller_method)) 
        {
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_unknown_method'],
            ], self::HTTP_METHOD_NOT_ALLOWED);
        }

        // Doing key related stuff? Can only do it if they have a key right?
        if ($this->_config['rest_enable_keys'] AND empty($this->rest->key) === false) 
        {
            // Check the limit
            if ($this->_config['rest_enable_limits'] AND $this->_check_limit($controller_method) === false) 
            {
                $response = [$this->_config['rest_status_field_name'] => false, $this->config['rest_message_field_name'] => $this->_lang['text_rest_api_key_time_limit']];
                $this->response($response, self::HTTP_UNAUTHORIZED);
            }

            // If no level is set use 0, they probably aren't using permissions
            $level = isset($this->methods[$controller_method]['level']) ? $this->methods[$controller_method]['level'] : 0;

            // If no level is set, or it is lower than/equal to the key's level
            $authorized = $level <= $this->rest->level;
            // IM TELLIN!
            if ($this->_config['rest_enable_logging'] AND $log_method) 
            {
                $this->_log_request($authorized);
            }
            if ($authorized === false) 
            {
                // They don't have good enough perms
                $response = [$this->_config['rest_status_field_name'] => false, $this->_config['rest_message_field_name'] => $this->_lang['text_rest_api_key_permissions']];
                $this->response($response, self::HTTP_UNAUTHORIZED);
            }
        }
        //check request limit by ip without login
        elseif ($this->_config['rest_limits_method'] == 'IP_ADDRESS' AND $this->_config['rest_enable_limits'] AND $this->_check_limit($controller_method) === false) 
        {
            $response = [$this->_config['rest_status_field_name'] => false, $this->_config['rest_message_field_name'] => $this->_lang['text_rest_ip_address_time_limit']];
            $this->response($response, self::HTTP_UNAUTHORIZED);
        }
        // No key stuff, but record that stuff is happening
        elseif ($this->_config['rest_enable_logging'] AND $log_method) 
        {
            $this->_log_request($authorized = true);
        }
        // Call the controller method and passed arguments
        try {
            if ($this->is_valid_request) 
            {
                call_user_func_array([$this, $controller_method], $arguments);
            }
        } catch (Exception $ex) {
            if ($this->_config['rest_handle_exceptions'] === false) 
            {
                throw $ex;
            }
            // If the method doesn't exist, then the error will be caught and an error response shown
            Exception::Throw($ex);
        }
    }

    /**
     * Takes mixed data and optionally a status code, then creates the response.
     *
     * @param array|null $data      Data to output to the user
     * @param int|null   $http_code HTTP status code
     * @param bool       $continue  TRUE to flush the response to the client and continue
     *                              running the script; otherwise, exit
     */
    public function response($data = null, $http_code = null, $continue = false)
    {
        //if profiling enabled then print profiling data
        $isProfilingEnabled = false;
        if (!$isProfilingEnabled) 
        {
            ob_start();
            // If the HTTP status is not NULL, then cast as an integer
            if ($http_code !== null) 
            {
                // So as to be safe later on in the process
                $http_code = (int) $http_code;
            }
            // Set the output as NULL by default
            $output = null;

            // If data is NULL and no HTTP status code provided, then display, error and exit
            if ($data === null AND $http_code === null) 
            {
                $http_code = self::HTTP_NOT_FOUND;
            }

            // If data is not NULL and a HTTP status code provided, then continue
            elseif ($data !== null) 
            {
                // If the format method exists, call and return the output in that format
                if (method_exists(Format::class, 'to_'.$this->response->format)) 
                {
                    // CORB protection
                    // First, get the output content.
                    $output = Format::factory($data)->{'to_'.$this->response->format}();

                    // Set the format header
                    // Then, check if the client asked for a callback, and if the output contains this callback :
                    if (isset($this->_get_args['callback']) AND $this->response->format == 'json' AND preg_match('/^'.$this->_get_args['callback'].'/', $output)) 
                    {
						$this->response->type($this->_supported_formats['jsonp']);
						$this->response->charset(strtolower(Config::get('general.charset') ?? 'utf-8'));
                    } 
                    else 
                    {
						var_dump($this->_supported_formats[$this->response->format]);
						
						$this->response->type($this->_supported_formats[$this->response->format]);
						$this->response->charset(strtolower(Config::get('general.charset') ?? 'utf-8'));
                    }

                    // An array must be parsed as a string, so as not to cause an array to string error
                    // Json is the most appropriate form for such a data type
                    if ($this->response->format === 'array') 
                    {
                        $output = Format::factory($output)->{'to_json'}();
                    }
                } 
                else 
                {
                    // If an array or object, then parse as a json, so as to be a 'string'
                    if (is_array($data) || is_object($data)) {
                        $data = Format::factory($data)->{'to_json'}();
                    }
                    // Format is not supported, so output the raw data as a string
                    $output = $data;
                }
            }

            // If not greater than zero, then set the HTTP status code as 200 by default
            // Though perhaps 500 should be set instead, for the developer not passing a
            // correct HTTP status code
            $http_code > 0 OR $http_code = self::HTTP_OK;

            $this->response->statusCode($http_code);

            // JC: Log response code only if rest logging enabled
            if ($this->_config['rest_enable_logging'] === true) 
            {
                $this->_log_response_code($http_code);
            }

            // Output the data
            $this->response->body($output);

            if ($continue === false) 
            {
                // Display the data and exit execution
                $this->response->send();
                exit;
            } 
            else 
            {
                if (is_callable('fastcgi_finish_request')) 
                {
                    // Terminates connection and returns response to client on PHP-FPM.
                    $this->response->send();
                    ob_end_flush();
                    fastcgi_finish_request();
                    ignore_user_abort(true);
                } 
                else 
                {
                    // Legacy compatibility.
                    ob_end_flush();
                }
            }
            ob_end_flush();
        // Otherwise dump the output automatically
        } 
        else 
        {
            echo json_encode($data);
        }
    }

    /**
     * Takes mixed data and optionally a status code, then creates the response
     * within the buffers of the Output class. The response is sent to the client
     * lately by the framework, after the current controller's method termination.
     * All the hooks after the controller's method termination are executable.
     *
     * @param array|null $data      Data to output to the user
     * @param int|null   $http_code HTTP status code
     */
    public function set_response($data = null, $http_code = null)
    {
        $this->response($data, $http_code, true);
    }

    /**
     * Get the input format e.g. json or xml.
     *
     * @return string|null Supported input format; otherwise, NULL
     */
    protected function _detect_input_format()
    {
        // Get the CONTENT-TYPE value from the SERVER variable
        $content_type = $this->data->server('CONTENT_TYPE');

        if (empty($content_type) === false) {
            // If a semi-colon exists in the string, then explode by ; and get the value of where
            // the current array pointer resides. This will generally be the first element of the array
            $content_type = (strpos($content_type, ';') !== false ? current(explode(';', $content_type)) : $content_type);

            // Check all formats against the CONTENT-TYPE header
            foreach ($this->_supported_formats as $type => $mime) {
                // $type = format e.g. csv
                // $mime = mime type e.g. application/csv

                // If both the mime types match, then return the format
                if ($content_type === $mime) {
                    return $type;
                }
            }
        }
    }

    /**
     * Gets the default format from the configuration. Fallbacks to 'json'
     * if the corresponding configuration option $config['rest_default_format']
     * is missing or is empty.
     *
     * @return string The default supported input format
     */
    protected function _get_default_output_format()
    {
        $default_format = (string) $this->_config['rest_default_format'];

        return $default_format === '' ? 'json' : $default_format;
    }

    /**
     * Detect which format should be used to output the data.
     *
     * @return mixed|null|string Output format
     */
    protected function _detect_output_format()
    {
        // Concatenate formats to a regex pattern e.g. \.(csv|json|xml)
        $pattern = '/\.('.implode('|', array_keys($this->_supported_formats)).')($|\/)/';
        $matches = [];

        // Check if a file extension is used e.g. http://example.com/api/index.json?param1=param2
        if (preg_match($pattern, $this->request->url, $matches)) {
            return $matches[1];
        }

        // Get the format parameter named as 'format'
        if (isset($this->_get_args['format'])) {
            $format = strtolower($this->_get_args['format']);

            if (isset($this->_supported_formats[$format]) === true) {
                return $format;
            }
        }

        // Get the HTTP_ACCEPT server variable
        $http_accept = $this->data->server('HTTP_ACCEPT');

        // Otherwise, check the HTTP_ACCEPT server variable
        if ($this->_config['rest_ignore_http_accept'] === false AND $http_accept !== null) 
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) As $format) {
                // Has this format been requested?
                if (strpos($http_accept, $format) !== false) {
                    if ($format !== 'html' && $format !== 'xml') {
                        // If not HTML or XML assume it's correct
                        return $format;
                    } elseif ($format === 'html' && strpos($http_accept, 'xml') === false) {
                        // HTML or XML have shown up as a match
                        // If it is truly HTML, it wont want any XML
                        return $format;
                    } elseif ($format === 'xml' && strpos($http_accept, 'html') === false) {
                        // If it is truly XML, it wont want any HTML
                        return $format;
                    }
                }
            }
        }

        // Check if the controller has a default format
        if (empty($this->rest_format) === false) {
            return $this->rest_format;
        }

        // Obtain the default format from the configuration
        return $this->_get_default_output_format();
    }

    /**
     * Get the HTTP request string e.g. get or post.
     *
     * @return string|null Supported request method as a lowercase string; otherwise, NULL if not supported
     */
    protected function _detect_method()
    {
        // Declare a variable to store the method
        $method = null;

        // Determine whether the 'enable_emulate_request' setting is enabled
        if ($this->_config['enable_emulate_request'] === true) {
            $method = $this->data->post('_method');
            if ($method === null) {
                $method = $this->data->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            }
            $method = strtolower($method);
        }

        if (empty($method)) {
            // Get the request method as a lowercase string
            $method = strtolower($this->request->method());
        }

        return in_array($method, $this->allowed_http_methods) && method_exists($this, '_parse_'.$method) ? $method : 'get';
    }

    /**
     * See if the user has provided an API key.
     *
     * @return bool
     */
    protected function _detect_api_key()
    {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->_config['rest_key_name'];

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_'.strtoupper(str_replace('-', '_', $api_key_variable));

        $this->rest->key = null;
        $this->rest->level = null;
        $this->rest->user_id = null;
        $this->rest->ignore_limits = false;

        // Find the key from server or arguments
        if (($key = isset($this->_args[$api_key_variable]) ? $this->_args[$api_key_variable] : $this->input->server($key_name))) 
        {
            $row = $this->rest->db->query('SELECT * FROM '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_keys_table']).' WHERE '.$this->_config['rest_key_column'].' = ?', [$key])->fetch();
            if (empty($row)) 
            {
                return false;
            }

            $this->rest->key = $row->{$this->_config['rest_key_column']};

            isset($row->user_id) && $this->rest->user_id = $row->user_id;
            isset($row->level) && $this->rest->level = $row->level;
            isset($row->ignore_limits) && $this->rest->ignore_limits = $row->ignore_limits;

            $this->_apiuser = $row;

            /*
             * If "is private key" is enabled, compare the ip address with the list
             * of valid ip addresses stored in the database
             */
            if (empty($row->is_private_key) === false) {
                // Check for a list of valid ip addresses
                if (isset($row->ip_addresses)) {
                    // multiple ip addresses must be separated using a comma, explode and loop
                    $list_ip_addresses = explode(',', $row->ip_addresses);
                    $ip_address = $this->request->clientIp();
                    $found_address = false;

                    foreach ($list_ip_addresses as $list_ip) {
                        if ($ip_address === trim($list_ip)) {
                            // there is a match, set the the value to TRUE and break out of the loop
                            $found_address = true;
                            break;
                        }
                    }

                    return $found_address;
                }
                 else {
                    // There should be at least one IP address for this private key
                    return false;
                }
            }

            return true;
        }

        // No key has been sent
        return false;
    }

    /**
     * Preferred return language.
     *
     * @return string|null|array The language code
     */
    protected function _detect_lang()
    {
        $lang = $this->data->server('HTTP_ACCEPT_LANGUAGE');
        if ($lang === null) {
            return;
        }

        // It appears more than one language has been sent using a comma delimiter
        if (strpos($lang, ',') !== false) {
            $langs = explode(',', $lang);

            $return_langs = [];
            foreach ($langs as $lang) {
                // Remove weight and trim leading and trailing whitespace
                list($lang) = explode(';', $lang);
                $return_langs[] = trim($lang);
            }

            return $return_langs;
        }

        // Otherwise simply return as a string
        return $lang;
    }

    /**
     * Add the request to the log table.
     *
     * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
     *
     * @return bool TRUE the data was inserted; otherwise, FALSE
     */
    protected function _log_request($authorized = false)
    {
        // Insert the request into the log table
        $is_inserted = $this->rest->db->query('INSERT INTO '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_logs_table']).'(uri, method, params, api_key, ip_address, time, authorized) VALUES(:uri, :method, :params, :api_key, :ip_address, :time, :authorized)', [
            'uri'        => $this->request->url,
            'method'     => $this->request->method,
            'params'     => $this->_args ? ($this->_config['rest_logs_json_params'] === true ? json_encode($this->_args) : serialize($this->_args)) : null,
            'api_key'    => isset($this->rest->key) ? $this->rest->key : '',
            'ip_address' => $this->request->clientIp(),
            'time'       => time(),
            'authorized' => $authorized,
        ]);

        // Get the last insert id to update at a later stage of the request
        $this->_insert_id = $this->rest->db->db->pdo()->lastInsertId();

        return $is_inserted;
    }

    /**
     * Check if the requests to a controller method exceed a limit.
     *
     * @param string $controller_method The method being called
     *
     * @return bool TRUE the call limit is below the threshold; otherwise, FALSE
     */
    protected function _check_limit($controller_method)
    {
        // They are special, or it might not even have a limit
        if (empty($this->rest->ignore_limits) === false) {
            // Everything is fine
            return true;
        }
        $api_key = isset($this->rest->key) ? $this->rest->key : '';

        switch ($this->_config['rest_limits_method'])
        {
            case 'IP_ADDRESS':
                $api_key = $this->request->clientIp();
                $limited_uri = 'ip-address:'.$api_key;
                break;

            case 'API_KEY':
                $limited_uri = 'api-key:'.$api_key;
                break;

            case 'METHOD_NAME':
                $limited_uri = 'method-name:'.$controller_method;
                break;

            case 'ROUTED_URL':
            default:
                $limited_uri = $this->request->url;
                if (strpos(strrev($limited_uri), strrev($this->response->format)) === 0) {
                    $limited_uri = substr($limited_uri, 0, -strlen($this->response->format) - 1);
                }
                $limited_uri = 'uri:'.$limited_uri.':'.$this->request->method; // It's good to differentiate GET from PUT
                break;
        }

        if (isset($this->methods[$controller_method]['limit']) === false) {
            // Everything is fine
            return true;
        }

        // How many times can you get to this method in a defined time_limit (default: 1 hour)?
        $limit = $this->methods[$controller_method]['limit'];

        $time_limit = (isset($this->methods[$controller_method]['time']) ? $this->methods[$controller_method]['time'] : 3600); // 3600 = 60 * 60

        // Get data about a keys' usage and limit to one row
        $result = $this->rest->db->query('SELECT * FROM '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_limits_table']).' WHERE uri = uri AND api_key = :api_key', [
            'uri' => $limited_uri,
            'api_key' => $api_key
        ])->fetch();

        // No calls have been made for this key
        if (empty($result)) 
        {
            // Create a new row for the following key
            $this->rest->db->query('INSERT INTO '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_limits_table']).'(uri, api_key, count, hour_started) VALUES(:uri :api_key, :count, :hour_started)', [
                'uri'          => $limited_uri,
                'api_key'      => $api_key,
                'count'        => 1,
                'hour_started' => time(),
            ]);
        }
        // Been a time limit (or by default an hour) since they called
        elseif ($result->hour_started < (time() - $time_limit)) 
        {
            // Reset the started period and count
            $this->rest->db->query('UPDATE '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_limits_table']).' SET count = :count, hour_started = :hour_started WHERE uri = :uri AND api_key = :api_key', [
                'uri'          => $limited_uri,
                'api_key'      => $api_key,
                'count'        => 1,
                'hour_started' => time(),
            ]);
        }

        // They have called within the hour, so lets update
        else {
            // The limit has been exceeded
            if ($result->count >= $limit) {
                return false;
            }

            // Increase the count by one
            $this->rest->db->query('UPDATE '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_limits_table']).' SET count = :count WHERE uri = :uri AND api_key = :api_key', [
                'uri'          => $limited_uri,
                'api_key'      => $api_key,
                'count'        => 'count + 1',
            ]);
        }

        return true;
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called.
     *
     * @return bool
     */
    protected function _auth_override_check()
    {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = $this->_config['auth_override_class_method'] ?? null;

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method)) {
            // Check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method[strtolower(Dispatcher::getController())]['*'])) { // Check for class overrides
                // No auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[strtolower(Dispatcher::getController())]['*'] === 'none') {
                    return true;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[strtolower(Dispatcher::getController())]['*'] === 'basic') {
                    $this->_prepare_basic_auth();

                    return true;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method[strtolower(Dispatcher::getController())]['*'] === 'digest') {
                    $this->_prepare_digest_auth();

                    return true;
                }

                // Session auth override found, check session
                if ($auth_override_class_method[strtolower(Dispatcher::getController())]['*'] === 'session') {
                    $this->_check_php_session();

                    return true;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[strtolower(Dispatcher::getController())]['*'] === 'whitelist') {
                    $this->_check_whitelist_auth();

                    return true;
                }
            }

            // Check to see if there's an override value set for the current class/method being called
            if (!empty($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())] === 'none') {
                    return true;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())] === 'basic') {
                    $this->_prepare_basic_auth();

                    return true;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())] === 'digest') {
                    $this->_prepare_digest_auth();

                    return true;
                }

                // Session auth override found, check session
                if ($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())] === 'session') {
                    $this->_check_php_session();

                    return true;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())] === 'whitelist') {
                    $this->_check_whitelist_auth();

                    return true;
                }
            }
        }

        // Assign the class/method/HTTP-method auth type override array from the config
        $auth_override_class_method_http = $this->_config['auth_override_class_method_http'] ?? null;

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method_http)) {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())] === 'none') {
                    return true;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())] === 'basic') {
                    $this->_prepare_basic_auth();

                    return true;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())] === 'digest') {
                    $this->_prepare_digest_auth();

                    return true;
                }

                // Session auth override found, check session
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())] === 'session') {
                    $this->_check_php_session();

                    return true;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())]['*'][strtolower($this->request->method())] === 'whitelist') {
                    $this->_check_whitelist_auth();

                    return true;
                }
            }

            // Check to see if there's an override value set for the current class/method/HTTP-method being called
            if (!empty($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())] === 'none') {
                    return true;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())] === 'basic') {
                    $this->_prepare_basic_auth();

                    return true;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())] === 'digest') {
                    $this->_prepare_digest_auth();

                    return true;
                }

                // Session auth override found, check session
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())] === 'session') {
                    $this->_check_php_session();

                    return true;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[strtolower(Dispatcher::getController())][strtolower(Dispatcher::getMethod())][strtolower($this->request->method())] === 'whitelist') {
                    $this->_check_whitelist_auth();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parse the GET request arguments.
     *
     * @return void
     */
    protected function _parse_get()
    {
        // Merge both the URI segments and query parameters
        $this->_get_args = array_merge($this->_get_args, $this->_query_args);
    }

    /**
     * Parse the POST request arguments.
     *
     * @return void
     */
    protected function _parse_post()
    {
        $this->_post_args = $_POST;

        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
        }
    }

    /**
     * Parse the PUT request arguments.
     *
     * @return void
     */
    protected function _parse_put()
    {
        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
            if ($this->request->format === 'json') {
                $this->_put_args = json_decode($this->input->raw_input_stream);
            }
        } elseif ($this->input->method() === 'put') {
            // If no file type is provided, then there are probably just arguments
            $this->_put_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the HEAD request arguments.
     *
     * @return void
     */
    protected function _parse_head()
    {
        // Parse the HEAD variables
        parse_str(parse_url($this->data->server('REQUEST_URI'), PHP_URL_QUERY), $head);

        // Merge both the URI segments and HEAD params
        $this->_head_args = array_merge($this->_head_args, $head);
    }

    /**
     * Parse the OPTIONS request arguments.
     *
     * @return void
     */
    protected function _parse_options()
    {
        // Parse the OPTIONS variables
        parse_str(parse_url($this->data->server('REQUEST_URI'), PHP_URL_QUERY), $options);

        // Merge both the URI segments and OPTIONS params
        $this->_options_args = array_merge($this->_options_args, $options);
    }

    /**
     * Parse the PATCH request arguments.
     *
     * @return void
     */
    protected function _parse_patch()
    {
        // It might be a HTTP body
        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
        } elseif (strtolower($this->request->method()) === 'patch') {
            // If no file type is provided, then there are probably just arguments
            $this->_patch_args = $this->data->input_stream();
        }
    }

    /**
     * Parse the DELETE request arguments.
     *
     * @return void
     */
    protected function _parse_delete()
    {
        // These should exist if a DELETE request
        if (strtolower($this->request->method()) === 'delete') {
            $this->_delete_args = $this->data->input_stream();
        }
    }

    /**
     * Parse the query parameters.
     *
     * @return void
     */
    protected function _parse_query()
    {
        $this->_query_args = $this->data->get();
    }

    // INPUT FUNCTION --------------------------------------------------------------

    /**
     * Retrieve a value from a GET request.
     *
     * @param null $key       Key to retrieve from the GET request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the GET request; otherwise, NULL
     */
    public function get($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_get_args;
        }

        return isset($this->_get_args[$key]) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a OPTIONS request.
     *
     * @param null $key       Key to retrieve from the OPTIONS request.
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the OPTIONS request; otherwise, NULL
     */
    public function options($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_options_args;
        }

        return isset($this->_options_args[$key]) ? $this->_xss_clean($this->_options_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a HEAD request.
     *
     * @param null $key       Key to retrieve from the HEAD request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the HEAD request; otherwise, NULL
     */
    public function head($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_head_args;
        }

        return isset($this->_head_args[$key]) ? $this->_xss_clean($this->_head_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a POST request.
     *
     * @param null $key       Key to retrieve from the POST request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the POST request; otherwise, NULL
     */
    public function post($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_post_args;
        }

        return isset($this->_post_args[$key]) ? $this->_xss_clean($this->_post_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a PUT request.
     *
     * @param null $key       Key to retrieve from the PUT request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the PUT request; otherwise, NULL
     */
    public function put($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_put_args;
        }

        return isset($this->_put_args[$key]) ? $this->_xss_clean($this->_put_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a DELETE request.
     *
     * @param null $key       Key to retrieve from the DELETE request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the DELETE request; otherwise, NULL
     */
    public function delete($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_delete_args;
        }

        return isset($this->_delete_args[$key]) ? $this->_xss_clean($this->_delete_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from a PATCH request.
     *
     * @param null $key       Key to retrieve from the PATCH request
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the PATCH request; otherwise, NULL
     */
    public function patch($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_patch_args;
        }

        return isset($this->_patch_args[$key]) ? $this->_xss_clean($this->_patch_args[$key], $xss_clean) : null;
    }

    /**
     * Retrieve a value from the query parameters.
     *
     * @param null $key       Key to retrieve from the query parameters
     *                        If NULL an array of arguments is returned
     * @param null $xss_clean Whether to apply XSS filtering
     *
     * @return array|string|null Value from the query parameters; otherwise, NULL
     */
    public function query($key = null, $xss_clean = null)
    {
        if ($key === null) {
            return $this->_query_args;
        }

        return isset($this->_query_args[$key]) ? $this->_xss_clean($this->_query_args[$key], $xss_clean) : null;
    }

    /**
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.
     *
     * @param string $value     Input data
     * @param bool   $xss_clean Whether to apply XSS filtering
     *
     * @return string
     */
    protected function _xss_clean($value, $xss_clean)
    {
        is_bool($xss_clean) || $xss_clean = $this->_enable_xss;

        return $xss_clean === true ? Xss::clean($value) : $value;
    }

    /**
     * Retrieve the validation errors.
     *
     * @return array
     */
    public function validation_errors()
    {
        /* $string = strip_tags($this->form_validation->error_string());

        return explode(PHP_EOL, trim($string, PHP_EOL)); */
    }

    // SECURITY FUNCTIONS ---------------------------------------------------------

    /**
     * Perform LDAP Authentication.
     *
     * @param string $username The username to validate
     * @param string $password The password to validate
     *
     * @return bool
     */
    protected function _perform_ldap_auth($username = '', $password = null)
    {
    /*
        if (empty($username)) {
            //log_message('debug', 'LDAP Auth: failure, empty username');

            return false;
        }

        log_message('debug', 'LDAP Auth: Loading configuration');

        $this->config->load('ldap', true);

        $ldap = [
            'timeout' => $this->config->item('timeout', 'ldap'),
            'host'    => $this->config->item('server', 'ldap'),
            'port'    => $this->config->item('port', 'ldap'),
            'rdn'     => $this->config->item('binduser', 'ldap'),
            'pass'    => $this->config->item('bindpw', 'ldap'),
            'basedn'  => $this->config->item('basedn', 'ldap'),
        ];

        log_message('debug', 'LDAP Auth: Connect to '.(isset($ldap['host']) ? $ldap['host'] : '[ldap not configured]'));

        // Connect to the ldap server
        $ldapconn = ldap_connect($ldap['host'], $ldap['port']);
        if ($ldapconn) {
            log_message('debug', 'Setting timeout to '.$ldap['timeout'].' seconds');

            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, $ldap['timeout']);

            log_message('debug', 'LDAP Auth: Binding to '.$ldap['host'].' with dn '.$ldap['rdn']);

            // Binding to the ldap server
            $ldapbind = ldap_bind($ldapconn, $ldap['rdn'], $ldap['pass']);

            // Verify the binding
            if ($ldapbind === false) {
                log_message('error', 'LDAP Auth: bind was unsuccessful');

                return false;
            }

            log_message('debug', 'LDAP Auth: bind successful');
        }

        // Search for user
        if (($res_id = ldap_search($ldapconn, $ldap['basedn'], "uid=$username")) === false) {
            log_message('error', 'LDAP Auth: User '.$username.' not found in search');

            return false;
        }

        if (ldap_count_entries($ldapconn, $res_id) !== 1) {
            log_message('error', 'LDAP Auth: Failure, username '.$username.'found more than once');

            return false;
        }

        if (($entry_id = ldap_first_entry($ldapconn, $res_id)) === false) {
            log_message('error', 'LDAP Auth: Failure, entry of search result could not be fetched');

            return false;
        }

        if (($user_dn = ldap_get_dn($ldapconn, $entry_id)) === false) {
            log_message('error', 'LDAP Auth: Failure, user-dn could not be fetched');

            return false;
        }

        // User found, could not authenticate as user
        if (($link_id = ldap_bind($ldapconn, $user_dn, $password)) === false) {
            log_message('error', 'LDAP Auth: Failure, username/password did not match: '.$user_dn);

            return false;
        }

        log_message('debug', 'LDAP Auth: Success '.$user_dn.' authenticated successfully');

        $this->_user_ldap_dn = $user_dn;

        ldap_close($ldapconn);

    */
        return true;
    }

    /**
     * Perform Library Authentication - Override this function to change the way the library is called.
     *
     * @param string $username The username to validate
     * @param string $password The password to validate
     *
     * @return bool
     */
    protected function _perform_library_auth($username = '', $password = null)
    {
        if (empty($username)) {
            //log_message('error', 'Library Auth: Failure, empty username');

            return false;
        }

        $auth_library_class = strtolower($this->_config['auth_library_class']);
        $auth_library_function = strtolower($this->_config['auth_library_function']);

        if (empty($auth_library_class)) {
           // log_message('debug', 'Library Auth: Failure, empty auth_library_class');

            return false;
        }
        if (empty($auth_library_function)) {
            //log_message('debug', 'Library Auth: Failure, empty auth_library_function');

            return false;
        }

        if (is_callable([$auth_library_class, $auth_library_function]) === false) {
            $this->loadLibrary($auth_library_class);
        }

        return $this->{$auth_library_class}->$auth_library_function($username, $password);
    }

    /**
     * Check if the user is logged in.
     *
     * @param string      $username The user's name
     * @param bool|string $password The user's password
     *
     * @return bool
     */
    protected function _check_login($username = null, $password = false)
    {
        if (empty($username)) {
            return false;
        }

        $auth_source = strtolower($this->_config['auth_source']);
        $rest_auth = strtolower($this->_config['rest_auth']);
        $valid_logins = $this->_config['rest_valid_logins'];

        if (!$this->_config['auth_source'] AND $rest_auth === 'digest')
        {
            // For digest we do not have a password passed as argument
            return md5($username.':'.$this->_config['rest_realm'].':'.(isset($valid_logins[$username]) ? $valid_logins[$username] : ''));
        }

        if ($password === false) {
            return false;
        }

        if ($auth_source === 'ldap') {
            //log_message('debug', "Performing LDAP authentication for $username");

            return $this->_perform_ldap_auth($username, $password);
        }

        if ($auth_source === 'library') {
           // log_message('debug', "Performing Library authentication for $username");

            return $this->_perform_library_auth($username, $password);
        }

        if (array_key_exists($username, $valid_logins) === false) {
            return false;
        }

        if ($valid_logins[$username] !== $password) {
            return false;
        }

        return true;
    }

    /**
     * Check to see if the user is logged in with a PHP session key.
     *
     * @return void
     */
    protected function _check_php_session()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->_config['rest_ip_whitelist_enabled']) {
            $this->_check_whitelist_auth();
        }

        // Get the auth_source config item
        $key = $this->_config['auth_source'];

        // If false, then the user isn't logged in
        if (empty($this->data->session($key))) 
        {
            // Display an error response
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_unauthorized'],
            ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Prepares for basic authentication.
     *
     * @return void
     */
    protected function _prepare_basic_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->_config['rest_ip_whitelist_enabled']) {
            $this->_check_whitelist_auth();
        }

        // Returns NULL if the SERVER variables PHP_AUTH_USER and HTTP_AUTHENTICATION don't exist
        $username = $this->data->server('PHP_AUTH_USER');
        $http_auth = $this->data->server('HTTP_AUTHENTICATION') ?: $this->data->server('HTTP_AUTHORIZATION');

        $password = null;
        if ($username !== null) {
            $password = $this->data->server('PHP_AUTH_PW');
        } elseif ($http_auth !== null) {
            // If the authentication header is set as basic, then extract the username and password from
            // HTTP_AUTHORIZATION e.g. my_username:my_password. This is passed in the .htaccess file
            if (strpos(strtolower($http_auth), 'basic') === 0) {
                // Search online for HTTP_AUTHORIZATION workaround to explain what this is doing
                list($username, $password) = explode(':', base64_decode(substr($this->data->server('HTTP_AUTHORIZATION'), 6)));
            }
        }

        // Check if the user is logged into the system
        if ($this->_check_login($username, $password) === false) {
            $this->_force_login();
        }
    }

    /**
     * Prepares for digest authentication.
     *
     * @return void
     */
    protected function _prepare_digest_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->_config['rest_ip_whitelist_enabled']) {
            $this->_check_whitelist_auth();
        }

        // We need to test which server authentication variable to use,
        // because the PHP ISAPI module in IIS acts different from CGI
        $digest_string = $this->data->server('PHP_AUTH_DIGEST');
        if ($digest_string === null) {
            $digest_string = $this->data->server('HTTP_AUTHORIZATION');
        }

        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        if (empty($digest_string)) {
            $this->_force_login($unique_id);
        }

        // We need to retrieve authentication data from the $digest_string variable
        $matches = [];
        preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
        $digest = (empty($matches[1]) || empty($matches[2])) ? [] : array_combine($matches[1], $matches[2]);

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        if (isset($digest['username']) === false || $this->_check_login($digest['username'], true) === false) {
            $this->_force_login($unique_id);
        }

        $md5 = md5(strtoupper($this->request->method).':'.$digest['uri']);
        $valid_response = md5($digest['username'].':'.$digest['nonce'].':'.$digest['nc'].':'.$digest['cnonce'].':'.$digest['qop'].':'.$md5);

        // Check if the string don't compare (case-insensitive)
        if (strcasecmp($digest['response'], $valid_response) !== 0) {
            // Display an error response
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_invalid_credentials'],
            ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Checks if the client's ip is in the 'rest_ip_blacklist' config and generates a 401 response.
     *
     * @return void
     */
    protected function _check_blacklist_auth()
    {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->request->clientIp());

        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if (preg_match($pattern, $this->_config['rest_ip_blacklist'])) {
            // Display an error response
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_ip_denied'],
            ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Check if the client's ip is in the 'rest_ip_whitelist' config and generates a 401 response.
     *
     * @return void
     */
    protected function _check_whitelist_auth()
    {
        $whitelist = explode(',', $this->_config['rest_ip_whitelist']);

        array_push($whitelist, '127.0.0.1', '0.0.0.0');

        foreach ($whitelist as &$ip) {
            // As $ip is a reference, trim leading and trailing whitespace, then store the new value
            // using the reference
            $ip = trim($ip);
        }

        if (in_array($this->request->clientIp(), $whitelist) === false) {
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_ip_unauthorized'],
            ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Force logging in by setting the WWW-Authenticate header.
     *
     * @param string $nonce A server-specified data string which should be uniquely generated
     *                      each time
     *
     * @return void
     */
    protected function _force_login($nonce = '')
    {
        $rest_auth = strtolower($this->_config['rest_auth']);
        $rest_realm = $this->_config['rest_realm'];
        if ($rest_auth === 'basic') {
            // See http://tools.ietf.org/html/rfc2617#page-5
            header('WWW-Authenticate: Basic realm="'.$rest_realm.'"');
        } elseif ($rest_auth === 'digest') {
            // See http://tools.ietf.org/html/rfc2617#page-18
            header(
                'WWW-Authenticate: Digest realm="'.$rest_realm
                .'", qop="auth", nonce="'.$nonce
                .'", opaque="'.md5($rest_realm).'"');
        }

        if ($this->_config['strict_api_and_auth'] === true) {
            $this->is_valid_request = false;
        }

        // Display an error response
        $this->response([
            $this->_config['rest_status_field_name']  => false,
            $this->_config['rest_message_field_name'] => $this->_lang['text_rest_unauthorized'],
        ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Updates the log table with the total access time.
     *
     * @author Chris Kacerguis
     *
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_access_time()
    {
        if ($this->_insert_id == '') {
            return false;
        }
        try {
            $this->rest->db->query('UPDATE '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_logs_table']).' SET rtime = :rtime WHERE id = :id', [
                'id' => $this->_insert_id,
                'rtime' => $this->_end_rtime - $this->_start_rtime
            ]);
            return true;
        }
        catch(Exception $e) {
            return false;
        }
    }

    /**
     * Updates the log table with HTTP response code.
     *
     * @author Justin Chen
     *
     * @param $http_code int HTTP status code
     *
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_response_code($http_code)
    {
        if ($this->_insert_id == '') {
            return false;
        }
        try {
            $this->rest->db->query('UPDATE '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_logs_table']).' SET response_code = :response_code WHERE id = :id', [
                'id' => $this->_insert_id,
                'response_code' => $http_code
            ]);
            return true;
        }
        catch(Exception $e) {
            return false;
        }
    }

    /**
     * Check to see if the API key has access to the controller and methods.
     *
     * @return bool TRUE the API key has access; otherwise, FALSE
     */
    protected function _check_access()
    {
        // If we don't want to check access, just return TRUE
        if ($this->_config['rest_enable_access'] === false) 
        {
            return true;
        }
        // Fetch controller based on path and controller name
        $controller = implode(
            '/', [
            $this->router->directory,
            strtolower(Dispatcher::getController()),
        ]);

        // Remove any double slashes for safety
        $controller = str_replace('//', '/', $controller);

        //check if the key has all_access
        $accessRow = $this->rest->db->query('SELECT * FROM '.(($this->rest->db->db->config['prefix'] ?? '').$this->_config['rest_access_table']).' WHERE key = :key AND controller = :controller', [
            'key' => $this->rest->key,
            'controller' => $controller
        ])->fetch(\PDO::FETCH_ASSOC);

        if (!empty($accessRow) && !empty($accessRow['all_access'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks allowed domains, and adds appropriate headers for HTTP access control (CORS).
     *
     * @return void
     */
    protected function _check_cors()
    {
        // Convert the config items into strings
        $allowed_headers = implode(', ', $this->_config['allowed_cors_headers']);
        $allowed_methods = implode(', ', $this->_config['allowed_cors_methods']);

        // If we want to allow any domain to access the API
        if ($this->_config['allow_any_cors_domain'] === true) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: '.$allowed_headers);
            header('Access-Control-Allow-Methods: '.$allowed_methods);
        } else {
            // We're going to allow only certain domains access
            // Store the HTTP Origin header
            $origin = $this->data->server('HTTP_ORIGIN');
            if ($origin === null) {
                $origin = '';
            }

            // If the origin domain is in the allowed_cors_origins list, then add the Access Control headers
            if (in_array($origin, $this->_config['allowed_cors_origins'])) {
                header('Access-Control-Allow-Origin: '.$origin);
                header('Access-Control-Allow-Headers: '.$allowed_headers);
                header('Access-Control-Allow-Methods: '.$allowed_methods);
            }
        }

        // If there are headers that should be forced in the CORS check, add them now
        if (is_array($this->_config['forced_cors_headers'])) {
            foreach ($this->_config['forced_cors_headers'] as $header => $value) {
                header($header.': '.$value);
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            // Load DB if needed for logging
            if (!isset($this->rest->db) AND $this->_config['rest_enable_logging']) {
                $this->rest->db = new Query ($this->_config['rest_database_group']);
            }
            exit;
        }
    }
}
