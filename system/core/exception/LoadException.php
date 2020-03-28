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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


 namespace dFramework\core\exception;

 use dFramework\core\Config;
 use dFramework\core\output\View;

/**
 * LoadException
 *
 * Manage exceptions of files loading system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.0
 * @file    	/system/core/exception/LoadException.php
 */

class LoadException extends Exception
{
    public static function except(string $message, int $code = 0)
    {
        if($code === 404 AND Config::get('general.environment') !== 'dev')
        {
            Config::set('general.use_template_engine', false);
            (new View('/reserved/errors/404', [
                'heading' => 'Page Not Found',
                'message' => 'The page you requested was not found.'
            ]))->render();
            exit;
        }
        else
        {
            parent::except($message, $code);
        }
    }
}