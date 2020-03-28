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
use dFramework\core\exception\LoadException;
use dFramework\core\exception\RouterException;
use dFramework\core\loader\DIC;
use dFramework\core\data\Request;
use ReflectionMethod;
/**
 * Dispatcher
 *
 * Dispatch a url request by creating the appropriate MVC controller
 * instance and runs its method by passing it the parameters.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file        /system/core/route/Dispatcher.php
 * @credit      Web MVC Framework v.1.1.1 2016 - by Rosario Carvello <rosario.carvello@gmail.com>
 */

/**
 * A url request must be in the formats:
 *  - http://site/controller.
 *  - http://site/controller/method.
 *  - http://site/controller/method/param1.
 *  - http://site/controller/method/param1/param2/../paramn.
 *
 * A url request could also contain applications subsystems, e.g.:
 *  - http://site/subsystem/controller/method/param1/param2
 *  - http://site/subsystem/childsubsystem/controller/method/param1/param2
 *
 * A url request could also contain http get parameters, e.g.:
 *  - http://site/controller?get1=value2&get2=value2
 *  - http://site/controller/method?get1=value2&get2=value2
 *  - http://site/controller/method/param1/param2?get1=value2&get2=value2
 *  - http://site/subsystem/controller/method/param1/param2?get1=value2&get2=value2
 *
 * A url request must be in lower/upper case and can contains underscore. Framework will
 * apply all format conversion to run the appropriate MVC instance.
 *
 * Conversions are like this:
 *
 *  - http://site/user/open/1              => User->open(1);
 *  - http://site/user_manager/get_user/1  => UserManager->getUser(1)
 */
class Dispatcher
{
    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * @var null|string Store the current subsystem name
     */
    private $current_subsystem;
    /**
     * @var string The controller class name.
     */
    private $controller_class;

    /** @var  string Stores the controller class name using a SEO format
     *
     */
    private $controllerSEOClassName;

    /**
     * @var string The method name.
     */
    private $method;

    /**
     * @var array The methods parameters.
     */
    private $methodParameters = array();

    /**
     * @var string The url to parse for generate a request to dispatch.
     */
    private $urlToDispatch;


    /**
     * Creates the appropriate MVC controller instance. Depending on url parsing,
     * it runs controller method/with parameters and outputs the result.
     *
     */
    public static function init()
    {
        $instance = self::getInstance();

        $controllerClass = ucfirst($instance->controllerSEOClassName);
        $method = !empty($instance->method) ? $instance->method : 'index';

        $separatorBeforeController = !empty($instance->current_subsystem) ? DS : '';
        $controllerClassFile = str_replace('/', DS, CONTROLLER_DIR.$instance->current_subsystem);
        if($controllerClassFile[-1] == DS AND $separatorBeforeController == DS)
        {
            $controllerClassFile = substr($controllerClassFile, 0, - 1);
        }
        $controllerClassFile .= $separatorBeforeController. ucfirst($controllerClass) . 'Controller.php';
        $controllerClass .= 'Controller';

        self::loadController($controllerClassFile, $controllerClass, $method);

    }

