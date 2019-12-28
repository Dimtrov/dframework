<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT)
 *
 * @package	dFramework
 * @author	Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Router
 *
 * Make a route
 *
 * @class       Router
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/routing
 * @filename	/system/core/route/Router.php
 */


namespace dFramework\core\route;


use dFramework\core\Config;
use dFramework\core\exception\RouterException;
use dFramework\core\utilities\Tableau;

class Router
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var array
     */
    private $routes = [];
    /**
     * @var array
     */
    private $namedRoutes = [];


    /**
     * Router constructor.
     * @param $url
     */
    private function __construct($url)
    {
        $this->url = $url;
        $this->routes = [
            'POST' => [],
            'GET' => [],
            'PUT' => [],
            'PATCH' => [],
            'DELETE' => [],
        ];
    }

    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * @return Router|null
     */
    private static function instance() : ?self
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new Router($_GET['url'] ?? '/');
        }
        return self::$_instance;
    }

    /**
     * @throws RouterException
     * @throws \ReflectionException
     */
    public static function init()
    {
        $instance = self::instance();

        $routes = Config::get('route');
        $routes = Tableau::remove($routes, 'default_controller');

        foreach ($routes As $key => $value)
        {
            $path = $key;
            if(!is_array($value))
            {
                $method = 'get';
                $callable = $value;
            }
            else
            {
                foreach ($value As $k => $v)
                {
                    $method = strtolower($k);
                    $callable = $v;
                }
            }
            $instance->$method($path, $callable);
        }
        $instance->run();
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws RouterException
     */
    public static function url(string $name, array $params = [])
    {
        $instance = self::instance();
        if(!isset($instance->namedRoutes[$name]))
        {
            throw new RouterException('No route matches this name', 404);
        }
        return $instance->namedRoutes[$name]->getUrl($params);
    }




    /**
     * @param string $path
     * @param callable|string $callable
     * @return Route
     */
    private function get(string $path, $callable)
    {
        return $this->add($path, $callable, 'GET');
    }
    /**
     * @param string $path
     * @param callable|string $callable
     * @return Route
     */
    private function post(string $path, $callable)
    {
        return $this->add($path, $callable, 'POST');
    }
    /**
     * @param string $path
     * @param callable|string $callable
     * @return Route
     */
    private function put(string $path, $callable)
    {
        return $this->add($path, $callable, 'PUT');
    }
    /**
     * @param string $path
     * @param callable|string $callable
     * @return Route
     */
    private function delete(string $path, $callable)
    {
        return $this->add($path, $callable, 'DELETE');
    }
    /**
     * @param string $path
     * @param callable|string $callable
     * @return Route
     */
    private function patch(string $path, $callable)
    {
        return $this->add($path, $callable, 'PATCH');
    }

    /**
     * @param $path
     * @param $callable
     * @param $method
     * @return Route
     */
    private function add($path, $callable, $method)
    {
        $route = new Route($path, $callable);
        $this->routes[$method][] = $route;
        if(is_string($callable))
        {
            $this->namedRoutes[$callable] = $route;
        }
        return $route;
    }

    /**
     * @return
     * @throws RouterException
     */
    private function run()
    {
        if(empty($_SERVER['REQUEST_METHOD']) OR !isset($this->routes[$_SERVER['REQUEST_METHOD']]))
        {
            throw new RouterException('REQUEST_METHOD does not exist', 405);
        }
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] As $route)
        {
            if($route->match($this->url))
            {
                return $route->call();
            }
        }
        Dispatcher::init();
    }

}
