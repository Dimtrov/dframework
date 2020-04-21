<?php 


namespace dFramework\components\rest;

use dFramework\core\Config;
use dFramework\core\Controller as CoreController;
use dFramework\core\loader\Load;
use dFramework\core\route\Dispatcher;
use dFramework\core\utilities\Uri;

class Controller extends CoreController
{

    /**
     * Common HTTP status codes and their respective description.
     *
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    const HTTP_OK                 = 200;
    const HTTP_CREATED            = 201;
    const HTTP_NOT_MODIFIED       = 304;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_INTERNAL_ERROR     = 500;


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
    protected $allowed_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

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



    private $_lang;

    public function index(){}

    public function __construct()
    {
        parent::__construct();

        $this->response->type('application/json');
        $this->response->charset(strtolower(Config::get('general.charset') ?? 'utf-8'));
        
        Load::lang('component.rest', $this->_lang, null, false);
        $this->_lang = (array) $this->_lang;
    }


    protected function response($data, int $status = self::HTTP_OK, bool $continue = false)
    {
        $this->response->statusCode($status);
        $this->response->body(json_encode($data));
        $this->response->send();

        if (!$continue)
        {
            exit;
        }
    }


    protected function allowed_methods(string ...$methods)
    {
        $methods = array_map(function($str) {
            return strtolower($str);
        }, $methods);

        if (!in_array(strtolower($this->request->method()), $methods))
        {
            return $this->response($this->_lang['unknown_method'], self::HTTP_METHOD_NOT_ALLOWED);
        }
    }

}