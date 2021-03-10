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

namespace dFramework\core\db\orm;

use ReflectionClass;
use dFramework\core\Config;
use dFramework\core\Entity;
use dFramework\core\db\Database;
use dFramework\core\loader\Load;
use dFramework\core\utilities\Str;
use dFramework\core\exception\Errors;
use dFramework\core\db\orm\Relations\HasOne;
use dFramework\core\db\orm\Relations\HasMany;
use dFramework\core\db\orm\Relations\BelongsTo;

/**
 * Model
 *
 * A database access layer for system orm
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/core/db/orm/Model.php
 */
class Model 
{
	/**
	 * @var boolean Specifie si on autoincremente les donnees en bd
	 */
	protected $incrementing = true;
	/**
	 * Verifie si une entite existe deja
	 *
	 * @var boolean
	 */
	public $exists = false;
	
	protected $per_page = 20;

	/**
	 * @var QueryBuilder
	 */
	private $queryBuilder = null;
	private $pagingBuilder = null;


	/**
	 * @var array Donnees de l'entite en provenance de la bd
	 */
	protected $data = [];

	/**
	 * @var Entity classe d'entite courrante
	 */
	public $class;


	// To stored loaded relation
	protected $relations = [];


	public function __construct(Entity $class, array $newData = [])
	{
		$this->class = $class;
		
		if (is_array($newData)) 
		{ 
			$this->setData( $newData); 
		}
	}


	/**
	 * Recupere les donnees
	 *
	 * @param string|null $field
	 * @return mixed
	 */
	public function getData(?string $field = null)
	{
		if (empty($field))
		{
			return $this->data;
		}
		return $this->data[$field] ?? ($this->data[self::getProperty($field)] ?? null);
	}

	/**
	 * Defini les propriete de la classe a partir des champs issu de la base de donnees
	 *
	 * @param string|array $field
	 * @param mixed $value
	 * @return Entity
	 */
	public function setData($field, $value = null)
	{
		if (is_array($field))
		{
			foreach ($field As $key => $value) 
			{
				$this->setData($key, $value);
			}
		}
		else 
		{
			$this->data[self::getProperty($field)] = $value;
		}

		return clone $this->class;
	}
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function toArray() : array
	{
		$array = $this->data;

		foreach ($this->relations As $relation => $models)
		{
			foreach ($models as $model)
			{
				$array[$relation ][] = $model->toArray();
			}
		}

		return $array;
	}
	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function json() : string
	{
		return json_encode($this->toArray());
	}


	/**
	 * Retrouve une donnees en fonction de sa cle primaire
	 *
	 * @param mixed $id
	 * @return mixed
	 */
	public function find($id)
	{
		$args = func_get_args();
		
		if (count($args) > 1)
		{
			$id = [];
			foreach ($args As $arg) 
			{ 
				$id[] = $arg; 
			}
		}
		$builder = $this->builder();
		$builder->in($this->getPrimaryKey(), (array) $id);

		$result = new Result($this, $builder);
		
		return is_array($id) ? $result->rows() : $result->first();
	}
	
	/**
	 * Recupere les donnees
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function get(array $columns = []) 
	{
		if (is_null( $this->queryBuilder )) 
		{
			return $this->all($columns);
		}
		if (!empty($columns)) 
		{
			$this->queryBuilder->select($columns);
		}
		$this->pagingBuilder = clone $this->queryBuilder;

		return (new Result($this, $this->queryBuilder))->rows();
	}

	/**
	 * Recupere toutes les donnees d'une table d'entite
	 *
	 * @param array $columns
	 * @return array
	 */
	public function all(array $columns = []) 
	{
		$builder = $this->builder();
		if (!empty($columns)) 
		{
			$builder->select($columns);
		}
		return (new Result($this, $builder))->rows();
	}
	
	/**
	 * Recupere les donnees du premier element dans la table d'entite
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function first(array $columns = [])
	{
		$builder = $this->queryBuilder ?: $this->builder();

		if (!empty($columns)) 
		{
			$builder->select($columns);
		}

		return (new Result( $this, $builder))->first();
	}

	/**
	 * Retourne la valeur d'un champ donné dans le premier enregistrement de la table d'entite
	 *
	 * @param string $field
	 * @return mixed
	 */
	protected function pluck(string $field)
	{
		return $this->first([$field])->{$field};
	}
	/**
	 * alias self::pluck
	 */
	protected function value(string $field)
	{
		return $this->pluck($field);
	} 

