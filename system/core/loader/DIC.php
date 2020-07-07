<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2
 */


namespace dFramework\core\loader;

use dFramework\core\exception\Exception;

/**
 * DIC
 *
 *  Dependency Injector Container
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       2.0
 * @file        /system/core/loader/DIC.php
 */
class DIC
{

    /**
     * @var array
     */
    private $registries = [];
    /**
     * @var array
     */
    private $instances = [];
    /**
     * @var array
     */
    private $factories = [];

    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * @return DIC|null
     */
    public static function instance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private function __construc() 
    {

    }

    /**
     * Add a classname in container
     * 
     * @param string $key
     * @param callable $resolver
     * @return DIC
     */
    public function add(string $key, callable $resolver) : self
    {
        $this->registries[$key] = $resolver;
        
        return $this;
    }


    /**
     * Add an factorie classname in container
     * 
     * @param string $key
     * @param callable $resolver
     * @return DIC
     */
    public function addFactory(string $key, callable $resolver) : self
    {
        $this->factories[$key] = $resolver;
        
        return  $this;
    }


    /**
     * Add an instance class in container
     * 
     * @param object $instance
     * @param string $alias
     * @throws \ReflectionException
     * @return DIC
     */
    public function addInstance(object $instance, string $alias = null) : self
    {
        $reflection = new \ReflectionClass($instance);
        
        if (null === $alias) 
        {
            $alias = $reflection->getName();
        }
        $this->instances[$alias] = $instance;

        return $this;
    }


    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function get(string $key)
    {
        if (isset($this->factories[$key]))
        {
            return $this->factories[$key]();
        }

        if (!isset($this->instances[$key]))
        {
            if (isset($this->registries[$key]))
            {
                $this->instances[$key] = $this->registries[$key]();
            }
            else
            {
                $reflected_class = new \ReflectionClass($key);
                if (true !== $reflected_class->isInstantiable())
                {
                    Exception::except($key. ' is not an instantiable class');
                }
                else
                {
                    $constructor = $reflected_class->getConstructor();
                    if ($constructor)
                    {
                        $parameters = $constructor->getParameters();
                        $constructor_parameters = [];
                        foreach ($parameters As $parameter)
                        {
                            if ($parameter->getClass())
                            {
                                $constructor_parameters[] = $this->get($parameter->getClass()->getName());
                            }
                            else
                            {
                                $constructor_parameters[] = $parameter->getDefaultValue();
                            }
                        }
                        $this->instances[$key] = $reflected_class->newInstanceArgs($constructor_parameters);
                    }
                    else
                    {
                        $this->instances[$key] = $reflected_class->newInstance();
                    }
                }
            }
        }

        return $this->instances[$key];
    }
}
