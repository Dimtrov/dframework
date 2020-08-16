<?php

namespace dFramework\filters;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Cors implements MiddlewareInterface
{
    protected $config = [
        'AllowOrigin' => true,
        'AllowCredentials' => true,
        'AllowMethods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'AllowHeaders' => true,
        'ExposeHeaders' => false,
        'MaxAge' => 86400, // 1 day
    ];


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
     * PHPCS docblock fix needed!
     */
    private function _allowOrigin($request) 
    {
        $allowOrigin = $this->config['AllowOrigin'];
        $origin = $request->getHeaderLine('Origin');

        if ($allowOrigin === true || $allowOrigin === '*') {
            return $origin;
        }

        if (is_array($allowOrigin)) {
            $origin = (array) $origin;

            foreach ($origin as $o) {
                if (in_array($o, $allowOrigin)) {
                    return $origin;
                }
            }

            return '';
        }

        return (string)$allowOrigin;
    }

    /**
     * PHPCS docblock fix needed!
     */
    private function _allowCredentials() {
        return ($this->config['AllowCredentials']) ? 'true' : 'false';
    }

    /**
     * PHPCS docblock fix needed!
     */
    private function _allowMethods() {
        return implode(', ', (array) $this->config['AllowMethods']);
    }

    /**
     * PHPCS docblock fix needed!
     */
    private function _allowHeaders($request) {
        $allowHeaders = $this->config['AllowHeaders'];

        if ($allowHeaders === true) {
            return $request->getHeaderLine('Access-Control-Request-Headers');
        }

        return implode(', ', (array) $allowHeaders);
    }

    /**
     * PHPCS docblock fix needed!
     */
    private function _exposeHeaders() {
        $exposeHeaders = $this->config['ExposeHeaders'];

        if (is_string($exposeHeaders) || is_array($exposeHeaders)) {
            return implode(', ', (array) $exposeHeaders);
        }

        return '';
    }

    /**
     * PHPCS docblock fix needed!
     */
    private function _maxAge() {
        $maxAge = (string) $this->config['MaxAge'];

        return ($maxAge) ?: '0';
    }
}