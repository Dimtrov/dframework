<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.0
 */

namespace dFramework\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Cors
 *  Middleware cors pour gerer les requetes d'origine croisees
 *
 * @package		dFramework
 * @subpackage	Middlewares
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Api.html
 * @since       3.1
 * @file        /system/middlewares/Cors.php
 */
class Cors implements MiddlewareInterface
{
    protected $config = [
        'AllowOrigin'      => true,
        'AllowCredentials' => true,
        'AllowMethods'     => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'AllowHeaders'     => true,
        'ExposeHeaders'    => false,
        'MaxAge'           => 86400,                                       // 1 day
    ];

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }


	/**
	 * Set MaxAge
	 *
	 * @param int|float $maxAge
	 * @return self
	 */
	public function setMaxAge($maxAge) : self
	{
		$this->config['MaxAge'] = $maxAge;

		return $this;
	}

	/**
	 * Set Exposes Headers
	 *
	 * @param bool|string|string[] $exposeHeaders
	 * @return self
	 */
	public function setExposeHeaders($exposeHeaders) : self
	{
		$this->config['ExposeHeaders'] = $exposeHeaders;

		return $this;
	}

	/**
	 * Set AllowHeaders
	 *
	 * @param bool|string|string[] $headers
	 * @return self
	 */
	public function setHeaders($headers) : self
	{
		$this->config['AllowHeaders'] = $headers;

		return $this;
	}

	/**
	 * Set AlloMethods
	 *
	 * @param string|string[] $methods
	 * @return self
	 */
	public function setMethods($methods) : self
	{
		$this->config['AlloMethods'] = $methods;

		return $this;
	}

	/**
	 * Set AllowCredentials
	 *
	 * @param bool $credentials
	 * @return self
	 */
	public function setCredentials($credentials) : self
	{
		$this->config['AllowCredentials'] = $credentials;

		return $this;
	}

	/**
	 * Set AllowOrigin
	 *
	 * @param bool|string|string[] $origin
	 * @return self
	 */
	public function setOrigin($origin) : self
	{
		$this->config['AllowOrigin'] = $origin;

		return $this;
	}


    /**
     * Process middleware
	 *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);

        if ($request->getHeaderLine('Origin'))
        {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $this->_allowOrigin($request))
                ->withHeader('Access-Control-Allow-Credentials', $this->_allowCredentials())
                ->withHeader('Access-Control-Max-Age', $this->_maxAge())
                ->withHeader('Access-Control-Expose-Headers', $this->_exposeHeaders())
            ;

            if (strtoupper($request->getMethod()) === 'OPTIONS')
            {
                $response = $response
                    ->withHeader('Access-Control-Allow-Headers', $this->_allowHeaders($request))
                    ->withHeader('Access-Control-Allow-Methods', $this->_allowMethods())
                ;
            }
        }

        return $response;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    private function _allowOrigin(ServerRequestInterface $request)
    {
        $allowOrigin = $this->config['AllowOrigin'];
        $origin = $request->getHeaderLine('Origin');

        if ($allowOrigin === true OR $allowOrigin === '*')
        {
            return $origin;
        }

        if (is_array($allowOrigin))
        {
            $origin = (array) $origin;

            foreach ($origin as $o)
            {
                if (in_array($o, $allowOrigin))
                {
                    return $origin;
                }
            }

            return '';
        }

        return (string) $allowOrigin;
    }

    /**
     *
     * @return string
     */
    private function _allowCredentials() : string
    {
        return ($this->config['AllowCredentials']) ? 'true' : 'false';
    }

    /**
     *
     * @return string
     */
    private function _allowMethods() : string
    {
        return implode(', ', (array) $this->config['AllowMethods']);
    }

    /**
     *
     *  @param ServerRequestInterface $request
     * @return string
     */
    private function _allowHeaders(ServerRequestInterface $request) : string
    {
        $allowHeaders = $this->config['AllowHeaders'];

        if ($allowHeaders === true)
        {
            return $request->getHeaderLine('Access-Control-Request-Headers');
        }

        return implode(', ', (array) $allowHeaders);
    }

    /**
     *
     * @return string
     */
    private function _exposeHeaders() : string
    {
        $exposeHeaders = $this->config['ExposeHeaders'];

        if (is_string($exposeHeaders) || is_array($exposeHeaders))
        {
            return implode(', ', (array) $exposeHeaders);
        }

        return '';
    }

    /**
     *
     * @return string
     */
    private function _maxAge() : string
    {
        $maxAge = (string) $this->config['MaxAge'];

        return ($maxAge) ?: '0';
    }
}
