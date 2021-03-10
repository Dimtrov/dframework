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

namespace dFramework\core\db\orm\Relations;

use PDO;
use dFramework\core\Entity;
use dFramework\core\db\orm\Model;

/**
 * BelongsToMany
 * 
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/BelongsToMany.php
 */
class BelongsToMany extends Relation 
{

	protected $pivot_builder;
	protected $pivot_result;
	/**
	 * @var string
	 */
	protected $foreign_key;
	protected $other_key;

	/**
	 * Construtor
	 *
	 * @param Entity $parent
	 * @param string $related
	 * @param mixed $pivot_builder
	 * @param string $foreign_key
	 * @param mixed $other_key
	 */
	function __construct(Entity $parent, string $related, $pivot_builder, string $foreign_key, $other_key)
	{
		parent::__construct($parent, $related);

		$this->pivot_builder = $pivot_builder;
		$this->foreign_key = $foreign_key;
		$this->other_key = $other_key;
	}

	function setJoin()
	{
		if( $this->eagerLoading )
		{
			$pivot_query = $this->pivot_builder->in($this->foreign_key, $this->eagerKeys);
		}
		else
		{
			$pivot_query = $this->pivot_builder->where($this->foreign_key, $this->parent->getData( $this->parent->getPrimaryKey() ))->get();
		}

		$other_id = [];

		$this->pivot_result = $pivot_query->all(PDO::FETCH_ASSOC);
		foreach ($this->pivot_result As $row)
		{
			$other_id[] = $row[ $this->other_key ];
		}

		$other_id = array_unique($other_id);

		if (!empty($other_id)) 
		{
			return $this->related->whereIn( $this->related->getPrimaryKey(), $other_id );
		}
	}

	/**
	 * @param Entity $parent
	 * @return array
	 */
	public function match(Entity $parent) : array
	{
		$return = [];

		foreach ($this->eagerResults As $row)
		{
			foreach ($this->pivot_result As $pivot_row)
			{
				if (
					$parent->getData( $parent->getPrimaryKey() ) == $pivot_row[ $this->foreign_key ] and
					$row->getData( $row->getPrimaryKey() ) == $pivot_row[ $this->other_key ]
				)
				{
					$return[] = $row;
					break;
				}
			}
		}

		return $return;
	}

	/**
	 * Recupere le resultat de la relation
	 *
	 * @return Entity[]
	 */
	public function getResult() : array
	{
		if (empty($this->join)) 
		{
			$this->join = $this->setJoin();
		}
		return $this->join->all();
	}
}
