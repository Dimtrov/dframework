<?php 

namespace dFramework\components\orm;

use dFramework\core\db\Builder;
use dFramework\core\exception\Errors;

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


	public function __construct(string $db_group, string $table)
	{
		$this->builder = (new Builder)->setDb($db_group);

		$this->table = $table;
		$this->builder->from( $this->table );
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
	 * Undocumented function
	 *
	 * @param array|string $columns
	 * @return object
	 */
	public function select($columns = [])
	{
		return $this->builder->select($columns);
	}

	public function insert($data)
	{
		$insert = $this->builder->from($this->table)->insert($data);

		return ($insert !== false) 
			? $this->builder->lastId($this->table) 
			: false;
	}

	public function update(array $data, array $where = [])
	{
		return $this->builder->from($this->table)->where($where)->update($data);
	}
}