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
 *  @version    3.2.3
 */

namespace dFramework\core;

use ReflectionClass;
use dFramework\core\http\Middleware;
use dFramework\core\loader\Load;
use dFramework\core\output\View;
use dFramework\core\output\Cache;
use dFramework\core\router\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Controller
 *
 * A global controller of system
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       1.0
 * @file		/system/core/Controller.php
 */
class Controller
{
    CONST 
        /**
         * Utilisation de l'objet Cache
         */
        CACHE_OBJECT = 1;

    /**
     * @var \dFramework\core\http\ServerRequest Instance de l'objet Request
     */
    protected $request;
    /**
     * @var \dFramework\core\http\Response Instance de l'objet Response
     */
    protected $response;
    /**
     * @var \dFramework\core\output\Cache Instance de l'objet Cache
     */
    protected $cache;

    private $_middlewares = [];
    

    /**
     * Controller
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

    /**
     * Recuperation d'une seule instance de controleur. Pattern singletton
     *
     * @return Controller
     */
    public static function instance() : self
    {
        if (null === self::$_instance) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private static $_instance;

   
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
    protected function runMiddleware($middlewares)
    {
        $middleware = new Middleware($this->response);

        $middleware->add($this->_middlewares)->add($middlewares);

        $this->response = $middleware->handle($this->request);
    }
    
    /**
     * @param int ...$object
     */
    final protected function useObject(int... $object)
    {
        foreach ($object As $value)
        {
            if (self::CACHE_OBJECT === $value)
            {
                $this->cache = new Cache();
            }
        }
    }
    

    /**
     * Charge une vue
     * 
     * @param string $view
     * @param array|null $data
     * @param array|null $options
     * @return View
     * @throws \ReflectionException
     */
    final protected function view(string $view, ?array $data = [], ?array $options = []) : View
    {
        $reflection = new ReflectionClass(get_called_class());
        $path = str_replace([CONTROLLER_DIR, 'Controller', '.php'], '', $reflection->getFileName());

        $view = new View($view, $data, $path, $options, $this->response);
        if (!empty($this->layout) AND is_string($this->layout)) 
        {
            $view->layout($this->layout);
        }

        if (!empty($this->view_datas) AND is_array($this->view_datas))
        {
            $view->addData($this->view_datas);
        }
        
        return $view;
    }
    /**
     * Charge et rend directement une vue
     *
     * @param string|null $view
     * @param array|null $data
     * @param array|null $options
     * @return ResponseInterface
     */
    final protected function render(?string $view = null, ?array $data = [], ?array $options = []) : ResponseInterface
    {
        if (empty($view)) 
        {
            $view = Dispatcher::getMethod();
        }
        $view = $this->view($view, $data, $options)->get(Config::get('general.compress_output'));

        return $this->response->withBody(stream_for($view));
    }

    /**
     * Defini des donnees à distribuer à toutes les vues
     *
     * @param string|array $key
     * @param [type] $value
     * @return self
     */
    final protected function addData($key, $value = null) : self
    {
        if (is_string($key) OR is_array($key))
        {
            $data = $key;
            if (!is_array($key))
            {
                $data = [$key => $value];
            }
            $this->view_datas = array_merge($this->view_datas, $data);
        }
        return $this;
    }


    /**
     * Charge un model
     * 
     * @param string|array $model
     * @param string|null $alias
     */
    final protected function loadModel($model, string $alias = null)
    {
        if (is_array($model))
        {
            foreach ($model As $k => $v) 
            {
                if (is_string($k)) 
                {
                    $mod = $k;
                    $alias = $v;
                }
                else 
                {
                    $mod = $v;
                    $alias = $v;
                }
                $this->loadModel($mod, $alias);
            }
        }
        else 
        {
            $mod = explode('/', $model);
            $mod = end($mod);
            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $mod);
     
            $this->{$property} = Load::model($model);
        }
    }

    /**
     * Charge un autre controller 
     * 
     * @param string|array $controller
     * @param string|null $alias
     */
    final protected function loadController($controller, string $alias = null)
    {
        if (is_array($controller))
        {
            foreach ($controller As $k => $v) 
            {
                if (is_string($k)) 
                {
                    $con = $k;
                    $alias = $v;
                }
                else 
                {
                    $con = $v;
                    $alias = $v;
                }
                $this->loadController($con, $alias);
            }
        }
        else 
        {
            $con = explode('/', $controller);
            $con = end($con);
            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $con);
     
            $this->{$property} = Load::controller($controller);
        }
    }

    /**
     * Charge une librarie
     * 
     * @param string|array $library
     * @param string|null $alias
     * @param mixed $var 
     */
    final public function loadLibrary($library, string $alias = null, &$var = null)
    {
        if (is_array($library))
        {
            foreach ($library As $k => $v) 
            {
                if (is_string($k)) 
                {
                    $lib = $k;
                    $alias = $v;
                }
                else 
                {
                    $lib = $v;
                    $alias = $v;
                }
                $this->loadLibrary($lib, $alias);
            }
        }
        else 
        {
            $lib = explode('/', $library);
            $lib = end($lib);
            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $lib);
     
            $this->{$property} = Load::library($library);
                
            /**
             * @since 2.2
             */
            if (array_key_exists(2, func_get_args()))
            {
                $var = $this->$property;
                unset($this->$property);
            }
        }
    }

    /**
     * Charge un helper
     * 
     * @param string ...$helpers
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
     * @throws \ReflectionException
     */
    final protected function setModel(string $model) : self
    {
        $this->loadModel($model, 'model');
        return $this;
    }


    /**
     * @throws \ReflectionException
     */
    private function _getElements()
    {
        $this->getModel();

        $this->autoloadModels();

        $this->autoloadLibraries();
    }
    /**
     * @throws \ReflectionException
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
     * @throws \ReflectionException
     */
    private function autoloadModels()
    {
        $models = (array) Config::get('autoload.models');
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
     * @throws \ReflectionException
     */
    private function autoloadLibraries()
    {
        $libraries = (array) Config::get('autoload.libraries');

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

   
    private function _launchMiddlewares()
    {
        $class = get_called_class();
        if (is_string($class))
        {
            $class = new $class;
         
            if (method_exists($class, 'middleware')) 
		    {
			    $middleware = $class->middleware(New Middleware($this->response));

			    $this->response = $middleware->handle($this->request);
		    }
        }
    }
}
