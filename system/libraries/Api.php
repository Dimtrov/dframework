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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

require_once SYST_DIR.'dependencies'.DS.'requests'.DS.'Requests.php';

/**
 * API
 *  Permet d'acceder a des APIs distants
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/Api.html
 */

class dF_Api
{
    const  BRUT = 1;

    const FORMAT = 2;

    private $base_url = '';

    private $return_type = self::FORMAT;


    /**
     * dF_Api constructor.
     */
    public function __construct()
    {
        Requests::register_autoloader();
    }

    /**
     * @param string $url
     */
    public function baseUrl(string $url)
    {
        $this->base_url = $url;
    }

    /**
     * @param $type
     */
    public function returnType($type)
    {
        $this->return_type = $type;
    }

    /**
     * Send a GET request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $options
     * @return Requests_Response
     */
    public function get(string $url, ?array $headers = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::get($url, $headers, $options));
    }

    /**
     * Send a HEAD request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $options
     * @return Requests_Response
     */
    public function head(string $url, ?array $headers = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::head($url, $headers, $options));
    }

    /**
     * Send a DELETE request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $options
     * @return Requests_Response
     */
    public function delete(string $url, ?array $headers = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::delete($url, $headers, $options));
    }

    /**
     * Send a TRACE request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $options
     * @return Requests_Response
     */
    public function trace(string $url, ?array $headers = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::trace($url, $headers, $options));
    }

     /**
     * Send a POST request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $data
     * @param array|null $options
     * @return Requests_Response
     */
    public function post(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::post($url, $headers, $data, $options));
    }

     /**
     * Send a PUT request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $data
     * @param array|null $options
     * @return Requests_Response
     */
    public function put(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::put($url, $headers, $data, $options));
    }

     /**
     * Send an OPTIONS request
     *
     * @param string $url
     * @param array|null $headers
     * @param array|null $data
     * @param array|null $options
     * @return Requests_Response
     */
    public function options(string $url, ?array $headers = [], ?array $data = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::options($url, $headers, $data, $options));
    }

     /**
     * Send a PATCH request
     *
     * @param string $url
     * @param array $headers
     * @param array|null $data
     * @param array|null $options
     * @return Requests_Response
     */
    public function patch(string $url, array $headers, ?array $data = [], ?array $options = [])
    {
        $url = rtrim($this->base_url, '/').'/'.ltrim($url, '/');
        return $this->return(Requests::patch($url, $headers, $data, $options));
    }


    /**
     * @param Requests_Response $response
     * @return Requests_Response
     */
    private function return(Requests_Response $response)
    {
        if($this->return_type === self::BRUT)
        {
            return $response;
        }
        $return['meta'] = [
            'code' => $response->status_code,
            'success' => $response->success,
            'content-type' => $response->headers->data['content-type'][0]
        ];
        $return['data'] = $response->body;

        return $return;
    }
}