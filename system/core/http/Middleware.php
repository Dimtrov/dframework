<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */
 
namespace dFramework\core\http;

use dFramework\core\exception\HttpException;
use dFramework\core\loader\Service;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware
 *
 * Gestionnaire des middlewares
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/middleware
 * @since       3.2.0
 * @file        /system/core/http/Middleware.php
 */
class Middleware implements RequestHandlerInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var array
     */
    private $middlewares = [];
    /**
     * @var integer
     */
    private $index = 0;
    

    /**
     * Contructor
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
     }

    /**
     * Ajoute un middleware a la chaine d'execution
     *
     * @param string|array|object|callable $middlewares
     * @return self
     */
    public function add($middlewares) : self
    {
        $middlewares = (array) $middlewares;

        foreach ($middlewares As $middleware)
        {
            $this->append($middleware);
        }        

        return $this;
    }

    /**
     * Ajoute un middleware en bout de chaine
     *
     * @param string|object|callable $middleware
     * @return self
     */
    public function append($middleware) : self 
    {
        $middleware = $this->makeMiddleware($middleware);
        array_push($this->middlewares, $middleware);

        return $this;
    }
    /**
     * Ajoute un middleware en debut de chaine
     *
     * @param string|object|callable $middleware
     * @return self
     */
    public function prepend($middleware) : self 
    {
        $middleware = $this->makeMiddleware($middleware);
        array_unshift($this->middlewares, $middleware);

        return $this;
    }

    /**
     * insert un middleware a une position donnee
     *
     * @param integer $index
     * @param string|object|callable $middleware
     * @alias insertAt
     * @return self
     */
    public function insert(int $index, $middleware) : self 
    {
        return $this->insertAt($index, $middleware);
    }
    /**
     * Insert a middleware callable at a specific index.
     *
     * If the index already exists, the new callable will be inserted,
     * and the existing element will be shifted one index greater.
     *
     * @param int $index The index to insert at.
     * @param string|object|callable $middleware The middleware to insert.
     * @return self
     */
    public function insertAt(int $index, $middleware) : self
    {
        $middleware = $this->makeMiddleware($middleware);
        array_splice($this->middlewares, $index, 0, $middleware);

        return $this;
    }

    /**
     * Insert a middleware object before the first matching class.
     *
     * Finds the index of the first middleware that matches the provided class,
     * and inserts the supplied callable before it.
     *
     * @param string $class The classname to insert the middleware before.
     * @param string|object|callable $middleware The middleware to insert.
     * @return self
     * @throws \LogicException If middleware to insert before is not found.
     */
    public function insertBefore(string $class, $middleware)
    {
        $found = false;
        $i = 0;
        foreach ($this->middlewares As $i => $object) 
        {
            if ((is_string($object) AND $object === $class) OR  is_a($object, $class)) 
            {
                $found = true;
                break;
            }
        }
        if ($found) 
        {
            return $this->insertAt($i, $middleware);
        }
        throw new LogicException(sprintf("No middleware matching '%s' could be found.", $class));
    }

    /**
     * Insert a middleware object after the first matching class.
     *
     * Finds the index of the first middleware that matches the provided class,
     * and inserts the supplied callable after it. If the class is not found,
     * this method will behave like add().
     *
     * @param string $class The classname to insert the middleware before.
     * @param string|object|callable $middleware The middleware to insert.
     * @return self
     */
    public function insertAfter(string $class, $middleware)
    {
        $found = false;
        $i = 0;
        foreach ($this->middlewares As $i => $object) 
        {
            if ((is_string($object) AND $object === $class) OR is_a($object, $class)) 
            {
                $found = true;
                break;
            }
        }
        if ($found) 
        {
            return $this->insertAt($i + 1, $middleware);
        }

        return $this->add($middleware);
    }

    

    
    /**
     * Execution du middleware
     *
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $middleware = $this->getMiddleware();
        
        if (empty($middleware)) 
        {
            return $this->response;
        }
        if (is_callable($middleware)) 
        {
            return $middleware($request, $this->response, [$this, 'handle']);
        }

        return $middleware->process($request, $this);
    }


    /**
     * Fabrique un middleware
     *
     * @param string|object|callable $middleware
     * @return object|callable
     */    
    private function makeMiddleware($middleware)
    {
        if (is_string($middleware))
        {
            return Service::container()->get($middleware);
        }
        if (is_callable($middleware) OR is_object($middleware)) 
        {
            return $middleware;
        }
        throw new HttpException("Unknow middleware type");
    }

    /**
     * Recuperation le middleware actuel
     *
     * @return object|callable
     */
    private function getMiddleware()
    {
        $middleware = null;

        if (isset($this->middlewares[$this->index]))
        {
            $middleware = $this->middlewares[$this->index];
        }
        $this->index++;

        return $middleware;
    }
}