    /**
     * Charge le controlleur et appelle la methode demandée
     *
     * @param $controllerClassFile
     * @param $controllerClass
     * @param $method
     * @param array|null $parameters
     * @throws \ReflectionException
     */
    public static function loadController($controllerClassFile, $controllerClass, $method, ?array $parameters = null)
    {
        if('cli' === php_sapi_name())
        {
            return;
        }
        $instance = self::getInstance();

        if(!empty($parameters) AND is_array($parameters))
        {
            $instance->methodParameters = $parameters;
        }

        $controllerClassFile .= (!preg_match('#\.php$#', $controllerClassFile)) ? '.php' : '';


        if(!file_exists($controllerClassFile))
        {
            LoadException::except('
                Can\'t load controller <b>'.preg_replace('#Controller$#', '',$controllerClass).'</b>. 
                <br> 
                The file &laquo; '.$controllerClassFile.' &raquo; do not exist
            ', 404);
        }
        /** @var TYPE_NAME $controllerClassFile */
        require_once $controllerClassFile;

        if (true !== class_exists($controllerClass))
        {
            RouterException::except('
                Impossible to load the controller <b>'.preg_replace('#Controller$#', '',$controllerClass).'</b>.
                <br> 
                The file &laquo; '.$controllerClassFile.' &raquo; do not contain class <b>'.$controllerClass.'</b>
            ', 404);
        }
        else
        {
            $controller = DIC::get($controllerClass);

            if (method_exists($controller, $method))
            {
                $reflection = new ReflectionMethod($controllerClass, $method);
                if($reflection->getName() == "__construct")
                {
                    RouterException::except("Access denied to __construct", 403);
                }
                if(preg_match('#^_#i', $reflection->getName()))
                {
                    RouterException::except("Access denied to ". $reflection->getName(), 403);
                }
                if ($reflection->isProtected() OR $reflection->isPrivate())
                {
                    RouterException::except("Access to " . $reflection->getName() . " method is denied in $controllerClass", 403);
                }

                $parameters = $reflection->getParameters(); $required_parameters = 0;
                foreach ($parameters AS $parameter)
                {
                    if(true !== $parameter->isOptional()) {
                        $required_parameters++;
                    }
                }
                if ($required_parameters > count($instance->methodParameters))
                {
                    RouterException::except('
                        Parameters error
                        <br>
                        The method <b>'.$method . '</b> of class '.$controllerClass.' require 
                        <b>'.$required_parameters.'</b> parameters, '.count($instance->methodParameters).' was send  
                    ', 400);
                }
                if (count($instance->methodParameters) > 0)
                {
                    call_user_func_array(array($controller, $method), $instance->methodParameters );
                }
                else
                {
                    call_user_func(array($controller, $method));
                }
            }
            else
            {
                RouterException::except('&laquo;<b>'.$method.'</b> method &raquo; is not defined in '.get_class($controller), 404);
            }
        }
    }


    public static function getClass()
    {
        return self::getInstance()->controller_class;
    }
    public static function getMethod()
    {
        return self::getInstance()->method;
    }
    public static function getController()
    {
        return trim(self::getInstance()->current_subsystem, '/');
    }


    /**
     * @return Dispatcher|null
     */
    private static function getInstance()
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new Dispatcher();
        }
        return self::$_instance;
    }

    /**
     * Dispatcher object constructor.
     */
    private function __construct()
    {
        $url = str_replace(Config::get('general.url_suffix'), '', ((new Request)->url ?? null));
        $current_subsystem = Lister::getCurrentSubSystem($url);

        $url = (is_string($url) AND isset($url[-1]) AND $url[-1] != '/') ? $url.'/' : $url;
        if(!empty($url) AND !empty($current_subsystem))
        {
            $url = preg_replace('#^'.$current_subsystem.'/?#', '', $url);
        }
        $this->current_subsystem =  str_replace("\\","/", $current_subsystem). "/";
        $this->urlToDispatch = $url;

        $this->parseUrlAndSetAttributes();
    }

    /**
     * Parses url by assuming controller/method/parameter_1/parameter_2/...etc.
     * positioning format and sets class attributes
     *
     */
    private function parseUrlAndSetAttributes()
    {
        if($this->current_subsystem == "/") {
            $this->current_subsystem = "";
        }
        if(is_string($this->urlToDispatch) AND (isset($this->urlToDispatch[-1]) AND  $this->urlToDispatch[-1] == '/'))
        {
            $this->urlToDispatch = substr($this->urlToDispatch, 0, -1);
        }
        $urlSegments = explode("/", $this->urlToDispatch);

        // First segment is the controller  - store its name ad SEO name if controller is passed
        if ($urlSegments[0] != "")
        {
            $this->controller_class = $this->current_subsystem . $this->underscoreToCamelCase($urlSegments[0],true);
            $this->controllerSEOClassName = strtolower($urlSegments[0]);
        }

        // if  root subsystem
        else if ($this->current_subsystem == "")
        {
            $default_controller = Config::get('route.default_controller');
            $this->controller_class = $this->underscoreToCamelCase($default_controller, true);
            $this->controllerSEOClassName = strtolower($default_controller);
        }

        // if root child subsystem
        else
        {
            $default_controller = Config::get('route.default_controller');
            $this->controller_class = $this->underscoreToCamelCase($default_controller, true);
            $this->controllerSEOClassName = strtolower($default_controller);
        }

        // Second segment is a controller Method
        if (isset($urlSegments[1]))
        {
            $this->method = $this->underscoreToCamelCase($urlSegments[1]);

            // If a method is present, then all the other right segments are
            // considered as method's parameters
            if (count($urlSegments) > 2)
            {
                $temp = array_slice($urlSegments, 2, count($urlSegments) - 1);
                $i = 0;
                foreach ($temp as $key => $value) {
                    $this->methodParameters[$i] = $value;
                    $i++;
                }
            }
        }
    }

    /**
     * Converts url notation, underscored and lower case, to Camel/Pascal case notation.
     *
     * @param string $string The url string to convert
     * @param bool $pascalCase . If true uses Pascal Case. Default false, uses Camel Case
     * @return string
     */
    private function underscoreToCamelCase(string $string, bool $pascalCase = false) : string
    {
        if( $pascalCase == true )
        {
            $string[0] = strtoupper($string[0]);
        }
        $str=$string;
        $i = array("-","_");
        $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
        $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
        $str = str_replace($i, ' ', $str);
        $str = str_replace(' ', '', ucwords(strtolower($str)));
        $str = strtolower(substr($str,0,1)).substr($str,1);
        return $str;
    }

}
