<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\db\query;

use dFramework\core\generator\Entity;

/**
 * Hydrator
 *
 * Database entities hydrator
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/db/query/Hydrator.php
 */
class Hydrator
{

    /**
     * @param array $datas
     * @param string $class
     * @param string $dir
     * @return \dFramework\core\models\Entity
     */
    public static function hydrate(array $datas, string $class, string $dir = '')
    {
        $class = preg_replace('#Entity#isU', '', $class) . 'Entity';
        
        $dir = ENTITY_DIR.trim($dir, '/\\');
        $dir = str_replace(['/', '\\'], DS, $dir);
        $dir = rtrim($dir, DS).DS;

        $file = $dir . ucfirst($class) . '.php';

        if (!is_file($file))
        {
            self::makeEntityClass($class, $dir);
        }
        require_once $file;

        $instance = new $class($datas, false);

        if (method_exists($instance, 'hydrate')) 
        {
            $instance->hydrate($datas);
        }

        return $instance;
    }

    /**
     * @param string $class
     * @param string $dir
     * @param string $db_setting
     */
    public static function makeEntityClass(string $class, string $dir, ?string $group = null)
    {
        (new Entity)->generate($class, $dir);
    }
}