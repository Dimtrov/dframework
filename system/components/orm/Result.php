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

use Countable;
use ArrayIterator;
use IteratorAggregate;
use PDO;

/**
 * Result
 *
 * @package		dFramework
 * @subpackage	Components
 * @category 	Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/components/orm/Result.php
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

	public function __construct(Model $model, QueryBuilder $query = null)
	{
		$this->model = $model;
		$this->query = $query;
	}

	public function row()
	{
		$rowData = $this->query->one(PDO::FETCH_ASSOC);
	
		return $this->model->setData($rowData);
	}

	public function rows() : array
	{
		$rows = $this->query->all(PDO::FETCH_ASSOC);

		$return = [];
		foreach ($rows As $rowData)
		{
			$return[] = $this->model->setData($rowData);;
		}

		return $return;
	}

	// Alias for row();
	public function first()
	{
		return $this->row();
	}

	public function pluck($field)
	{
		$first = $this->row();

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
	public function getIterator() : ArrayIterator
	{
		return new ArrayIterator($this->rows);
	}

	// Implements Countable function
	public function count() : int
	{
		return count($this->rows);
	}
}
