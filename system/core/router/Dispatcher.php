<?php 
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.2
 */

namespace dFramework\core\router;

use dFramework\core\exception\RouterException;
use dFramework\core\http\ServerRequest;
use dFramework\core\loader\Service;
use ReflectionMethod;

/**
 * Dispatcher
 *
 * Dispatch a url request by creating the appropriate MVC controller
 * instance and runs its method by passing it the parameters.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Router
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file        /system/core/router/Dispatcher.php
 */
class Dispatcher 
{
    /**
     * @var \dFramework\core\http\ServerRequest
     */
    private $request;
    /**
     * @var Routere
     */
    private $router;

	/**
	 * @var string
	 */
	private $controller;
	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var array
	 */
	private $parameters;

    private static $_instance = null;
    /**
     * Renvoi une instance unique de la classe
     *
     * @return self
     */
    private static function instance() : self
    {
        if (null === self::$_instance) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private function __construct()
    {
        $this->request = Service::request();
    }

    public static function init()
    {
        require_once APP_DIR . 'config' . DS . 'routes.php';
        if (empty($routes) OR ! $routes instanceof RouteCollection)
		{
            $routes = Service::routes();
        }

		$instance = self::instance();

        $instance->dispatchRoutes($routes, $instance->request);
        
        $instance->startController();

        // Closure controller has run in startController().
		if (! is_callable($instance->controller))
		{
			$instance->runController($instance->createController());
		}
    }

	//--------------------------------------------------------------------

	/**
     * Recupere la classe chargee par la requete
     *
     * @return string
     */
    public static function getClass() : ?string
    {
		$controller = self::instance()->controller;
		if (empty($controller)) 
		{
			$controller = Service::routes()->defaultController();
		}
		return str_replace('Controller', '', $controller);
    }
    /**
     * Retourne la methode invoquee
     *
     * @return string
     */
    public static function getMethod() : ?string
    {
		$method = self::instance()->method;
		if (empty($method)) 
		{
			$method = Service::routes()->defaultMethod();
		}
		return $method;
    }
    /**
     * Recupere le controleur courant
     *
     * @return string
     */
    public static function getController() : ?string
    {
		return '';
        //return trim(self::instance()->current_subsystem, '/');
    }

	//--------------------------------------------------------------------

    /**
	 * Works with the router to match a route against the current URI. If the route is a
	 * "redirect route", will also handle the redirect.
	 *
	 * @param RouteCollection $routes An collection interface to use in place of the config file.
	 */
	private function dispatchRoutes(RouteCollection $routes, ServerRequest $request)
    {
        $this->router = new Router($routes, $request);
    
        $this->controller     = $this->router->handle($request->url ?? '/');
        $this->method         = $this->router->methodName();
        $this->parameters     = $this->router->params();
        $this->controllerFile = $this->router->controllerFile();

		// If a {locale} segment was matched in the final route,
		// then we need to set the correct locale on our Request.
		if ($this->router->hasLocale())
		{
			// $this->request->setLocale($this->router->getLocale());
		}        
    }
    
	/**
	 * Now that everything has been setup, this method attempts to run the
	 * controller method and make the script go. If it's not able to, will
	 * show the appropriate Page Not Found error.
	 */
	protected function startController()
	{
		// Is it routed to a Closure?
		if (is_object($this->controller) AND (get_class($this->controller) === 'Closure'))
		{
			$controller = $this->controller;
			return $controller(...$this->parameters);
		}

		// No controller specified - we don't know what to do now.
		if (empty($this->controller))
		{
			RouterException::except(
				'empty controller',
				'No Controller specified.'
			);
			throw new \Exception("PageNotFoundException::forEmptyController()");
		}

		// Try to autoload the class
		if (!class_exists($this->controller, true))
		{
			RouterException::except(
				'Controller not found',
				'Impossible to load the controller <b>'.preg_replace('#Controller$#', '',$this->controller).'</b>.
				<br>
				The file &laquo; '.$this->controllerFile.' &raquo; do not contain class <b>'.$this->controller.'</b>
			', 404);
		}

		if (!method_exists($this->controller, $this->method))
		{
			RouterException::except(
				'Method not found',
				'&laquo;<b>'.$this->method.'</b> method &raquo; is not defined in '.$this->controller, 
				404
			);
		}

		$reflection = new ReflectionMethod($this->controller, $this->method);

        if ($reflection->getName() == "__construct")
        {
            RouterException::except(
				'Forbidden',
				'Access denied to <b>__construct</b> method'
				, 403
			);
        }
        if (!in_array($reflection->getName(), ['_remap', 'before', 'after']) AND preg_match('#^_#i', $reflection->getName()))
        {
			RouterException::except(
				'Forbidden',
				'Access denied to <b>'.$reflection->getName().'</b> method', 
				403
			);
        }
        if ($reflection->isProtected() OR $reflection->isPrivate())
        {
            RouterException::except(
				'Forbidden',
				'Access to <b>'. $reflection->getName().'</b> method is denied in '.$this->controller, 
				403
			);
        }

        if ($this->method !== '_remap')
        {
            $params = $reflection->getParameters();
			$required_parameters = 0;
			
            foreach ($params As $param)
            {
				if (true !== $param->isOptional()) 
				{
                    $required_parameters++;
                }
            }
            if ($required_parameters > count($this->parameters))
            {
                RouterException::except(
					'Parameters error',
                    'The method <b>'.$this->method . '</b> of class '.$this->controller.' require
						<b>'.$required_parameters.'</b> parameters, '.count($this->parameters).' was send', 
					400
				);
            }
		}
    }
    
    /**
	 * Instantiates the controller class.
	 *
	 * @return mixed
	 */
	protected function createController()
	{
		$class = new $this->controller();
		//$class->initController($this->request, $this->response);

		return $class;
	}

	/**
	 * Runs the controller, allowing for _remap methods to function.
	 *
	 * @param mixed $class
	 *
	 * @return mixed
	 */
	protected function runController($class)
	{
		// If this is a console request then use the input segments as parameters
		$params = defined('SPARKED') ? $this->request->getSegments() : $this->parameters;

		if (method_exists($class, '_remap'))
		{
			$output = $class->_remap($this->method, ...$params);
		}
		else
		{
			$output = $class->{$this->method}(...$params);
		}
		
		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a 404 Page Not Found error. If set, will try to
	 * call the 404Override controller/method that was set in routing config.
	 *
	 * @param \Exception $e
	 */
	protected function display404errors(\Exception $e)
	{
		// Is there a 404 Override available?
		if ($override = $this->router->get404Override())
		{
			if ($override instanceof \Closure)
			{
				echo $override($e->getMessage());
			}
			else if (is_array($override))
			{
				$this->controller = $override[0];
				$this->method     = $override[1];

				unset($override);

				$controller = $this->createController();
				$this->runController($controller);
			}

			return;
		}

		// Display 404 Errors
		$this->response->setStatusCode($e->getCode());

		if (config('general.environment') !== 'test')
		{
			if (ob_get_level() > 0)
			{
				ob_end_flush();
			}
		}
		else
		{
			// When testing, one is for phpunit, another is for test case.
			if (ob_get_level() > 2)
			{
				ob_end_flush();
			}
		}

		throw new \Exception("PageNotFoundException::forPageNotFound($e->getMessage())");
	}
}