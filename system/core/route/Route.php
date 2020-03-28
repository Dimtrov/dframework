<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MIT	MIT License
 * @homepage	https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\route;

/**
 * Route
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.0
 * @file        /system/core/route/Route.php
 */

class Route
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string|callable
     */
    private $callable;
    /**
     * @var array
     */
    private $matches;

    /**
     * Route constructor.
     * @param string $path
     * @param callable|string $callable
     */
    public function __construct(string $path, $callable)
    {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function match(string $url) : bool
    {
        $url = trim($url, '/');
        if(!preg_match('#^'.$this->path.'$#i', $url, $matches))
        {
            return false;
        }
        array_shift($matches);
        $this->matches = $matches;
        return true;
    }


    /**
     * @throws \ReflectionException
     * @throws \dFramework\core\exception\LoadException
     * @throws \dFramework\core\exception\RouterException
     */
    public function call()
    {
        if(is_string($this->callable))
        {
            $params = explode('::', $this->callable);
            $controllerClassFile = explode('/', $params[0].'Controller');
            $controllerClass = array_pop($controllerClassFile);
            $controllerClassFile = implode('/', $controllerClassFile);

            $controllerClassFile = (empty($controllerClassFile))
                ? CONTROLLER_DIR.$controllerClass
                : CONTROLLER_DIR.rtrim($controllerClassFile, '/').DS.$controllerClass;

            Dispatcher::loadController($controllerClassFile, $controllerClass, $params[1] ?? 'index', $this->matches);
        }
        else
        {
            call_user_func_array($this->callable, $this->matches);
        }
    }


    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params) : string
    {
        $path = $this->path;
        foreach($params as $k => $v)
        {
            $path = str_replace(":$k", $v, $path);
        }
        return $path;
    }
}
