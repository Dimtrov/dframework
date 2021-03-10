<?php 
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\db\migration;

use ArrayAccess;
use dFramework\core\support\contracts\Arrayable;
use dFramework\core\support\contracts\Jsonable;
use JsonSerializable;

/**
 * Column
 * Schema columns definitions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Migration
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/migration/Column.php
 * 
 * @method Column after(string $column) Place the column "after" another column (MySQL)
 * @method Column always() Used as a modifier for generatedAs() (PostgreSQL)
 * @method Column autoIncrement() Set INTEGER columns as auto-increment (primary key)
 * @method Column change() Change the column
 * @method Column charset(string $charset) Specify a character set for the column (MySQL)
 * @method Column collation(string $collation) Specify a collation for the column (MySQL/PostgreSQL/SQL Server)
 * @method Column comment(string $comment) Add a comment to the column (MySQL)
 * @method Column default(mixed $value) Specify a "default" value for the column
 * @method Column first() Place the column "first" in the table (MySQL)
 * @method Column generatedAs(string|Expression $expression = null) Create a SQL compliant identity column (PostgreSQL)
 * @method Column index(string $indexName = null) Add an index
 * @method Column nullable(bool $value = true) Allow NULL values to be inserted into the column
 * @method Column primary() Add a primary index
 * @method Column spatialIndex() Add a spatial index
 * @method Column storedAs(string $expression) Create a stored generated column (MySQL)
 * @method Column unique() Add a unique index
 * @method Column unsigned() Set the INTEGER column as UNSIGNED (MySQL)
 * @method Column useCurrent() Set the TIMESTAMP column to use CURRENT_TIMESTAMP as default value
 * @method Column virtualAs(string $expression) Create a virtual generated column (MySQL)
 * @method Column persisted() Mark the computed generated column as persistent (SQL Server)
 */
class Column  implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }


    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
    
    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get an attribute from the fluent instance.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) 
        {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * Get the attributes from the fluent instance.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }


    /**
     * Convert the fluent instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
