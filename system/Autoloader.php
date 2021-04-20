<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework;

/**
 * Autoloader
 *
 * Autoload a dFramework system class
 *
 * @package		dFramework
 * @subpackage	null
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 */
class Autoloader
{
    /**
     * Array contain every map classes of system
     */
    private static $_class_map_syst = [];

    /**
     * Array contain every map classes of application
     */
    private static $_class_map_app = [];

    /**
     * @var array Array contain every psr4 definition
     */
    private static $_class_map_psr = [];

    /**
     * @var array Array contain every class aliases definitions
     */
    private static $_aliases = [];



    public static function load()
    {
        self::loadConfigFiles();
        
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * @param string $input
     */
    public static function autoload(string $input)
    {
        $functions = [
            'syst', 'app', 
            'psr4', 'syst_classmap', 'app_classmap',
            'controllers', 'models', 'entities', 'middlewares'
        ];

        foreach ($functions As $func)
        {
            if (self::{'autoload_'.$func}($input)) 
            {
                return;
            }
        }
        self::retrieveAlias($input);
    }


    /**
     * Charge les differents fichiers utiles a l'autoloader
     *
     * @return void
     */
    private static function loadConfigFiles()
    {
        $class_map_file = APP_DIR.'config'.DS.'psr4.php';
        /**
        * Chargement des fichiers de namespace personnalisés
        */
        if (file_exists($class_map_file) AND true !== in_array($class_map_file, \get_included_files()))
        {
            self::$_class_map_psr = require $class_map_file;
        }

        $class_map_file = SYST_DIR.'constants'.DS.'.classmap.php';
        /**
         * Chargement des dependences systemes mapées
         */
        if (file_exists($class_map_file) AND true !== in_array($class_map_file, \get_included_files()))
        {
            self::$_class_map_syst = require $class_map_file;
        }
        
        $class_map_file = APP_DIR.'resources'.DS.'reserved'.DS.'.classmap.php';
        /**
         * Chargement des classes applicatives mapées
         */
        if (file_exists($class_map_file) AND true !== in_array($class_map_file, \get_included_files()))
        {
            self::$_class_map_app = require $class_map_file;
        }

    }

    /**
     * Verifie si on a faire a une alias et retourne la classe correspondante
     *
     * @param string $alias
     * @return void
     */
    private static function retrieveAlias(string $alias)
    {
        $class_map_file = APP_DIR.'config'.DS.'aliases.php';
        /**
         * Chargement des alias
         */
        if (file_exists($class_map_file) AND true !== in_array($class_map_file, \get_included_files()))
        {
            self::$_aliases = require $class_map_file;
        }
        $original = self::$_aliases[$alias] ?? null;

        if (!empty($original) AND is_string($original) AND !class_exists($alias))
        {
            class_alias($original, $alias);
        }
    }


    /**
     * Chargement des fichiers systeme
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_syst(string $input) : bool
    {
        if (strpos($input, __NAMESPACE__ . '\\') !== false)
        {
            $input = str_replace(__NAMESPACE__ . '\\', '', $input);
            $input = explode('\\', $input);

            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = __DIR__. DS . $namespace . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
    /**
     * Chargement des fichiers du namespace App
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_app(string $input) : bool
    {
        if (strpos($input, 'App\\') !== false)
        {
            $input = str_replace('App\\', '', $input);
            $input = explode('\\', $input);

            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(APP_DIR . 'class' . DS . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
        
    /**
     * Chargement des fichiers de namespace personnalisés
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_psr4($input) : bool 
    {
        foreach (self::$_class_map_psr As $key => $value) 
        {
            $key = rtrim($key, '\\').'\\';
            $value = rtrim($value, DS);

            if (strpos($input, $key) !== false)
            {
                $input = str_replace($key, '', $input);
                $input = explode('\\', $input);

                $class = array_pop($input);
                $namespace = implode(DS, $input);

                $file = rtrim($value . DS . $namespace, DS) . DS. $class . '.php';
                if (is_file($file))
                {
                    require_once $file;
                    return true;
                }

                break;
            }
        } 

        return false;
    }
    /**
     * Chargement des dependences systemes mapées
     *  
     * @param string $input
     * @return bool
     */
    private static function autoload_syst_classmap(string $input) : bool 
    {
        if (array_key_exists($input, self::$_class_map_syst))
        {
            $file = str_replace(['{SYST_DIR}', '\\'], [SYST_DIR, DS], self::$_class_map_syst[$input]);
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
    /**
     * Chargement des classes applicatives mapées
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_app_classmap(string $input) : bool 
    {
        if (array_key_exists($input, self::$_class_map_app))
        {
            $file = str_replace(['{APP_DIR}', '\\'], [APP_DIR, DS], self::$_class_map_app[$input]);
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }

    /**
     * Chargement des controllers
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_controllers(string $input) : bool 
    {
        if (preg_match('#Controller$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(CONTROLLER_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
    /**
     * Chargement des modeles
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_models(string $input) : bool
    {
        if (preg_match('#Model$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(MODEL_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }
        
        return false;
    }
    /**
     * Chargement des entites
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_entities(string $input) : bool
    {
        if (preg_match('#Entity$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(ENTITY_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file)) 
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
    /**
     * Chargement des middlewares
     *
     * @param string $input
     * @return boolean
     */
    private static function autoload_middlewares(string $input) : bool 
    {
        if (preg_match('#Middleware$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);            
            
            $file = rtrim(MIDDLEWARE_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                
                return true;
            }
        }

        return false;
    }
}