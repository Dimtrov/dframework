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

use dFramework\core\models\Entity;
use dFramework\core\db\query\Builder;
use dFramework\core\exception\Errors;

/**
 * QueryBuilder
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db\Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/QueryBuilder.php
 */
class QueryBuilder
{
	/**
	 * @var Builder
	 */
	protected $builder = null;

	/**
	 * @var string
	 */
	protected $table = '';

	/**
	 * @var Entity
	 */
	protected $class;

	public function __construct(Entity $class, ?string $table = null)
	{
		$this->builder = new Builder($class->getGroup());
		$this->class = $class;
		$this->table = empty($table) ? $class->getTable() : $table;
		$this->builder->from($this->table);
	}


	public function __call($name, $arguments)
	{
		if (!method_exists($this->builder, $name))
		{
			Errors::show_error('La fonction "'.$name.'" que vous tentez d\'utiliser n\'existe pas', 'Unknown function');
		}

		return call_user_func_array([$this->builder, $name], $arguments);
	}

	public function __clone()
	{
		$this->builder = clone $this->builder;
	}


	/**
     * Renvoi le dernier id generé par autoincrement
     *
     * @return integer|null
     */
    public function lastId() : ?int
    {
        return $this->builder->insertID();
    }
	/**
	 * Selection
	 *
	 * @param array|string $columns
	 * @return object
	 */
	public function select($columns = [])
	{
		return $this->builder->select($columns);
	}
	/**
	 * Insertion
	 *
	 * @param array $data
	 * @return int|false
	 */
	public function insert($data)
	{
		$insert = $this->builder->from($this->table)->insert($data);

		return ($insert !== false)
			? $this->builder->lastId($this->table)
			: false;
	}
	/**
	 * Modification
	 *
	 * @param array $data
	 * @param array $where
	 * @return void
	 */
	public function update(array $data, array $where = [])
	{
		return $this->builder->from($this->table)->where($where)->update($data);
	}
}
