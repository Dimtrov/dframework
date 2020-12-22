<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2020, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2020, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2020, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.2
 */

namespace dFramework\components\orm;

use ReflectionClass;
use ReflectionProperty;

/**
 * DefinitionReader
 *
 * @package		dFramework
 * @subpackage	Components
 * @category 	Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/components/orm/DefinitionReader.php
 */
class DefinitionReader 
{

    /**
     * @var ReflectionClass
     */
    private $class;

    public function __construct(ReflectionClass $class)
    {
        $this->class = $class;
    }

    public function getTableDefinition() 
    {
        return $this->class->getConstant('table');
    }

    public function getPropertyDefinition(ReflectionProperty $property) : ?object 
    {
        $definition = $this->class->getConstant('properties');
        
        $object = $definition[$property->getName()] ?? null;

        if (!empty($object)) 
        {
            if (is_string($object))
            {
                $object = json_decode($object, true);
            }
            
            return (object) array_merge($object, ['name' => $property->getName()]);
        }

        return null;
    }
}
