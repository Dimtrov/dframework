<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    2.1
 */

/**
 * Config
 *
 * Configuration of application
 *
 * @class       LoadException
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 */


namespace dFramework\core\exception;


use dFramework\core\Config;
use dFramework\core\output\View;
use Throwable;

class LoadException extends Exception
{

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    /**
     * @return string|void
     */
    public function __toString()
    {
        if(Config::get('general.environment') === 'dev')
        {
            $this->renderView('Load Exception');
        }
        else
        {
            $this->notFound();
        }
    }


    protected function notFound()
    {
        (new View('/errors/404', [
            'heading' => 'Page Not Found',
            'message' => 'The page you requested was not found.'
        ]))->render();
        exit;
    }

}