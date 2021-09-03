<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\controllers;

use BadMethodCallException;
use dFramework\core\http\Middleware;
use dFramework\core\loader\Injector;
use dFramework\core\loader\Load;
use dFramework\core\utilities\Arr;
use ReflectionClass;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * BaseController
 *
 * A global controller of system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Controllers
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       3.4.0
 * @file		/system/core/controllers/BaseController.php
 */
class BaseController
{
	/**
     * Common HTTP status codes and their respective description.
     *
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
	const HTTP_OK                 = 200;
	const HTTP_CREATED            = 201;
	const HTTP_NO_CONTENT         = 204;
	const HTTP_NOT_MODIFIED       = 304;
	const HTTP_BAD_REQUEST        = 400;
	const HTTP_UNAUTHORIZED       = 401;
	const HTTP_FORBIDDEN          = 403;
	const HTTP_NOT_FOUND          = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE     = 406;
	const HTTP_CONFLICT           = 409;
	const HTTP_INVALID_TOKEN      = 498;
	const HTTP_INTERNAL_ERROR     = 500;
	const HTTP_NOT_IMPLEMENTED    = 501;

	/**
     * @var \dFramework\core\http\ServerRequest Instance de l'objet Request
     */
    protected $request;
    /**
     * @var \dFramework\core\http\Response Instance de l'objet Response
     */
    protected $response;

    /**
     * @var array Données partagées entre toutes les vue chargées à partir d'un controleur
     */
    protected $view_datas = [];
    /**
     * @var string Layout a utiliser
     */
    protected $layout = null;

    private $_middlewares = [];


    /**
     * Constructor
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function initialize(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;

        /**
         * Lance les filtres http
         */
        $this->_launchMiddlewares();

