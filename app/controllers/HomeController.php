<?php

use dFramework\core\Controller;
use dFramework\core\utilities\Debugger as UtilitiesDebugger;
use Tracy\Debugger;

class HomeController extends Controller
{

    public function index()
    {
        echo 'Home Page';
    }

    public function method()
    {
        echo 'Some method';
    }

    public function params($a)
    {
        echo 'Page '.$a;
    }

}