	/**
	 * Cree une entite ou modifie l'entite si elle existe deja
	 *
	 * @return mixed
	 */
	public function save(bool $from_fillable = false)
	{
		$data = $this->loadData($from_fillable);

		if (empty($data)) 
		{
			return false;
		}
		$builder = $this->builder();
		$pk = $this->getPrimaryKey();


		// Do an insert statement
		if (!$this->exists)
		{
			if ( !$this->incrementing AND empty($data[$pk])) 
			{
				return false;
			}

			$return = $builder->insert($data);

			if ($return !== false)
			{
				$this->exists = true;

				if ($this->incrementing) 
				{
					$this->setData($pk, $builder->lastId());
				}
			}

			return $return;
		}
		else
		{
			return $builder->update($this->getData(), [$pk => $this->getData($pk)]);
		}
	}
	
	/**
	 * Cree une nouvelle entite
	 *
	 * @param array $data
	 * @return Entity
	 */
	public function create(array $data) : Entity
	{
		if (empty($data)) 
		{
			return false;
		}

		$class = (new ReflectionClass($this->class))->newInstance($data);
		$class->save(true);

		return $class;
	}

	/**
	 * Modifie une entite
	 *
	 * @param array $data
	 * @return void
	 */
	public function update($data)
	{
		if (empty($this->queryBuilder))
		{
			$param = func_get_args();

			if (count($param) < 1) 
			{
				return false;
			}
			@list($data, $where) = $param;

			return $this->builder()->update($data, $where);
		}
		else if (is_object($this->queryBuilder))
		{
			return $this->queryBuilder->update($data);
		}
	}

	/**
	 * Supprime une entite
	 *
	 * @return mixed
	 */
	public function delete()
	{
		$pk = $this->getPrimaryKey();

		if (!$this->exists AND empty($this->queryBuilder))
		{
			$params = func_get_args();
			if (empty($params)) 
			{ 
				return false; 
			}
			$first = reset($params);
			
			if (is_array($first)) 
			{
				$params = $first;
			}
			$where = [];

			foreach ($params As $id)
			{
				if (is_array($id)) 
				{
					continue;
				}
				$where[] = $id;
			}

			$builder = $this->builder();

			if (count($where) <= 1) 
			{
				$builder->where($pk, reset($where));
			} 
			else 
			{
				$builder->in($pk, $where);
			}

			return $builder->delete();
		}

		if ($this->exists) 
		{
			$this->where($pk, $this->getData($pk));
		}

		if (is_object($this->queryBuilder))
		{
			$this->queryBuilder->delete();
		}
	}

	
	// ======================================
	// Pagination Methods
	// ======================================

	/**
	 * Pagine les resultats
	 *
	 * @param integer $page
	 * @param integer $per_page
	 * @return array
	 */
	public function paging(int $page = 1, int $per_page = null)
	{
		$page = intval($page);
		if (empty($page) OR $page < 0) 
		{
			$page = 1;
		}
		if (empty($per_page)) 
		{
			$per_page = $this->class->getPerPage();
		}
		$offset = ($page - 1) * $per_page;

		$builder = $this->builder();
		$builder->limit($per_page, $offset);
		return $this->get();
	}
	/**
	 * Genere une pagination navbar
	 *
	 * @param integer|null $per_page
	 * @return array
	 */
	public function paginate(?int $per_page = null)
	{
		$per_page = intval($per_page);
		if ($per_page <= 0) 
		{
			$per_page = $this->class->getPerPage();
		}
		
		$builder = $this->pagingBuilder ?: $this->builder();
		$builder->offset(false);
		
		$paginator = Load::library('Paginator');
		$paginator->init([
			'run_query' => false,
			'max_item'  => (int) $builder->count(),
			'limit'     => $per_page
		]);
		
		return $paginator->pagine();
	}


	// ======================================
	// Relationship Methods
	// ======================================

	/**
	 * Determine une cle etrangere pour la relation 
	 *
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return string
	 */
	private function getRelationFk(string $related, ?string $foreign_key = null) : string 
	{
		if (empty($foreign_key))
		{
			$related = explode('\\', preg_replace('#Entity$#', '', $related));
			$related = end($related);

			$pk = Database::indexes(plural(Str::toSnake($related)), 'PRIMARY');
			$foreign_key = $pk->fields[0] ?? Str::toSnake('id_' . singular($related));
		}
		return $foreign_key;
	}
	/**
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasOne
	 */
	public function hasOne(string $related, ?string $foreign_key = null) : HasOne
	{
		return new Relations\HasOne($this->class, $related, $this->getRelationFk($related, $foreign_key));
	}
	/**
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasMany
	 */
	public function hasMany(string $related, ?string $foreign_key = null) : HasMany
	{
		return new Relations\HasMany($this->class, $related, $this->getRelationFk($related, $foreign_key));
	}
	/**
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasMany
	 */
	public function belongsTo(string $related, ?string $foreign_key = null) : BelongsTo
	{
		return new Relations\BelongsTo($this->class, $related, $this->getRelationFk($related, $foreign_key));
	}

