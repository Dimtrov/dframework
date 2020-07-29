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
 *  @version    3.2.1
 */

namespace dFramework\core\loader;

use dFramework\core\Config;
use dFramework\core\exception\LoadException;
use InvalidArgumentException;

/**
 * Load
 *
 *  Load the files that the application needs
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
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

        if (!empty($autoload) AND is_array($autoload))
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

        // Autoload Helpers
        self::helper(array_merge([
            'global',
        ], $autoload['helpers'] ?? []));
    }


    /**
     * Charge un fichier d'aide
     * 
     * @param string|array $helpers
     * @return void
     * @throws LoadException
     * @throws InvalidArgumentException
     */
    public static function helper($helpers)
    {
        if (empty($helpers))
        {
            throw new LoadException('Veuillez specifier le helper à charger');
        }
        if (!is_string($helpers) AND !is_array($helpers))
        {
            throw new InvalidArgumentException('Type de parametre incorrect pour la methode "helper"');
        }
        
        $helpers = (array) $helpers;
        foreach ($helpers As $helper)
        {
           FileLocator::helper($helper);
        }
    }

    /**
     * Charge un modele a un controleur donné
     * 
     * @param string|array $model
     * @return object|object[]
     * @throws LoadException
     * @throws InvalidArgumentException
     */
    public static function model($model)
    {
        if (empty($model))
        {
            throw new LoadException('Veuillez specifier le modele à charger');
        }
        if (!is_string($model) AND !is_array($model))
        {
            throw new InvalidArgumentException('Type de parametre incorrect pour la methode "model"');
        }

        if (is_array($model))
        {
            $models = [];

            foreach ($model As $value)
            {
                $models[] = self::model($value);
            }

            return $models;
        }

        if (!self::is_loaded('models', $model))
        {
            self::loaded('models', $model, FileLocator::model($model));
        }

        return self::get_loaded('models', $model);
    }

    /**
     * Charge un autre controleur dans un controleur donné
     * 
     * @param string|array $controller
     * @return object|object[]
     * @since 3.0.2
     * @throws LoadException
     * @throws InvalidArgumentException
     */
    public static function controller($controller)
    {
        if (empty($controller))
        {
            throw new LoadException('Veuillez specifier le controleur à charger');
        }
        if (!is_string($controller) AND !is_array($controller))
        {
            throw new InvalidArgumentException('Type de parametre incorrect pour la methode "controller"');
        }

        if (is_array($controller))
        {
            $controllers = [];

            foreach ($controller As $value)
            {
                $controllers[] = self::controller($value);
            }

            return $controllers;
        }

        if (!self::is_loaded('controllers', $controller))
        {
            self::loaded('controllers', $controller, FileLocator::controller($controller));
        }

        return self::get_loaded('controllers', $controller);
    }

    /**
     * Charge une librairie dans un controlleur donné
     * 
     * @param string|array $library
     * @return object|object[]
     * @throws LoadException
     * @throws InvalidArgumentException
     */
    public static function library($library)
    {
        if (empty($library))
        {
            throw new LoadException('Veuillez specifier la librarie à charger');
        }
        if (!is_string($library) AND !is_array($library))
        {
            throw new InvalidArgumentException('Type de parametre incorrect pour la methode "library"');
        }

        if (is_array($library))
        {
            $librairies = [];

            foreach ($library As $value)
            {
                $librairies[] = self::library($value);
            }

            return $librairies;
        }

        if (!self::is_loaded('libraries', $library))
        {
            self::loaded('librairies', $library, FileLocator::library($library));
        }

        return self::get_loaded('librairies', $library);
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
     * Recupere toutes les definitions des services a injecter dans le container
     *
     * @return array
     */
    public static function providers() : array
    {
        $providers = [];

        // System services
        $filename = SYST_DIR . 'constants' . DS . 'provider.php';
        if (!file_exists($filename))
        {
            LoadException::except('
                Impossible de charger le fichier de definition des services du systeme. 
                <br> 
                Le fichier &laquo; '.$filename.' &raquo; n\'existe pas ou est innaccessible en lecture.
            ');

        }
        else 
        {
            $providers = array_merge($providers, require_once $filename);
        }
        
        // App services
        $filename = APP_DIR . 'config' . DS . 'provider.php';
        if (file_exists($filename))
        {
            $providers = array_merge($providers, require_once $filename);
        }
        
        return $providers;
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
