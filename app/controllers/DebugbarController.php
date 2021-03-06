<?php 

use dFramework\core\output\Debugbar;

class DebugbarController extends AppController
{
    public function index() 
    {
        $debugbar = new Debugbar();
        $debugbarRenderer = $debugbar->jsRenderer();

        $debugbar["messages"]->addMessage("hello world!");

        ?><html>
        <head>
            <?php echo $debugbarRenderer->renderHead() ?>
        </head>
        <body>
            ...
            <?php echo $debugbarRenderer->render() ?>
        </body>
    </html><?php
    }
}
