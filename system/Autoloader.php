<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.1
 */

namespace dFramework;

use Nette\Loaders\RobotLoader;

/**
 * Autoloader
 *
 * Autoload a dFramework system class
 *
 * @class       Autoloader
 * @package		dFramework
 * @subpackage	null
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
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

    static function load()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * @param $input
     */
    static function autoload($input)
    {
        if (strpos($input, __NAMESPACE__ . '\\') !== false)
        {
            $input = str_replace(__NAMESPACE__ . '\\', '', $input);
            $input = explode('\\', $input);

            $class = array_pop($input);
            $namespace = implode(DIRECTORY_SEPARATOR, $input);

            require_once __DIR__. DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $class . '.php';
        }
        else 
        {
            if (file_exists(SYST_DIR.'constants'.DIRECTORY_SEPARATOR.'.classmap.php'))
            {
                $class_map_file = SYST_DIR.'constants'.DIRECTORY_SEPARATOR.'.classmap.php';
                if(true !== in_array($class_map_file, \get_included_files()))
                {
                    self::$_class_map_syst = require $class_map_file;
                }
                if(array_key_exists($input, self::$_class_map_syst))
                {
                    require_once str_replace('{SYST_DIR}', SYST_DIR, self::$_class_map_syst[$input]);
                }
            }
            if (file_exists(APP_DIR.'resources'.DIRECTORY_SEPARATOR.'reserved'.DIRECTORY_SEPARATOR.'.classmap.php'))
            {
                $class_map_file = APP_DIR.'resources'.DIRECTORY_SEPARATOR.'reserved'.DIRECTORY_SEPARATOR.'.classmap.php';
                if(true !== in_array($class_map_file, \get_included_files()))
                {
                    self::$_class_map_app = require $class_map_file;
                }
                if(array_key_exists($input, self::$_class_map_app))
                {
                    require_once str_replace('{APP_DIR}', APP_DIR, self::$_class_map_app[$input]);
                }
            }
        }
    }
}

\dFramework\Autoloader::load();

require_once SYST_DIR . 'constants'.DIRECTORY_SEPARATOR.'constants.php';