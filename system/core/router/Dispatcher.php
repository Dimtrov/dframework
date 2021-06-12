<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\router;

use dFramework\components\rest\Controller;
use dFramework\core\exception\RouterException;
use dFramework\core\http\Middleware;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Uri;
use dFramework\core\loader\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
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
     * @var \dFramework\core\http\Response
     */
    private $response;
    /**
     * @var \dFramework\core\router\Router
     */
	private $router;
	/**
	 * @var \dFramework\core\http\Middleware
	 */
	private $middleware;
	/**
	 * @var \dFramework\core\debug\Timer
	 */
	private $timer;


	/**
	 * @var string
	 */
	private $controller;
	/**
	 * @var string
	 */
	private $method;
	/**
	 * Methodes reservees qui ne peuvent pas etre utilisee dans des routes
	 *
	 * @var array
	 */
	private $reservedMethods = [
		'_remap',
		'initialize',
		'middleware'
	];
	/**
	 * @var array
	 */
	private $parameters;
	/**
	 * @var array
	 */
	private $routeMiddlewares = [];

	/**
	 * @var float
	 */
	private $startTime;
	/**
	 * @var float
	 */
	private $totalTime = 0;


	private static $_instance = null;

	private $output = '';


    /**
     * Renvoi une instance unique de la classe
     *
     * @return self
     */
    public static function instance() : self
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

	/**
	 * Constructor
	 */
    private function __construct()
    {
		$this->request = Service::request();
		$this->response = Service::response();

		$this->middleware = new Middleware($this->response);

		$middlewaresFile = APP_DIR . 'config' . DS . 'middlewares.php';
		if (file_exists($middlewaresFile) AND !in_array($middlewaresFile, get_included_files()))
		{
			$middleware = require $middlewaresFile;
			if (is_callable($middleware))
			{
				$middlewareQueue = call_user_func($middleware, $this->middleware, $this->request);
				if ($middlewareQueue instanceof Middleware)
				{
					$this->middleware = $middlewareQueue;
				}
			}
		}
	}


    public static function init()
    {
		return self::instance()->run();
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
		if (empty($controller) OR !is_string($controller))
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


	private function run()
	{
		$this->startBenchmark();

		$this->spoofRequestMethod();

		/**
		 * Init event manager
		 */
		$events_file = APP_DIR . 'config' . DS . 'events.php';
		if (file_exists($events_file))
		{
			require_once $events_file;
		}
		Service::event()->trigger('pre_system');

		$routesFile = APP_DIR . 'config' . DS . 'routes.php';
		if (file_exists($routesFile))
		{
			require_once $routesFile;
		}
		if (empty($routes) OR ! $routes instanceof RouteCollection)
		{
			$routes = Service::routes();
		}

		/**
		 * Route middlewares
		 */
		$this->routeMiddlewares = (array) $this->dispatchRoutes($routes, $this->request);

		/*
		 * The bootstrapping in a middleware
		 */
		$this->middleware->append(function(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
			$resp = null;

			$resp = $this->startController();
			// Closure controller has run in startController().
			if (! is_callable($this->controller))
			{
				$controller = $this->createController();

				// Is there a "post_controller_constructor" event?
				Service::event()->trigger('post_controller_constructor');

				$resp = $this->runController($controller);
			}
			else
			{
				$this->timer->stop('controller_constructor');
				$this->timer->stop('controller');
			}

			Service::event()->trigger('post_system');


			if ($resp instanceof ResponseInterface)
			{
				$response = $resp;
			}

			return $response;
		});

		/**
		 * Add routes middlewares
		 */
		foreach ($this->routeMiddlewares as $middleware)
		{
			$this->middleware->prepend($middleware);
		}

		// Save our current URI as the previous URI in the session
		// for safer, more accurate use with `previous_url()` helper function.
		$this->storePreviousURL((string)current_url(true));

		/**
		 * Emission de la reponse
		 */
		$this->emitResponse($this->middleware->handle($this->request));
	}

	/**
	 * Gathers the script output from the buffer, replaces some execution
	 * time tag in the output and displays the debug toolbar, if required.
	 *
	 * @param ResponseInterface|mixed $returned
	 */
	protected function emitResponse($returned = null)
	{
		$this->output = ob_get_contents();
		// If buffering is not null.
		// Clean (erase) the output buffer and turn off output buffering
		if (ob_get_length())
		{
			ob_end_clean();
		}
		// If the controller returned a response object,
		// we need to grab the body from it so it can
		// be added to anything else that might have been
		// echoed already.
		// We also need to save the instance locally
		// so that any status code changes, etc, take place.
		if ($returned instanceof ResponseInterface)
		{
			$this->response = $returned;
			$returned       = $returned->getBody();
		}
		if (is_string($returned))
		{
			$this->output .= $returned;
			$this->output = $this->displayPerformanceMetrics($this->output);
		}
		/**
		 * @var \dFramework\core\http\Response
		 */
		$response = $this->response;
		if (empty($response->body()))
		{
			$this->response = $this->response->withBody(to_stream($this->output));
		}

		$this->totalTime = $this->timer->getElapsedTime('total_execution');


		Service::emitter()->emit(
			Service::toolbar()->prepare($this, $this->request, $this->response)
		);
	}


	//--------------------------------------------------------------------

	/**
	 * Start the Benchmark
	 *
	 * The timer is used to display total script execution both in the
	 * debug toolbar, and potentially on the displayed page.
	 */
	private function startBenchmark()
	{
		$this->startTime = microtime(true);

		$this->timer = Service::timer();
		$this->timer->start('total_execution', $this->startTime);
		$this->timer->start('bootstrap');
	}

	/**
	 * Modifies the Request Object to use a different method if a POST
	 * variable called _method is found.
	 */
	private function spoofRequestMethod()
	{
		// Only works with POSTED forms
		if ($this->request->getMethod() !== 'post')
		{
			return;
		}

		$post = $this->request->getParsedBody();

		if (empty($post['_method']))
		{
			return;
		}

		$this->request = $this->request->withMethod($post['_method']);
	}

    /**
	 * Works with the router to match a route against the current URI. If the route is a
	 * "redirect route", will also handle the redirect.
	 *
	 * @param RouteCollection $routes An collection interface to use in place of the config file.
	 * @param ServerRequest $request
	 * @return string|array
	 */
	private function dispatchRoutes(RouteCollection $routes, ServerRequest $request)
    {
		$this->router = new Router($routes, $request);

		$this->timer->stop('bootstrap');
		$this->timer->start('routing');
		ob_start();

		$this->controller     = $this->router->handle($request->url ?? null);
        $this->method         = $this->router->methodName();
        $this->parameters     = $this->router->params();
		$this->controllerFile = $this->router->controllerFile();

		// If a {locale} segment was matched in the final route,
		// then we need to set the correct locale on our Request.
		if ($this->router->hasLocale())
		{
			// $this->request->setLocale($this->router->getLocale());
		}

		$this->timer->stop('routing');

		return $this->router->getMiddleware();
    }

	/**
	 * Now that everything has been setup, this method attempts to run the
	 * controller method and make the script go. If it's not able to, will
	 * show the appropriate Page Not Found error.
	 */
	private function startController()
	{
		$this->timer->start('controller');
		$this->timer->start('controller_constructor');

		// No controller specified - we don't know what to do now.
		if (empty($this->controller))
		{
			RouterException::except(
				'empty controller',
				'No Controller specified.'
			);
		}

		// Is it routed to a Closure?
		if (is_object($this->controller) AND get_class($this->controller) === 'Closure')
		{
			$controller = $this->controller;

			return $controller(...$this->parameters);
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

		$reflectedClass = new ReflectionClass($this->controller);

		if (!method_exists($this->controller, $this->method))
		{
			if ($reflectedClass->isSubclassOf(Controller::class)) {
				$this->response = $this->response->withBody(to_stream(json_encode([
					'status' => false, 'message' => lang('rest.unknow_method')
				])))->withStatus(Controller::HTTP_NOT_ACCEPTABLE);

				return;
			}
			RouterException::except(
				'Method not found',
				'&laquo;<b>'.$this->method.'</b> method &raquo; is not defined in '.$this->controller,
				404
			);
		}

		$reflection = new ReflectionMethod($this->controller, $this->method);

        if ($reflection->getName() == "__construct")
        {
			if ($reflectedClass->isSubclassOf(Controller::class)) {
				$this->response = $this->response->withBody(to_stream(json_encode([
					'status' => false, 'message' => lang('rest.unauthorized')
				])))->withStatus(Controller::HTTP_FORBIDDEN);

				return;
			}
            RouterException::except(
				'Forbidden',
				'Access denied to <b>__construct</b> method'
				, 403
			);
        }
        if ($reflection->isProtected() OR $reflection->isPrivate())
        {
            if ($reflectedClass->isSubclassOf(Controller::class)) {
				$this->response = $this->response->withBody(to_stream(json_encode([
					'status' => false, 'message' => lang('rest.unauthorized')
				])))->withStatus(Controller::HTTP_FORBIDDEN);

				return;
			}
			RouterException::except(
				'Forbidden',
				'Access to <b>'. $reflection->getName().'</b> method is denied in '.$this->controller,
				403
			);
        }

		if (!in_array($reflection->getName(), $this->reservedMethods) AND preg_match('#^_#i', $reflection->getName()))
        {
			if ($reflectedClass->isSubclassOf(Controller::class)) {
				$this->response = $this->response->withBody(to_stream(json_encode([
					'status' => false, 'message' => lang('rest.unauthorized')
				])))->withStatus(Controller::HTTP_FORBIDDEN);

				return;
			}
			RouterException::except(
				'Forbidden',
				'Access denied to <b>'.$reflection->getName().'</b> method',
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
                if ($reflectedClass->isSubclassOf(Controller::class)) {
					return;
				}
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
	private function createController()
	{
		/**
		 * @var \dFramework\core\Controller
		 */
		$class = new $this->controller();

		$class->initialize($this->request, $this->response);

		return $class;
	}

	/**
	 * Runs the controller, allowing for _remap methods to function.
	 *
	 * @param mixed $class
	 *
	 * @return mixed
	 */
	private function runController($class)
	{
		// If this is a console request then use the input segments as parameters
		// $params = defined('SPARKED') ? $this->request->getSegments() : $this->parameters;
		$params = $this->parameters;

		if (method_exists($class, '_remap'))
		{
			$output = $class->_remap($this->method, (array) $params);
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
	private function display404errors(\Exception $e)
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
		$this->response = $this->response->withStatus($e->getCode());

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



	//--------------------------------------------------------------------

	/**
	 * If we have a session object to use, store the current URI
	 * as the previous URI. This is called just prior to sending the
	 * response to the client, and will make it available next request.
	 *
	 * This helps provider safer, more reliable previous_url() detection.
	 *
	 * @param \dFramework\core\http\URI|string $uri
	 */
	public function storePreviousURL($uri)
	{
		// Ignore CLI requests
		if (is_cli())
		{
			return;
		}
		// Ignore AJAX requests
		if (method_exists($this->request, 'isAJAX') AND $this->request->isAJAX())
		{
			return;
		}

		// This is mainly needed during testing...
		if (is_string($uri))
		{
			$uri = new URI($uri);
		}

		if (isset($_SESSION))
		{
			$_SESSION['_df_previous_url'] = (string) $uri;
		}
	}

	/**
	 * Replaces the memory_usage and elapsed_time tags.
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function displayPerformanceMetrics(string $output): string
	{
		$output = str_replace('{elapsed_time}', $this->totalTime, $output);

		return $output;
	}

	/**
	 * Returns an array with our basic performance stats collected.
	 *
	 * @return array
	 */
	public function getPerformanceStats(): array
	{
		return [
			'startTime' => $this->startTime,
			'totalTime' => $this->totalTime,
		];
	}
}
