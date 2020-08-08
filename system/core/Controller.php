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

namespace dFramework\core;

use dFramework\core\http\Filter;
use dFramework\core\loader\Load;
use dFramework\core\loader\Service;
use dFramework\core\output\Cache;
use dFramework\core\output\View;
use ReflectionClass;

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
         * Utilisation de l'objet Request
         */
        REQUEST_OBJECT = 1,
        /**
         * Utilisation des objet Response
         */
        RESPONSE_OBJECT = 2, 
        /**
         * Utilisation de l'objet Cache
         */
        CACHE_OBJECT = 3;

    protected 
        /**
         * @var Request Instance de l'objet Request
         */
        $request,
        /**
         * @var Response Instance de l'objet Response
         */
        $response,
        /**
         * @var Cache Instance de l'objet Cache
         */
        $cache;

    private $_filters = [];
    

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        /**
         * Use Request and Response Object automaticaly
         * @since 2.2
         */
        $this->useObject(self::REQUEST_OBJECT, self::RESPONSE_OBJECT);

        $this->_launchFilters();
        
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
     * Renvoi la liste des filtres a appliquer a la requete
     *
     * @return array
     */
    protected function _filters() : array 
    {
        return [];
    }
    /**
     * Defini une liste de filtres specifique a la methode
     *
     * @param string|string[] $filters
     * @return void
     */
    final protected function useFilter($filters)
    {
        $this->_filters = (array) $filters;
    }

    
    /**
     * @param int ...$object
     */
    final protected function useObject(int... $object)
    {
        foreach ($object As $value)
        {
            if (self::RESPONSE_OBJECT === $value)
            {
                $this->response = Service::response();
            }
            if (self::REQUEST_OBJECT === $value)
            {
                $this->request = Service::request();
            }
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

        $view = new View($view, $data, $path, $options);
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

    private function _launchFilters()
    {
        $filter = new Filter();

        $filter->add(array_merge($this->_filters(), $this->_filters));

        $this->response = $filter->handle($this->request);
    }
}
