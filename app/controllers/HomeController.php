<?php

use dFramework\core\Controller;
use dFramework\core\db\Manager;
use dFramework\core\generator\Entity;
use dFramework\core\generator\Model;
use dFramework\core\utilities\Chaine;
use dFramework\core\utilities\Debugger as UtilitiesDebugger;
use Tracy\Debugger;

class HomeController extends Controller
{

    public function index()
    {
        echo Chaine::toCamelCase('TEste encore');
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