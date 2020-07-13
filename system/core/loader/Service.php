<?php 

namespace dFramework\core\loader;

use dFramework\core\http\Request;
use dFramework\core\http\Response;
use dFramework\core\http\Uri;
use dFramework\core\output\Cache;

class Service 
{
    protected $dic;

    
    public static function init()
    {
        self::instance()->dic = DIC::instance();
        self::instance()->addServices(); 
    }
    public static function instance() 
    {
        if (null === self::$_instance) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private static $_instance;
    
    
    private function addServices()
    {
        $this->dic
            ->addInstance(new Request,  'request')
            ->addInstance(new Response, 'response')
            ->addInstance(new Uri,      'uri')
            ->addInstance(new Cache,    'cache');
    }

    public static function __callStatic($name, $arguments)
    {
        return self::instance()->dic->get($name);
    }
}
