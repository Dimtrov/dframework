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

namespace dFramework\core\db\orm\relations;

use Countable;
use IteratorAggregate;
use EmptyIterator;
use dFramework\core\db\orm\Result;
use dFramework\core\db\query\Hydrator;
use dFramework\core\models\Entity;

/**
 * Relation
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Orm
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/relations/Relation.php
 */
abstract class Relation implements Countable, IteratorAggregate
{
	/**
	 * @var Entity
	 */
	protected $parent;
	/**
	 * @var Entity
	 */
	protected $related;

	protected $join;

	protected $eagerLoading = false;
	protected $eagerKeys;
	protected $eagerResults;

	/**
	 * Constructor
	 *
	 * @param Entity $parent
	 * @param string $related
	 */
	public function __construct(Entity $parent, string $related)
	{
		$this->parent = $parent;
		$this->related = Hydrator::hydrate([], $related);
	}
	/**
	 * Recupere le resultat de la relation
	 *
	 * @return Entity[]|Entity|null
	 */
	public function result()
	{
		return $this->getResult();
	}
	abstract public function getResult();
	abstract public function setJoin();
	abstract public function match(Entity $parent);

	/**
	 * @param array $parent_rows
	 * @param mixed $related_keys
	 * @param string $relation
	 * @return array
	 */
	public function eagerLoad(array $parent_rows, $related_keys, string $relation ) : array
	{
		$this->eagerLoading = true;
		$this->eagerKeys = (array) $related_keys;

		foreach ($parent_rows As $i => $row)
		{
			$row->setRelation($relation, $this);

			$parent_rows[$i] = $row;
		}

		return $parent_rows;
	}

	/**
	 * Undocumented function
	 *
	 * @param Entity $parent
	 * @return mixed
	 */
	public function relate(Entity $parent)
	{
		if (empty($this->eagerResults))
		{
			if (empty($this->join))
			{
				$this->join = $this->setJoin();
			}

			$this->eagerResults = $this->join->get();
		}

		return $this->match($parent);
	}

	// Implements IteratorAggregate function so the result can be looped without needs to call get() first.
	public function getIterator()
	{
		$return = $this->getResult();

		return ($return instanceof Result) ? $return : new EmptyIterator;
	}

	// Implements Countable function
	public function count()
	{
		$result = $this->getResult();

		return ($result instanceof Result) ? count( $this->getResult() ) : 0;
	}

	// Chains with Active Record method if available
	public function __call(string $name, $param)
	{
		if (is_callable([$this->related, $name]))
		{
			if (empty($this->join))
			{
				$parent_data = $this->parent->getData();

				// If parent data is empty then it means we are eager loading.
				if (!empty($parent_data))
				{
					$this->join = $this->setJoin();
				}
				// No need to generate the "join", it will be generated later with eager loading method
				else
				{
					$this->join = $this->related;
				}
			}

			$return = call_user_func_array([$this->join, $name], $param);

			if ($return instanceof Result)
			{
				return $return;
			}
			else if ($name == 'get')
			{
				return new EmptyIterator;
			}
			return $this;
		}
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		$result = $this->getResult();
		if ($result instanceof Entity)
		{
			return $result->{$name};
		}
	}
}
