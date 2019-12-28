<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */


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


namespace dFramework;


class Autoloader
{
    static function load()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * @param $input
     */
    static function autoload($input)
    {
        if(strpos($input, __NAMESPACE__ . '\\') !== false)
        {
            $input = str_replace('dFramework\\', '', $input);
            $input = explode('\\', $input);

            $class = array_pop($input);
            $namespace = implode(DIRECTORY_SEPARATOR, $input);

            require_once __DIR__. DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $class . '.php';
        }
    }
}
\dFramework\Autoloader::load();
\dFramework\core\dFramework::init();