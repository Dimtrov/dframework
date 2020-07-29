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
 *  @version    3.2.1
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
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
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
     * Recupere une seule instance de l'objet (singleton) via le conteneur
     *
     * @param string $classname
     * @return mixed
     */
    public static function singleton(string $classname)
    {
        return self::container()->get($classname);
    }

    /**
     * Cree une nouvelle instance d'une classe
     *
     * @param string $classname
     * @param array $params
     * @return mixed
     */
    public static function factory(string $classname, array $params = [])
    {
        return self::container()->make($classname, $params);
    } 

    /**
     * Undocumented function
     *
     * @param Callable $callable
     * @param array $params
     * @return mixed
     */
    public static function call($callable, array $params = [])
    {
        return self::container()->call($callable, $params);
    }
}