	public function belongsToMany(string $related, ?string $pivot_table = null, ?string $foreign_key = null, $other_key = null)
	{
		if (empty($pivot_table))
		{
			$models = [strtolower($this->class->getTable()), strtolower($related)];
			sort($models);

			$pivot_table = strtolower(implode('_', $models));
		}

		if (empty($foreign_key))
		{
			$foreign_key = strtolower(get_called_class()) . '_id';
		}
		if (empty($other_key))
		{
			$other_key = strtolower($related) . '_id';
		}
		$pivot_builder = new QueryBuilder($this->class, $pivot_table);

		return new Relations\BelongsToMany($this->class, $related, $pivot_builder, $foreign_key, $other_key);
	}

	/**
	 * Defini une relation
	 *
	 * @param string $name
	 * @param Relations\Relation $relation
	 * @return void
	 */
	public function setRelation(string $name, Relations\Relation $relation)
	{
		$this->relations[$name] = $relation->relate($this->class);
	}
	/**
	 * Recupere une relation
	 *
	 * @param string $name
	 * @return Relations\Relation
	 */
	public function getRelation(string $name) : ?Relations\Relation
	{
		return $this->relations[$name] ?? null;
	}
	// Eager loading for a single row? Just call the method
	public function load($related)
	{
		if(!method_exists($this, $related))
		{
			return false;
		} 

		$this->setRelation($related, $this->$related());
	}


	// ======================================
	// Aggregate Methods
	// ======================================

	protected function aggregates($function, $field)
	{
		return call_user_func([$this->builder(), $function], $field);
	}
	protected function max($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}
	protected function min($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}
	protected function avg($field)
	{
		return round( $this->aggregates(__FUNCTION__, $field), 2);
	}
	protected function sum($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}
	protected function count($field = null)
	{
		if (empty($field))
		{
			$field = $this->getPrimaryKey();
		}

		return $this->aggregates(__FUNCTION__, $field);
	}



	protected function builder() : QueryBuilder
	{
		if (!empty($this->queryBuilder))
		{
			return $this->queryBuilder;
		}
		return $this->queryBuilder = new QueryBuilder($this->class);
	}

	/**
	 * Renvoi la cle primaire de la classe d'entite courrante
	 *
	 * @return string
	 */
	public function getPrimaryKey() : string
	{
		return $this->class->getPrimaryKey();
	}
	/**
	 * Renvoi le nom de la classe
	 *
	 * @param bool $withNamespace
	 * @return string
	 */
	public function getClassName(bool $withNamespace = true) : string
	{
		$class = new ReflectionClass($this->class);
		return $withNamespace === true ? $class->getName() : $class->getShortName();
	}

	/**
	 * Load data from properties
	 */
	private function loadData(bool $from_fillable = true) : array
	{
		$data = [];
		if (true === $from_fillable)
		{
			foreach($this->class->fillables() As $field)
			{
				$data[$field] = $this->getData($field);
			}
		}
		else 
		{
			foreach ($this->data As $key => $value)
			{
				$data[Str::toSnake($key)] = $value;
			}
		}
		return $data;
	}
	/**
     * getProperty
     *
     * @param string $fieldName
     * @return string
     */
    private static function getProperty(string $fieldName) : string
    {
        $case = strtolower(Config::get('data.hydrator.case'));
        if (in_array($case, ['camel', 'pascal', 'snake', 'ada', 'macro']))
        {
            return Str::{'to'.$case}($fieldName);
        }        
        return $fieldName;
    }

	
	// ======================================
	// Magic Methods
	// ======================================

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		// Check if the method is available in this model
		if (method_exists($this, $name))
		{
			return call_user_func_array([$this, $name], $arguments);
		}

		// Check if the method is a "scope" method
        // Read documentation about scope method
        $scope = "scope" . Str::toPascal($name);
		if (method_exists($this, $scope))
		{
			array_unshift($arguments, $this);

			return call_user_func_array([$this, $scope], $arguments);
		}

		if (is_null($this->queryBuilder)) 
		{
			$this->queryBuilder = $this->builder();
		}
		if (is_callable([$this->queryBuilder, $name]))
		{
			call_user_func_array([$this->queryBuilder, $name], $arguments);
			return $this;
		}

		Errors::show_error('Unknown function '.$name, 'Unknow function');
	}
	
	/**
	 * @param string $field
	 * @return boolean
	 */
    public function __isset(string $field) : bool
	{
		return !empty($this->data[$field]) OR !empty($this->data[self::getProperty($field)]);
	}
}
