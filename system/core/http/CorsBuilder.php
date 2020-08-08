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

use Psr\Http\Message\MessageInterface;

/**
 * CorsBuilder
 * 
 * A builder object that assists in defining Cross Origin Request related
 * headers.
 *
 * Each of the methods in this object provide a fluent interface. Once you've
 * set all the headers you want to use, the `build()` method can be used to return
 * a modified Response.
 *
 * It is most convenient to get this object via `Request::cors()`.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @credit      Cake\HTTP\CorsBuilder (CakePHP 3.2.8 http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/http/CorsBuilder.php
 */
class CorsBuilder
{
    /**
     * The response object this builder is attached to.
     *
     * @var \Psr\Http\Message\MessageInterface
     */
    protected $_response;

    /**
     * The request's Origin header value
     *
     * @var string
     */
    protected $_origin;

    /**
     * Whether or not the request was over SSL.
     *
     * @var bool
     */
    protected $_isSsl;

    /**
     * The headers that have been queued so far.
     *
     * @var array
     */
    protected $_headers = [];

    /**
     * Constructor.
     *
     * @param \Psr\Http\Message\MessageInterface $response The response object to add headers onto.
     * @param string $origin The request's Origin header.
     * @param bool $isSsl Whether or not the request was over SSL.
     */
    public function __construct(MessageInterface $response, $origin, $isSsl = false)
    {
        $this->_origin = $origin;
        $this->_isSsl = $isSsl;
        $this->_response = $response;
    }

    /**
     * Apply the queued headers to the response.
     *
     * If the builder has no Origin, or if there are no allowed domains,
     * or if the allowed domains do not match the Origin header no headers will be applied.
     *
     * @return \Psr\Http\Message\MessageInterface A new instance of the response with new headers.
     */
    public function build()
    {
        $response = $this->_response;
        if (empty($this->_origin)) {
            return $response;
        }

        if (isset($this->_headers['Access-Control-Allow-Origin'])) {
            foreach ($this->_headers as $key => $value) {
                $response = $response->withHeader($key, $value);
            }
        }

        return $response;
    }

    /**
     * Set the list of allowed domains.
     *
     * Accepts a string or an array of domains that have CORS enabled.
     * You can use `*.example.com` wildcards to accept subdomains, or `*` to allow all domains
     *
     * @param string|string[] $domains The allowed domains
     * @return $this
     */
    public function allowOrigin($domains)
    {
        $allowed = $this->_normalizeDomains((array)$domains);
        foreach ($allowed as $domain) {
            if (!preg_match($domain['preg'], $this->_origin)) {
                continue;
            }
            $value = $domain['original'] === '*' ? '*' : $this->_origin;
            $this->_headers['Access-Control-Allow-Origin'] = $value;
            break;
        }

        return $this;
    }

    /**
     * Normalize the origin to regular expressions and put in an array format
     *
     * @param string[] $domains Domain names to normalize.
     * @return array
     */
    protected function _normalizeDomains($domains)
    {
        $result = [];
        foreach ($domains as $domain) {
            if ($domain === '*') {
                $result[] = ['preg' => '@.@', 'original' => '*'];
                continue;
            }

            $original = $preg = $domain;
            if (strpos($domain, '://') === false) {
                $preg = ($this->_isSsl ? 'https://' : 'http://') . $domain;
            }
            $preg = '@^' . str_replace('\*', '.*', preg_quote($preg, '@')) . '$@';
            $result[] = compact('original', 'preg');
        }

        return $result;
    }

    /**
     * Set the list of allowed HTTP Methods.
     *
     * @param string[] $methods The allowed HTTP methods
     * @return $this
     */
    public function allowMethods(array $methods)
    {
        $this->_headers['Access-Control-Allow-Methods'] = implode(', ', $methods);

        return $this;
    }

    /**
     * Enable cookies to be sent in CORS requests.
     *
     * @return $this
     */
    public function allowCredentials()
    {
        $this->_headers['Access-Control-Allow-Credentials'] = 'true';

        return $this;
    }

    /**
     * Whitelist headers that can be sent in CORS requests.
     *
     * @param string[] $headers The list of headers to accept in CORS requests.
     * @return $this
     */
    public function allowHeaders(array $headers)
    {
        $this->_headers['Access-Control-Allow-Headers'] = implode(', ', $headers);

        return $this;
    }

    /**
     * Define the headers a client library/browser can expose to scripting
     *
     * @param string[] $headers The list of headers to expose CORS responses
     * @return $this
     */
    public function exposeHeaders(array $headers)
    {
        $this->_headers['Access-Control-Expose-Headers'] = implode(', ', $headers);

        return $this;
    }

    /**
     * Define the max-age preflight OPTIONS requests are valid for.
     *
     * @param int $age The max-age for OPTIONS requests in seconds
     * @return $this
     */
    public function maxAge($age)
    {
        $this->_headers['Access-Control-Max-Age'] = $age;

        return $this;
    }
}
