<?php

use dFramework\core\Controller;

class HomeController extends Controller
{

    public function index()
    {
        $this->debug->dump($_SERVER);
    }

}