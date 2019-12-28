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
 * Exception
 *
 * General system exception of application
 *
 * @class       Exception
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/exception
 */


namespace dFramework\core\exception;



use Throwable;

class Exception extends \Exception
{

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    /**
     *
     */
    public static function init() : void
    {
        set_error_handler(function($level, $message, $file, $line){
            echo 'Erreur : ' .$message. '<br>';
            echo 'Niveau de l\'erreur : ' .$level. '<br>';
            echo 'Erreur dans le fichier : ' .$file. '<br>';
            echo 'Emplacement de l\'erreur : ' .$line. '<br>';
        });

        set_exception_handler(function($exception){
            $refletion = new \ReflectionClass($exception);
            $namespace = $refletion->getNamespaceName();

            if(strpos($namespace, __NAMESPACE__) !== false)
            {
                $exception->__toString();
            }
            else
            {
                echo "Exception non attrapée : " , $exception->getMessage(), "<br><br>";
                echo "File : " , $exception->getFile(), " -- ", $exception->getLine() , "<br>";
            }
        });
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




    /**
     * @return string|void
     */
    public function __toString()
    {
        $this->renderView();
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