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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\db\orm;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Result
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db\Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/Result.php
 */
class Result implements Countable, IteratorAggregate
{
	/**
	 * @var Model
	 */
	protected $model = null;
	/**
	 * @var QueryBuilder
	 */
	protected $query = null;

	protected $rows;

	public function __construct(Model $model, QueryBuilder $query)
	{
		$this->model = $model;
		$this->query = $query;
	}

	/**
	 * Renvoi un resultat
	 *
	 * @param int|string $type
	 * @return mixed
	 */
	public function row($type = null)
	{
		if (empty($type))
		{
			$type = $this->model->getClassName();
		}
		return $this->query->one($type);
	}
	/**
	 * @alias self::row()
	 * @param int|string $type
	 * @return mixed
	 */
	public function first($type = null)
	{
		return $this->row($type);
	}
	/**
	 * @alias self::row()
	 * @param int|string $type
	 * @return mixed
	 */
	public function one($type = null)
	{
		return $this->row($type);
	}

	/**
	 * Recupere toute les donnees presende dans le resultat
	 *
	 * @param int|string $type
	 * @return array
	 */
	public function rows($type = null) : array
	{
		if (empty($type))
		{
			$type = $this->model->getClassName();
		}
		return $this->query->all($type);
	}
	/**
	 * @alias self::rows()
	 * @param int|string $type
	 * @return array
	 */
	public function all($type = null) : array
	{
		return $this->rows($type);
	}
	/**
	 * alias self::rows()
	 * @param int|string $type
	 * @return array
	 */
	public function result($type = null) : array
	{
		return $this->rows($type);
	}



	public function pluck($field, $type = null)
	{
		$first = $this->row($type);

		return $first->$field;
	}

	// Eager loading
	public function load($method)
	{
		if (!is_callable([$this->model, $method]))
		{
			return false;
		}

		$relation = call_user_func([$this->model, $method]);

		$primaries = array();

		foreach ($this->rows As $row)
		{
			$primaries[] = $row->getData( $row->getPrimaryKey() );
		}

		$this->rows = $relation->eagerLoad( $this->rows, $primaries, $method );
	}

	public function toArray() : array
	{
		$array = [];

		foreach ($this->rows As $row)
		{
			$array[] = $row->toArray();
		}

		return $array;
	}

	public function json() : string
	{
		return json_encode( $this->toArray() );
	}

	// Implements IteratorAggregate function
	public function getIterator() : Traversable
	{
		return new ArrayIterator($this->rows);
	}

	// Implements Countable function
	public function count() : int
	{
		return count($this->rows);
	}
}