		/**
         * Recupere les elements
         */
        $this->_getElements();
    }
	public function __construct()
	{

	}


    /**
     * Defini une liste de filtres specifique a la methode
     *
     * @param string|string[] $middlewares
     * @return self
     */
    final protected function useMiddleware($middlewares) : self
    {
        $this->_middlewares = (array) $middlewares;

        return $this;
    }

    /**
     * Execute des middlewares
     *
     * @param mixed $middlewares
     * @return void
     */
    final protected function runMiddleware($middlewares)
    {
        $middleware = new Middleware($this->response);
        if (!empty($this->_middlewares))
        {
            $middleware->add($this->_middlewares);
        }
        $middleware->add($middlewares);

        $this->response = $middleware->handle($this->request);
    }


    /**
     * Validation rapide de donnees
     *
     * @param array $rules
     * @param array|null $data
     * @param string|null $locale
     * @return bool
     * @throws exception\LoadException
     */
    final protected function validate(array $rules, ?array $data = [], ?string $locale = null)
    {
        if (!Arr::isAssoc($rules))
        {
            throw new BadMethodCallException('Mauvaise utilisation de la methode '. __METHOD__);
        }
        if (empty($data)) {
            $data = $this->request->getParsedBody();
        }
        $this->loadLibrary('Validator');
        $this->validator->init($locale, $data);

        foreach ($rules As $field => $rule)
        {
            $rule = (array) $rule;
            $messages = $rule[1] ?? [];
            $rule = explode('|', $rule[0]);

            $field = explode('|', $field);
            $label = $field[1] ?? '';
            $field = $field[0];

            for ($i = 0, $size = count($rule); $i < $size; $i++)
            {
                $r = $rule[$i];
                $params = [];

                if (preg_match('#^([a-z-_]+){(.+)}$#isU', $r, $p))
                {
                    $params = explode(',', $p[2] ?? '');
                    $r = $p[1] ?? '';
                }
                if (!empty($r))
                {
                    call_user_func([$this->validator, $r], $field, ...$params);

                    if (!empty($messages[$i]))
                    {
                        $this->validator->message($messages[$i]);
                    }
                    if (!empty($label))
                    {
                        $this->validator->label($label);
                    }
                }
            }
        }

        return $this->validator->validate();
    }


    /**
     * Charge un model
     *
     * @param string|array $model
     * @param string|null|false $alias
	 * @return \dFrammework\core\Model|\dFrammework\core\Model[]|void
     * @throws \dFrammework\core\exception\LoadException
     */
    final protected function loadModel($model, $alias = null)
    {
        if (is_array($model))
        {
			$modelInstances = [];
            foreach ($model As $k => $v)
            {
                $mod = is_string($k) ? $k : $v;
				$alias = false !== $alias ? $v : $alias;

				$instance = $this->loadModel($mod, $alias);

				if (!empty($instance))
				{
					$modelInstances[] = $instance;
				}
            }

			if (!empty($modelInstances))
			{
				return $modelInstances;
			}
        }
        else
        {
            $instance = Load::model($model);

			if ($alias === false) {
				return $instance;
			}

			$mod = explode('/', $model);
            $mod = end($mod);

			$property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $mod);
			$this->{$property} = $instance;
        }
    }

    /**
     * Charge un autre controller
     *
     * @param string|array $controller
     * @param string|null|false $alias
	 * @return self|self[]|void
     * @throws \dFrammework\core\exception\LoadException
     */
    final protected function loadController($controller, $alias = null)
    {
        if (is_array($controller))
        {
			$controllerInstances = [];
            foreach ($controller As $k => $v)
            {
				$con = is_string($k) ? $k : $v;
				$alias = false !== $alias ? $v : $alias;

				$instance = $this->loadController($con, $alias);

				if (!empty($instance))
				{
					$controllerInstances[] = $instance;
				}
            }

			if (!empty($controllerInstances))
			{
				return $controllerInstances;
			}
        }
        else
        {
			$instance = Load::controller($controller);

			if ($alias == false) {
				return $instance;
			}

			$con = explode('/', $controller);
            $con = end($con);

            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $con);
			$this->{$property} = $instance;
     	}
    }

    /**
     * Charge une librarie
     *
     * @param string|array $library
     * @param string|null|false $alias
	 * @return mixed|mixed[]|void
     * @throws \dFrammework\core\exception\LoadException
     */
    final public function loadLibrary($library, $alias = null)
    {
		if (is_array($library))
        {
            $libraryInstances = [];
			foreach ($library As $k => $v)
            {
				$lib = is_string($k) ? $k : $v;
				$alias = false !== $alias ? $v : $alias;

				$instance = $this->loadLibrary($lib, $alias);

				if (!empty($instance))
				{
					$libraryInstances[] = $instance;
				}
            }

			if (!empty($libraryInstances))
			{
				return $libraryInstances;
			}
        }
        else
        {
            $instance = Load::library($library);

			if ($alias == false) {
				return $instance;
			}

			$lib = explode('/', $library);
            $lib = end($lib);

            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $lib);
			$this->{$property} = $instance;
        }
    }

    /**
     * Charge un helper
     *
     * @param string ...$helpers
     * @throws exception\LoadException
     */
    final public function loadHelper(string ...$helpers)
    {
        Load::helper($helpers);
    }

    /**
     * Sets the controller Model.
     *
     * @param string $model
     * @return Controller
     * @throws exception\LoadException
     */
    final protected function setModel(string $model) : self
    {
        $this->loadModel($model, 'model');
        return $this;
    }


    /**
	 * Recherche et charge tous les elements dont le controleur pourrait avoir besoin
	 *
     * @throws \ReflectionException
     * @throws \dFramework\core\exception\LoadException
     */
    private function _getElements()
    {
        $this->getModel();

        $this->autoloadModels();

        $this->autoloadLibraries();
    }

    /**
	 * Recherche le model par defaut (à base de son nom) du controleur
	 *
     * @throws \ReflectionException
     * @throws \dFramework\core\exception\LoadException
     */
    private function getModel()
    {
        $reflection = new ReflectionClass(get_called_class());
        $model = str_replace([CONTROLLER_DIR, 'Controller', '.php'], '', $reflection->getFileName()).'Model';

        if (file_exists(MODEL_DIR.$model.'.php'))
        {
            $this->setModel($model);
        }
    }

    /**
	 * Autocharge les models globaux (definis dans le fichier /app/config/autoload)
	 *
     * @throws \dFramework\core\exception\LoadException
     */
    private function autoloadModels()
    {
        $models = (array) config('autoload.models');
        foreach ($models As $key => $value)
        {
            if (is_string($key) AND is_string($value))
            {
                $this->loadModel($key, $value);
            }
            if (is_int($key) AND is_string($value))
            {
                $this->loadModel($value);
            }
        }
    }

    /**
	 * Autocharge les librairies globales (definis dans le fichier /app/config/autoload)
	 *
     * @throws \dFramework\core\exception\LoadException
     */
    private function autoloadLibraries()
    {
        $libraries = (array) config('autoload.libraries');

        foreach ($libraries As $key => $value)
        {
            if (is_string($key) AND is_string($value))
            {
                $this->loadLibrary($key, $value);
            }
            if (is_int($key) AND is_string($value))
            {
                $this->loadLibrary($value);
            }
        }
    }

	/**
	 * Execute les middleware post construction
	 */
    private function _launchMiddlewares()
    {
        $class = get_called_class();
        if (is_string($class))
        {
            $class = Injector::get($class);

            if (method_exists($class, 'middleware'))
		    {
			    $middleware = $class->middleware(Injector::make(Middleware::class, [$this->response]));

			    $this->response = $middleware->handle($this->request);
		    }
        }
    }
}
