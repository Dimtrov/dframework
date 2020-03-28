<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core\loader;

/**
 * DIC
 *
 *  Dependency Injector Container
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
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
    private static function instance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new DIC;
        }
        return self::$_instance;
    }


    /**
     * @param string $key
     * @param callable $resolver
     */
    public static function set(string $key, Callable $resolver)
    {
        self::instance()->registries[$key] = $resolver;
    }


    /**
     * @param string $key
     * @param callable $resolver
     */
    public static function setFactory(string $key, Callable $resolver)
    {
        self::instance()->factories[$key] = $resolver;
    }


    /**
     * @param $instance
     * @throws \ReflectionException
     */
    public static function setInstance($instance)
    {
        $reflection = new \ReflectionClass($instance);

        self::instance()->instances[$reflection->getName()] = $instance;
    }


    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function get(string $key)
    {
        $instance = self::instance();

        if(isset($instance->factories[$key]))
        {
            return $instance->factories[$key]();
        }
        if(!isset($instance->instances[$key]))
        {
            if(isset($instance->registries[$key]))
            {
                $instance->instances[$key] = $instance->registries[$key]();
            }
            else
            {
                $reflected_class = new \ReflectionClass($key);
                if(true !== $reflected_class->isInstantiable())
                {
                    throw new \Exception($key. ' is not an instantiable class');
                }
                else
                {
                    $constructor = $reflected_class->getConstructor();
                    if($constructor)
                    {
                        $parameters = $constructor->getParameters();
                        $constructor_parameters = [];
                        foreach ($parameters As $parameter)
                        {
                            if($parameter->getClass())
                            {
                                $constructor_parameters[] = self::get($parameter->getClass()->getName());
                            }
                            else
                            {
                                $constructor_parameters[] = $parameter->getDefaultValue();
                            }
                        }
                        $instance->instances[$key] = $reflected_class->newInstanceArgs($constructor_parameters);
                    }
                    else
                    {
                        $instance->instances[$key] = $reflected_class->newInstance();
                    }
                }
            }
        }
        return $instance->instances[$key];
    }
}