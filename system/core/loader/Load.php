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
 *  @version    3.1
 */


namespace dFramework\core\loader;

use dFramework\core\Config;
use dFramework\core\Controller;
use dFramework\core\exception\LoadException;

/**
 * Load
 *
 *  Load the files that the application needs
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file        /system/core/loader/Load.php
 */

class Load
{
    /**
     * @var array
     */
    private static $loads = [
        'controllers' => [],
        'helpers' => [],
        'langs' => [],
        'libraries' => [],
        'models' => []
    ];
    

    /**
     * @throws LoadException
     * @throws \ReflectionException
     * @throws \dFramework\core\exception\Exception
     */
    public static function init()
    {
        $autoload = (array) Config::get('autoload');

        if(!empty($autoload) AND is_array($autoload))
        {
            $modules = ['helpers', 'libraries', 'models'];
            foreach ($autoload As $module => $loads)
            {
                if (!in_array($module, $modules))
                {
                    LoadException::except('
                        The <b>' . $module . '</b> module is unchargeable element. (Accept values: ' . join('/', $modules) . ')
                        <br>
                        Please edit &laquo; ' . Config::$_config_file['autoload'] . ' &raquo; file to correct it
                    ');
                }
            }
        }

        $autoload = array_merge([
            'system', 'url', 'assets', 'scl'
        ], $autoload['helpers'] ?? []);

        foreach ($autoload As $load)
        {
            self::_helper($load, preg_match('#^my_#i', $load));
        }
    }


    /**
     * @param string|array $helperss
     * @throws LoadException
     */
    public static function helper($helpers)
    {
        $helpers = (array) $helpers;
        foreach ($helpers As $helper)
        {
            self::_helper($helper, preg_match('#^my_#', $helper));
        }
    }
    /**
     * @param string $func
     * @param bool $app
     * @throws LoadException
     */
    private static function  _helper(string $func, bool $app = false)
    {
        $func = trim($func);
        if (!self::is_loaded('helpers', $func))
        {
            $file = ($app === false)
                ? SYST_DIR.'helpers'.DS.$func.'.php'
                : APP_DIR.'helpers'.DS.$func.'.php';

            if (!file_exists($file))
            {
                LoadException::except('
                    Impossible de charger les fonctions <b>'.$func.'</b>. 
                    <br> 
                    Le fichier &laquo; '.$file.' &raquo; n\'existe pas
                ');
            }
            self::loaded('helpers', $func);

            require_once $file;
        }
    }


    /**
     * Charge un model a un controleur donné
     * 
     * @param Controller $object
     * @param string|array $model
     * @param string $alias
     * @throws LoadException
     * @throws \ReflectionException
     */
    public static function model(Controller &$object, $model, string $alias = null)
    {
        if (!empty($model) AND is_array($model))
        {
            foreach ($model As $key => $value)
            {
                if (!empty($key) AND is_string($key))
                {
                    if (!empty($value) AND is_string($value))
                    {
                        $property = strtolower($value);
                        $object->{$property} = self::_model($key);
                    }
                    else
                    {
                        $property = explode('/', $key); 
                        $property = strtolower(end($property));
                        $object->{$property} = self::_model($key);
                    }
                }
                else if(!empty($value) AND is_string($value))
                {
                    $property = strtolower($value);
                    $object->{$property} = self::_model($value);
                }
            }
        }
        if (!empty($model) AND is_string($model))
        {
            if (!empty($alias) AND is_string($alias)) 
            {
                $property = strtolower($alias);
                $object->{$property} = self::_model($model);
            }
            else
            {
                $property = explode('/', $model); 
                $property = strtolower(end($property));
                $object->{$property} = self::_model($model);
            }
        }
    }
    /**
     * @param $model
     * @return null
     * @throws LoadException
     * @throws \ReflectionException
     */
    private static function _model($model)
    {
        $model = str_replace('.'.pathinfo($model, PATHINFO_EXTENSION), '', $model);
        $model = (!preg_match('#Model$#', $model)) ? $model.'Model' : $model;

        $part_model = pathinfo($model);
        $model = ucfirst($part_model['filename']);
        $model_path = MODEL_DIR.trim(str_replace('/', DS, $part_model['dirname']), DS).DS.$model.'.php';

        if (!self::is_loaded('models', $model))
        {
            if (!file_exists($model_path))
            {
                LoadException::except('
                    Impossible de charger le model <b>'.str_replace('Model', '', $model).'</b>. 
                    <br> 
                    Le fichier &laquo; '.$model_path.' &raquo; n\'existe pas
                ');
            }
            require_once $model_path;

            if (!class_exists($model))
            {
                LoadException::except('
                    Impossible de charger le model <b>'.str_replace('Model', '', $model).'</b>. 
                    <br> 
                    Le fichier &laquo; '.$model_path.' &raquo; ne contient pas de classe <b>'.$model.'</b>
                ');
            }
            self::loaded('models', $model, DIC::get($model));
        }
        return self::get_loaded('models', $model);
    }

    /**
     * Charge un autre controller dans un controleur donné
     * 
     * @param Controller $object
     * @param string|array $controller
     * @param string $alias
     * @since 3.0.2
     * @throws LoadException
     * @throws \ReflectionException
     */
    public static function controller(Controller &$object, $controller, string $alias)
    {
        if (!empty($controller) AND is_array($controller))
        {
            foreach ($controller As $key => $value)
            {
                if (!empty($key) AND is_string($key))
                {
                    if (!empty($value) AND is_string($value))
                    {
                        $property = strtolower($value);
                        $object->{$property} = self::_controller($key);
                    }
                    else
                    {
                        $property = explode('/', $key); 
                        $property = strtolower(end($property));
                        $object->{$property} = self::_controller($key);
                    }
                }
                else if (!empty($value) AND is_string($value))
                {
                    $property = strtolower($value);
                    $object->{$property} = self::_controller($value);
                }
            }
        }
        if (!empty($controller) AND is_string($controller))
        {
            if (!empty($alias) AND is_string($alias)) 
            {
                $property = strtolower($alias);
                $object->{$property} = self::_controller($controller);
            }
            else
            {
                $property = explode('/', $controller); 
                $property = strtolower(end($property));
                $object->{$property} = self::_controller($controller);
            }
        } 
    }
    /**
     * @param $controller
     * @return null
     * @throws LoadException
     * @throws \ReflectionException
     */
    private static function _controller($controller)
    {
        $controller = str_replace('.'.pathinfo($controller, PATHINFO_EXTENSION), '', $controller);
        $controller = (!preg_match('#Controller$#', $controller)) ? $controller.'Controller' : $controller;

        $part_controller = pathinfo($controller);
        $controller = ucfirst($part_controller['filename']);
        $controller_path = CONTROLLER_DIR.trim(str_replace('/', DS, $part_controller['dirname']), DS).DS.$controller.'.php';

        if (! self::is_loaded('controllers', $controller))
        {
            if (! file_exists($controller_path))
            {
                LoadException::except('
                    Impossible de charger le controlleur <b>'.str_replace('Controller', '', $controller).'</b>. 
                    <br> 
                    Le fichier &laquo; '.$controller_path.' &raquo; n\'existe pas
                ');
            }
            require_once $controller_path;

            if (! class_exists($controller))
            {
                LoadException::except('
                    Impossible de charger le controlleur <b>'.str_replace('Controller', '', $controller).'</b>. 
                    <br> 
                    Le fichier &laquo; '.$controller_path.' &raquo; ne contient pas de classe <b>'.$controller.'</b>
                ');
            }
            self::loaded('controllers', $controller, DIC::get($controller));
        }
        return self::get_loaded('controllers', $controller);
    }

    /**
     * Charge une librairie dans un controlleur donné
     * 
     * @param Controller $object
     * @param string|array $library
     * @param null|string $alias
     * @throws \ReflectionException
     */
    public static function library(Controller &$object, $library, string $alias = null) : void
    {
        if (!empty($library) AND is_array($library))
        {
            foreach ($library As $key => $value)
            {
                if (!empty($key) AND is_string($key))
                {
                    $lib = explode('/', $key); $lib = end($lib);
                    $property = strtolower(!empty($value) AND is_string($value) ? $value : $key);
                    $object->{$property} = self::_library($key, preg_match('#^my_#i', $lib));
                }
                else if (!empty($value) AND is_string($value))
                {
                    $lib = explode('/', $value); 
                    $lib = end($lib);
                    $property = strtolower($value);
                    $object->{$property} = self::_library($value, preg_match('#^my_#i', $lib));
                }
            }
        }
        if(!empty($library) AND is_string($library))
        {
            $lib = explode('/', $library); 
            $lib = end($lib);
            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $library);
            $object->{$property} = self::_library($library, preg_match('#^my_#i', $lib));
        }
    }
    /**
     * @param $library
     * @param bool $app
     * @return mixed
     * @throws \ReflectionException
     */
    private static function _library($library, bool $app = false)
    {
        $library = ucfirst($library);

        $file = ($app === false)
            ? SYST_DIR.'libraries'.DS.str_replace('/', DS, $library).'.php'
            : APP_DIR.'libraries'.DS.str_replace('/', DS, $library).'.php'
        ;
        $library = explode('/', trim($library)); 
        $library = end($library);

        if(!self::is_loaded('libraries', $library))
        {
            if (!file_exists($file))
            {
                LoadException::except('
                    Impossible de charger la librairie <b>'.$library.'</b>. 
                    <br> 
                    Le fichier &laquo; '.$file.' &raquo; n\'existe pas
                ');
            }
            require_once $file;

            $library = ($app === false) ? "dF_$library" : $library;
            if (!class_exists($library))
            {
                LoadException::except('
                    Impossible de charger la librarie <b>'.$library.'</b>. 
                    <br> 
                    Le fichier &laquo; '.$file.' &raquo; ne contient pas de classe <b>'.$library.'</b>
                ');
            }
            self::loaded('libraries', $library, DIC::get($library));
        }
        return self::get_loaded('libraries', $library);
    }

    /**
     * Charge un fichier de gestion de langue
     * 
     * @param string $file
     * @param mixed $var 
     * @param string|null $locale
     * @param bool $app
     * @since 3.0
     */
    public static function lang(string $file, &$var, ?string $locale = null, bool $app = false)
    {
        if (empty($locale))
        {
            $locale = Config::get('general.language');
        }
        $file = preg_replace('#\.json$#i', '', $file);
        $filename = (true === $app) 
            ? RESOURCE_DIR . 'reserved'.DS.'lang' . DS . $locale . DS . $file . '.json'
            : SYST_DIR . 'constants' . DS . 'lang' . DS . $locale . DS . $file . '.json';

        if (true !== file_exists($filename))
        {
            LoadException::except('
                Impossible de charger le fichier de langue <b>'.$file.'</b>. 
                <br> 
                Le fichier &laquo; '.$filename.' &raquo; n\'existe pas.
            ');
        }
        if (false === ($lang = file_get_contents($filename)))
        {
            LoadException::except('
                Impossible de charger le fichier de langue <b>'.$file.'</b>. 
                <br> 
                Le fichier &laquo; '.$filename.' &raquo; est innaccessible en lecture.
            ');
        }
        $var = json_decode($lang);
    }

    
    
    /**
     * Verifie si un element est chargé dans la liste des modules
     * 
     * @param string $module
     * @param $element
     * @return bool
     */
    private static function is_loaded(string $module, $element) : bool
    {
        if (!isset(self::$loads[$module]) OR !is_array(self::$loads[$module]))
        {
            return false;
        }
        return (in_array($element, self::$loads[$module]));
    }
    /**
     * Ajoute un element aux elements chargés
     * 
     * @param string $module
     * @param string $element
     * @param mixed|null $value
     * @return void
     */
    private static function loaded(string $module, $element, $value = null) : void 
    {
        self::$loads[$module][$element] = $value;
    }    
    /**
     * Renvoie un element chargé
     *
     * @param  string $module
     * @param  string $element
     * @return mixed
     */
    private static function get_loaded(string $module, $element)
    {
        return self::$loads[$module][$element] ?? null;
    }
}