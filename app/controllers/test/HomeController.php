<?php

use dFramework\core\dFramework;

class HomeController extends \dFramework\components\rest\Controller
{
    public function index()
    {
        $this->send_error();
    }
}