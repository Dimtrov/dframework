<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage	https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\exception;

use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Exception
 *
 * General system exception of application
 *
 * @class       Exception
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.0
 * @file        /system/core/exception/Exception.php
 */

class Exception extends \Exception
{
    /**
     * Initialise la capture des exception
     */
    public static function init() : void
    {  
        $whoops  =  new Run();
        $whoops->pushHandler(new PrettyPageHandler); 
        $whoops->pushHandler([New Log, 'register']);
        $whoops->register();
    }


    /**
     * @param \Exception $e
     */
    public static function Throw(\Exception $e)
    {
        die('Exception: <br><pre>'.print_r($e, true).'</pre>');
    }

    /**
     * @param string $message
     * @param int $code
     */
    public static function show(string $message, int $code = 0)
    {
        die($message);
    }

    public static function except(string $message, int $code = 0)
    {
        $class = get_called_class();
        $class = trim(str_replace('Exception', '', $class));
        $class = (empty($class)) ? 'General' : $class;

        $backtrace = debug_backtrace();

        var_dump($message, $class);
        die();
    }



    /**
     * @param string $exception_type
     */
    protected function renderView(string $exception_type = 'General Exception')
    {

        ?>
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport"
                  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title><?= $exception_type; ?></title>
            <style>
                html {padding:0;margin:0;width:100%;height:100%;background:whitesmoke;}
                body{padding:1em;margin:5% auto;width:70%;background:white;border:1px solid rgba(0, 0, 0, .1);font-family:sans-serif;}
                h1{color:cornflowerblue;border-bottom:1px solid;}
                dl{padding:5px;font-size:.9em;line-height:1.5em;}
                .row{display:-ms-flexbox;display:flex;}
                .col-2{-ms-flex:0 0 16.666667%;flex:0 0 16.666667%;max-width:16.666667%}.col-10{-ms-flex:0 0 83.333333%;flex:0 0 83.333333%;max-width:83.333333%}
                *{word-break: break-word}
            </style>
        </head>
        <body>
        <h1><?= $exception_type; ?></h1>
        <dl class="row">
            <dt class="col-2">Code</dt>
            <dd class="col-10"><?= $this->getCode(); ?></dd>
        </dl>
        <dl class="row">
            <dt class="col-2">Message</dt>
            <dd class="col-10"><?= $this->getMessage(); ?></dd>
        </dl>
        <dl class="row">
            <dt class="col-2">File</dt>
            <dd class="col-10"><?= $this->getFile(); ?></dd>
        </dl>
        <dl class="row">
            <dt class="col-2">Line</dt>
            <dd class="col-10"><?= $this->getLine(); ?></dd>
        </dl>

        <hr size="1" style="margin-bottom: 0; margin-top: 2em">
        <p style="position: relative; padding-bottom: 1em">
            <small style="position: absolute; right: 0">Powered By dFramework</small>
        </p>
        </body>
        </html>
        <?php

        die();
    }

}