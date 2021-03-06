<?php

use dFramework\core\db\migration\Runner;

class TestController extends AppController
{
    public function index() 
    {
        
        $runner = Runner::instance();
        $migrations = $runner->up();

        foreach ($migrations As $migration) 
                        {
                            $runner->launch($migration, 'up');
                        }
        vd($migrations);
    }
}