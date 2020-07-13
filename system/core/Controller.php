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
 *  @version    3.2
 */


namespace dFramework\core;

use dFramework\core\data\Data;
use dFramework\core\http\Request;
use dFramework\core\http\Response;
use dFramework\core\loader\Load;
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

abstract class Controller
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
         * Utilisation de l'objet Data
         */
        DATA_OBJECT = 3,
        /**
         * Utilisation de l'objet Cache
         */
        CACHE_OBJECT = 4;

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
         * @var Data Instance de l'objet Data
         */
        $data,
        /**
         * @var Cache Instance de l'objet Cache
         */
        $cache;


    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->getElements();

        /**
         * Use Request and Response Object automaticaly
         * @since 2.2
         */
        $this->useObject(self::REQUEST_OBJECT, self::RESPONSE_OBJECT);
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
     * @param int ...$object
     */
    final protected function useObject(int... $object)
    {
        foreach ($object As $value)
        {
            if (self::RESPONSE_OBJECT === $value)
            {
                $this->response = new Response();
            }
            if (self::REQUEST_OBJECT === $value)
            {
                $this->request = new Request();
            }
            if (self::DATA_OBJECT === $value) 
            {        
                $this->data = new Data();
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
        if (!empty($this->layout) AND is_string($this->layout)) {
            $view->layout($this->layout);
        }
        
        return $view;
    }

    /**
     * Charge un model
     * 
     * @param string|array $model
     * @param string|null $alias
     * @throws \ReflectionException
     */
    final protected function loadModel($model, string $alias = null)
    {
        Load::model($this, $model, $alias);
    }

    /**
     * Charge un autre controller 
     * 
     * @param string|array $controller
     * @param string|null $alias
     */
    final protected function loadController($controller, string $alias = null)
    {
        Load::controller($this, $controller, $alias);
    }

    /**
     * Charge une librarie
     * 
     * @param string|array $library
     * @param string|null $alias
     * @param mixed $var 
     * @throws \ReflectionException
     */
    final protected function loadLibrary($library, string $alias = null, &$var = null)
    {
        Load::library($this, $library, $alias);
        
        /**
         * @since 2.2
         */
        if (is_string($library) AND array_key_exists(2, func_get_args()))
        {
            $prop = strtolower(!empty($alias) ? $alias : $library);
            $var = $this->$prop;
        }
    }

    /**
     * Charge un helper
     * 
     * @param string ...$helpers
     */
    final protected function loadHelper(string ...$helpers)
    {
        Load::helper($helpers);
    }

    /**
     * Charge un fichier de langue
     * 
     * @param string $file
     * @param mixed $var 
     * @param string|null $locale
     * @since 3.0
     */
    final protected function loadLang(string $file, &$var, ?string $locale = null)
    {
        Load::lang($file, $var, $locale, true);
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
    private function getElements()
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
}
