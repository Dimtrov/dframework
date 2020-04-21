<?php

use dFramework\core\Controller;

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

    public function _remap($method, $params = [])
    {
        echo 'Remapper';

        $this->debug->dump(func_get_args());
    }
}