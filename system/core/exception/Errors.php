<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

 namespace dFramework\core\exception;

use dFramework\core\Config;
use dFramework\core\http\Response;
use dFramework\core\loader\Service;

/**
 * Errors
 *
 * Trigger errors
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1
 * @file    	/system/core/exception/Errors.php
 */

class Errors
{

    public function log($message, $code, $file, $line)
    {
        Log::save($message, $code, $file, $line);
    }


    public static function show404(string $message = 'The page you requested was not found.', string $heading = 'Page Not Found', array $params = [])
    {
        return self::show_error($message, $heading, $params, 404);
    }


    public static function show_error($message, $heading, array $params = [], int $status_code = 500)
    {
        if(\php_sapi_name() === 'cli')
        {
            $message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
            
            echo "\nERROR: ",
                $heading,
                "\n\n",
                $message,
                "\n\n";
            exit;
        }
        $message = '<p>'.(is_array($message) ? implode('</p><p>', $message) : $message).'</p>';

        Config::set('general.use_template_engine', false);
        Service::response()->statusCode($status_code);
        Service::viewer()->addData(array_merge(
            !is_array($params) ? [] : $params, 
            compact('message', 'heading')
        ))->display('/reserved/errors/'.$status_code)->render();
        exit;       
    }
}