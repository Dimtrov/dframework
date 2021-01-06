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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */
 
namespace dFramework\core\generator;

use dFramework\core\db\Manager;

/**
 * Generator
 *
 * Generate a class file
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Generator
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.1
 * @file		/system/core/generator/Generator.php
 */
abstract class Generator
{

    protected $manager;
        
    /**
     * __construct
     *
     * @param string $db_setting
     * @return void
     */
    public function __construct(string $db_setting = 'default')
    {
        $this->manager = new Manager($db_setting);
    }

    abstract protected function createFile($render, $class, $dir);
}
