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
     * Array contain every psr4 definition
     */
    private static $_class_map_psr = [];



    public static function load()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * @param $input
     */
    public static function autoload($input)
    {
        /**
         * Chargement des fichiers systeme
         */
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
                return;
            }
        }
        /**
         * Chargement des fichiers du namespace App
         */
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
                return;
            }
        }
        /**
        * Chargement des fichiers de namespace personnalisés
        */
        if (file_exists(APP_DIR.'config'.DS.'psr4.php'))
        {
            $class_map_file = APP_DIR.'config'.DS.'psr4.php';
            if (true !== in_array($class_map_file, \get_included_files()))
            {
                self::$_class_map_psr = require $class_map_file;
            }

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
                        return;
                    }

                    break;
                }
            }
        } 
        
        /**
         * Chargement des dependences systemes mapées
         */
        if (file_exists(SYST_DIR.'constants'.DS.'.classmap.php'))
        {
            $class_map_file = SYST_DIR.'constants'.DS.'.classmap.php';
            if (true !== in_array($class_map_file, \get_included_files()))
            {
                self::$_class_map_syst = require $class_map_file;
            }
            if (array_key_exists($input, self::$_class_map_syst))
            {
                $file = str_replace(['{SYST_DIR}', '\\'], [SYST_DIR, DS], self::$_class_map_syst[$input]);
                if (is_file($file))
                {
                    require_once $file;
                    return;
                }
            }
        }
        /**
         * Chargement des classes applicatives mapées
         */
        if (file_exists(APP_DIR.'resources'.DS.'reserved'.DS.'.classmap.php'))
        {
            $class_map_file = APP_DIR.'resources'.DS.'reserved'.DS.'.classmap.php';
            if (true !== in_array($class_map_file, \get_included_files()))
            {
                self::$_class_map_app = require $class_map_file;
            }
            if (array_key_exists($input, self::$_class_map_app))
            {
                $file = str_replace(['{APP_DIR}', '\\'], [APP_DIR, DS], self::$_class_map_app[$input]);
                if (is_file($file))
                {
                    require_once $file;
                    return;
                }
            }
        }

        /**
         * Chargement des controleurs
         */
        if (preg_match('#Controller$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(CONTROLLER_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                return;
            }
        }
        /**
         * Chargement des modeles
         */
        if (preg_match('#Model$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(MODEL_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                return;
            }
        }
        /**
         * Chargement des entites
         */
        if (preg_match('#Entity$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);

            $file = rtrim(ENTITY_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file)) 
            {
                require_once $file;
                return;
            }
        }
        /**
         * Chargement des middlewares
         */
        if (preg_match('#Middleware$#', $input))
        {
            $input = explode('\\', $input);
            $class = array_pop($input);
            $namespace = implode(DS, $input);            
            
            $file = rtrim(MIDDLEWARE_DIR . $namespace, DS) . DS . $class . '.php';
            if (is_file($file))
            {
                require_once $file;
                return;
            }
        }
    }
}

\dFramework\Autoloader::load();

require_once SYST_DIR . 'constants'.DS.'constants.php';