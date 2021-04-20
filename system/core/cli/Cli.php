<?php 
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\cli;

use dFramework\core\exception\Exception;

/**
 * CLi
 * A abstract factory class for cli commands
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.3.0
 * @file        /system/core/cli/Cli.php
 */
abstract class Cli 
{   
    public static function __callStatic($name, $arguments)
    {
        $instance = new static;
        
        if (method_exists($instance, $name))
        {
            return call_user_func_array([$instance, $name], $arguments);
        }
        if (method_exists($instance, '_'.$name))
        {
            return call_user_func_array([$instance, '_'.$name], $arguments);
        }
        throw new Exception("Method < " .__CLASS__.'::'.$name. "() > not found");
    }
}