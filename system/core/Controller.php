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


 
namespace dFramework\core;

use dFramework\core\data\Data;
use dFramework\core\data\Request;
use dFramework\core\data\Response;
use dFramework\core\loader\Load;
use dFramework\core\output\Cache;
use dFramework\core\output\Layout;
use dFramework\core\output\View;
use ReflectionClass;

/**
 * Controller
 *
 * A global controller of system
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       1.0
 * @file		/system/core/Controller.php
 */

abstract class Controller
{
    const REQUEST_OBJECT = 1;

    const RESPONSE_OBJECT = 2;

    const CACHE_OBJECT = 3;

    /**
     * @var Layout
     */
    protected $layout;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var Cache
     */
    protected $cache;


    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->getElements();

        $this->layout = $this->layout('default');

        $this->data = new Data();

        /**
         * Use Request and Response Object automaticaly
         * @since 2.2
         */
        $this->useObject(self::REQUEST_OBJECT, self::RESPONSE_OBJECT);
    }

    /**
     * @param int ...$object
     */
    final protected function useObject(int... $object)
    {
        foreach ($object As $value)
        {
            if(self::RESPONSE_OBJECT === $value)
            {
                $this->response = new Response();
            }
            if(self::REQUEST_OBJECT === $value)
            {
                $this->request = new Request();
            }
            if(self::CACHE_OBJECT === $value)
            {
                $this->cache = new Cache();
            }
        }
    }

    /**
     * @return mixed
     */
    abstract protected function index();

    /**
     * @param string $view
     * @param array $vars
     * @return View
     * @throws \ReflectionException
     */
    final protected function view(string $view, array $vars = []) : View
    {
        $reflection = new ReflectionClass(get_called_class());
        $path = str_replace([CONTROLLER_DIR, 'Controller', '.php'], '', $reflection->getFileName());

        return new View($view, $vars, $path);
    }

    /**
     * @param string $layout
     * @param array|null $data
     * @return Layout
     */
    final protected function layout(string $layout, ?array $data = []): Layout
    {
        return new Layout($layout, $data);
    }


    /**
     * @param string $model
     * @param string|null $alias
     * @throws \ReflectionException
     */
    final protected function loadModel(string $model, string $alias = null)
    {
        Load::model($this, $model, $alias);
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
        if(is_string($library) AND array_key_exists(2, func_get_args()))
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

        if(file_exists(MODEL_DIR.$model.'.php'))
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
            if(is_string($key) AND is_string($value))
            {
                $this->loadModel($key, $value);
            }
            if(is_int($key) AND is_string($value))
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
            if(is_string($key) AND is_string($value))
            {
                $this->loadLibrary($key, $value);
            }
            if(is_int($key) AND is_string($value))
            {
                $this->loadLibrary($value);
            }
        }

        $this->loadLibrary('Debug');
    }


}