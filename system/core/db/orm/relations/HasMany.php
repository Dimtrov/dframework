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

use dFramework\core\models\Entity;

/**
 * HasMany
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/relations/HasMany.php
 */
class HasMany extends Relation
{
	/**
	 * @var string
	 */
	protected $foreign_key;

	/**
	 * Constructor
	 *
	 * @param Entity $parent
	 * @param string $related
	 * @param string $foreign_key
	 */
	public function __construct(Entity $parent, string $related, string $foreign_key)
	{
		parent::__construct($parent, $related);

		$this->foreign_key = $foreign_key;
	}

	public function setJoin()
	{
		if ($this->eagerLoading)
		{
			return $this->related->in($this->foreign_key, $this->eagerKeys);
		}
		return $this->related->where($this->foreign_key, $this->parent->getData( $this->parent->getPrimaryKey() ));
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
			if ($row->{$this->foreign_key} == $parent->getData( $parent->getPrimaryKey() ))
			{
				$return[] = $row;
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

		if (!empty($this->join) AND is_object($this->join) AND method_exists($this->join, 'get'))
		{
			return $this->join->get();
		}

		return [];
	}
}
