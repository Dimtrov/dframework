<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core\route;

use dFramework\core\Config;
use dFramework\core\exception\RouterException;
use dFramework\core\utilities\Tableau;
use dFramework\core\data\Request;

/**
 * Router
 *
 * Make a route
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.0
 * @file	    /system/core/route/Router.php
 */

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
            self::$_instance = new Router((new Request)->url ?? '/');
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
                $callable = $value;
                $methods = ['post', 'put', 'patch', 'delete'];
                foreach($methods As $method) 
                {
                    $instance->add($path, $callable, strtoupper($method));
                }
                $method = 'get';
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
            RouterException::except('No route matches this name', 404);
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
        $method = (new Request)->method();
        
        if(empty($method) OR !isset($this->routes[strtoupper($method)]))
        {
            if('cli' !== php_sapi_name())
            {
                throw new RouterException('REQUEST_METHOD does not exist', 405);
            }
        }
        if(!empty($this->routes[strtoupper($method)]))
        {
            foreach ($this->routes[strtoupper($method)] As $route)
            {
                if($route->match($this->url))
                {
                    return $route->call();
                }
            }
        }
        Dispatcher::init();
    }

}
