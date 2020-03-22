<?php 


namespace dFramework\components\rest;

use dFramework\core\Controller as CoreController;
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
     * Uri
     */
    private $_uri = null;

    public function index(){}

    public function __construct()
    {
        parent::__construct();

        $this->response->type('application/json; charset=UTF-8');

        $this->_uri = Uri::instance();

        $this->_runMethod();
    }


    protected function response($data, int $status = self::HTTP_OK)
    {
        $this->response->statusCode($status);
        $this->response->body(json_encode($data));
        
        return $this->response->send();
    }


    private function _runMethod()
    {
        // Create an argument container if it doesn't exist e.g. _get_args
        if (isset($this->{'_'.strtolower($this->request->method()).'_args'}) === false) 
        {
            $this->{'_'.strtolower($this->request->method()).'_args'} = [];
        }

        // Set up the query parameters
        $this->_parse_query();

        // Set up the GET variables
        $this->_get_args = array_merge($this->_get_args, $this->_uri->ruri_to_assoc());

        $this->{'_parse_'.strtolower($this->request->method())}();

        // Fix parse method return arguments null
        if ($this->{'_'.strtolower($this->request->method()).'_args'} === null) 
        {
            $this->{'_'.strtolower($this->request->method()).'_args'} = [];
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
/* 
        // Only allow ajax requests
        if ($this->request->is('ajax') === false AND $this->_config['rest_ajax_only']) 
        {
            // Display an error response
            $this->response([
                $this->_config['rest_status_field_name']  => false,
                $this->_config['rest_message_field_name'] => $this->_lang['text_rest_ajax_only'],
            ], self::HTTP_NOT_ACCEPTABLE);
        } */

        
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
    private function _parse_post()
    {
        $this->_post_args = $this->request->data;
    }
    /**
     * Parse the PUT request arguments.
     *
     * @return void
     */
    private function _parse_put()
    {
        $this->_put_args = $this->input->input_stream();
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
        $this->_patch_args = $this->data->input_stream();
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
        $this->_query_args = $this->request->query;
    }

}