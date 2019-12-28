<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT)
 *
 * @package	dFramework
 * @author	Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Config
 *
 * Configuration of application
 *
 * @class       Exception
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/exception
 */


namespace dFramework\core\exception;


use dFramework\core\Config;
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
        $env = Config::get('general.environment');
        if(strtolower($env) == 'development')
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

    }

}