<?php

use dFramework\core\db\migration\Runner;

class TestController extends AppController
{
    public function index() 
    {
      $run = new Runner();

      $run->regress();
    }
}