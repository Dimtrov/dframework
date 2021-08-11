<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

namespace dFramework\core\loader;

use dFramework\core\Config;
use DI\Container;
use DI\ContainerBuilder;
/**
 * Injector
 *
 *  Dependency Injector Container
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.2.1
 * @file        /system/core/loader/Injector.php
 */
class Injector
{
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @var self
     */
    private static $instance;

    /**
     * Constructor
     */
    private function __construct()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions(Load::providers());

        if (Config::get('general.environment') === 'prod')
        {
            $builder->enableCompilation(SYST_DIR.'constants'.DS);
        }

        $this->container = $builder->build();
    }

    /**
     * Renvoie l'instance unique de la classe courante
     *
     * @return self
     */
    public static function instance() : self
    {
        if (null === self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Renvoie l'instance du conteneur
     *
     * @return Container
     */
    public static function container() : Container
    {
        return self::instance()->container;
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name.
     * @return mixed
     */
    public static function get(string $name)
    {
        return self::container()->get($name);
    }
	/**
	 * Alias of self::get
	 *
	 * @param string $classname
     */
	public static function singleton(string $classname)
	{
		return self::get($classname);
	}

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like singleton() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @param string $name Entry name or a class name.
     * @param array $parameters Optional parameters to use to build the entry. Use this to force specific parameters
     *                           to specific values. Parameters not defined in this array will be resolved using
     *                           the container.
     * @return mixed
     */
    public static function make(string $name, array $parameters = [])
    {
        return self::container()->make($name, $parameters);
    }
	/**
	 * Alias of self::make
	 *
	 * @param string $classname
     * @param array $parameters
	 */
	public static function factory(string $classname, array $parameters = [])
	{
		return self::make($classname, $parameters);
	}

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable $callable   Function to call.
     * @param array    $parameters Parameters to use. Can be indexed by the parameter names
     *                             or not indexed (same order as the parameters).
     *                             The array can also contain DI definitions, e.g. DI\get().
     *
     * @return mixed Result of the function.
     */
    public static function call($callable, array $params = [])
    {
        return self::container()->call($callable, $params);
    }

	/**
     * Test if the container can provide something for the given name.
     *
     * @param string $name Entry name or a class name
     * @return bool
     */
    public static function has(string $name) : bool
	{
		return self::container()->has($name);
	}

	/**
     * Define an object or a value in the container.
     *
     * @param string $name Entry name
     * @param mixed $value Value, use definition helpers to define objects
	 * @return void
     */
    public static function add(string $name, $value) : void
    {
		self::container()->set($name, $value);
    }
}
