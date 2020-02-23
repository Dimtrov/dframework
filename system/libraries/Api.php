<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


/**
 * API
 *  Permet d'acceder a des APIs distants
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/Api.html
 * @since       2.0.1
 */

class dF_Api
{
    /**
     * Donnees bruts (toutes les donnees, memes les entetes)
     */
    const  BRUT = 1;
    /**
     * Les donnees formatees (DATAS + METAS )
     */ 
    const FORMAT = 2;

    /**
     * Donnees specifiquement renvoyes
     */
    const   DATAS = 3;
    /**
     * Les meta donnees simples
     */
    const   METAS = 4;
    /**
     * Les headers
     */
    const   HEADERS = 5;


    /**
     * @var string
     */
    private $base_url = '';

    /**
     * @var int
     */
    private $return_type = self::DATAS;


    /**
     * dF_Api constructor.
     */
    public function __construct()
    {
        Requests::register_autoloader();
    }

    /**
     * Definit l'URL de base pour l'appel des services externes
     *
     * @param string $url
     * @return void
     */
    public function baseUrl(string $url) : void
    {
        $this->base_url = $url;
    }

    /**
     * Definit le type de formatage du resultat
     *
     * @param int $type dF_Api::FORMAT | dF_Api::BRUT
     * @return void
     */
    public function returnType(int $type) : void
    {
        $this->return_type = $type;
    }



    /**
     * Main interface for HTTP requests
     *
     * This method initiates a request and sends it via a transport before
     * parsing.
     *
     * The `$options` parameter takes an associative array with the following
     * options:
     *
     * - `timeout`: How long should we wait for a response?
     *    Note: for cURL, a minimum of 1 second applies, as DNS resolution
     *    operates at second-resolution only.
     *    (float, seconds with a millisecond precision, default: 10, example: 0.01)
     * - `connect_timeout`: How long should we wait while trying to connect?
     *    (float, seconds with a millisecond precision, default: 10, example: 0.01)
     * - `useragent`: Useragent to send to the server
     *    (string, default: php-requests/$version)
     * - `follow_redirects`: Should we follow 3xx redirects?
     *    (boolean, default: true)
     * - `redirects`: How many times should we redirect before erroring?
     *    (integer, default: 10)
     * - `blocking`: Should we block processing on this request?
     *    (boolean, default: true)
     * - `filename`: File to stream the body to instead.
     *    (string|boolean, default: false)
     * - `auth`: Authentication handler or array of user/password details to use
     *    for Basic authentication
     *    (Requests_Auth|array|boolean, default: false)
     * - `proxy`: Proxy details to use for proxy by-passing and authentication
     *    (Requests_Proxy|array|string|boolean, default: false)
     * - `max_bytes`: Limit for the response body size.
     *    (integer|boolean, default: false)
     * - `idn`: Enable IDN parsing
     *    (boolean, default: true)
     * - `transport`: Custom transport. Either a class name, or a
     *    transport object. Defaults to the first working transport from
     *    {@see getTransport()}
     *    (string|Requests_Transport, default: {@see getTransport()})
     * - `hooks`: Hooks handler.
     *    (Requests_Hooker, default: new Requests_Hooks())
     * - `verify`: Should we verify SSL certificates? Allows passing in a custom
     *    certificate file as a string. (Using true uses the system-wide root
     *    certificate store instead, but this may have different behaviour
     *    across transports.)
     *    (string|boolean, default: library/Requests/Transport/cacert.pem)
     * - `verifyname`: Should we verify the common name in the SSL certificate?
     *    (boolean: default, true)
     * - `data_format`: How should we send the `$data` parameter?
     *    (string, one of 'query' or 'body', default: 'query' for
     *    HEAD/GET/DELETE, 'body' for POST/PUT/OPTIONS/PATCH)
     *
    */


    /**
     * Send a GET request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function get(string $url, ?array $headers = [], ?array $options = [])
    {
        return $this->return(Requests::get($this->url($url), $headers, $options));
    }

    /**
     * Send a HEAD request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function head(string $url, ?array $headers = [], ?array $options = [])
    {
        return $this->return(Requests::head($this->url($url), $headers, $options));
    }

    /**
     * Send a DELETE request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function delete(string $url, ?array $headers = [], ?array $options = [])
    {
        return $this->return(Requests::delete($this->url($url), $headers, $options));
    }

    /**
     * Send a TRACE request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function trace(string $url, ?array $headers = [], ?array $options = [])
    {
        return $this->return(Requests::trace($this->url($url), $headers, $options));
    }

     /**
     * Send a POST request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function post(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        return $this->return(Requests::post($this->url($url), $headers, $data, $options));
    }

     /**
     * Send a PUT request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function put(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        return $this->return(Requests::put($this->url($url), $headers, $data, $options));
    }

     /**
     * Send an OPTIONS request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function options(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        return $this->return(Requests::options($this->url($url), $headers, $data, $options));
    }

     /**
     * Send a PATCH request
     *
     * @param string $url URL to request
     * @param array|null $headers Extra headers to send with request
     * @param array|null $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param array|null $options Options for request
     * @return Requests_Response|array
     */
    public function patch(string $url, array $headers, ?array $data = [], ?array $options = [])
    {
        return $this->return(Requests::patch($this->url($url), $headers, $data, $options));
    }


    /**
     * @param string $url
     * @return string
     */
    private function url(string $url) : string
    {
        if(preg_match('#^(?:-|/)#', $url)) 
        {
            $url = trim(substr($url, 1));
        }
        else if(!empty($this->base_url))
        {
            $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        }
        return $url;
    }

    /**
     * @param Requests_Response $response
     * @return Requests_Response|array
     */
    private function return(Requests_Response $response)
    {
        if(self::HEADERS === $this->return_type)
        {
            return $response->headers;
        }
        if($this->return_type === self::BRUT)
        {
            return $response;
        }
        $return = [
            'metas' => [
                'code' => $response->status_code,
                'success' => $response->success,
                'content-type' => $response->headers->data['content-type'][0]
            ],
            'datas' => $response->body
        ];
        if(self::DATAS === $this->return_type)
        {
            return $return['data'];
        }
        if(self::METAS === $this->return_type)
        {
            return $return['metas'];
        }
        return $return;
    }
}