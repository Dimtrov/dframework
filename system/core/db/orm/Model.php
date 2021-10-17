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
 *  @version    3.3.4
 */

namespace dFramework\core\db\orm;

use Exception;
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
use dFramework\core\db\orm\Relations\BelongsToMany;

/**
 * Model
 *
 * A database access layer for system orm
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/orm
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
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
	protected $exists = false;

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
	 * @var array Donnees reelles de la bd sans transformations de casse
	 */
	private $trustData = [];

	/**
	 * @var Entity classe d'entite courrante
	 */
	public $entity;


	// To stored loaded relation
	protected $relations = [];


	/**
	 * construct
	 *
	 * @param Entity $entity
	 * @param array $newData
	 */
	public function __construct(Entity $entity, array $newData = [])
	{
		$this->entity = $entity;

		if (is_array($newData))
		{
			$this->setData($newData);
		}
		helper('inflector');
	}

	/**
	 * Modifie le parametre d'existanse d'un element
	 *
	 * @param boolean $exists
	 * @return void
	 */
	public function setExist(bool $exists)
	{
		$this->exists = $exists;
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
		return $this->data[$field] ?? ($this->data[self::getProperty($field)] ?? ($this->trustData[$field] ?? null));
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
			$this->trustData[$field] = $value;
		}

		return clone $this->entity;
	}

	/**
	 * Renvoie les données exposées de l'entité sous forme de tableau associatif
	 *
	 * @return array
	 */
	public function toArray() : array
	{
	    $exposed = array_map(function ($elt) {
	        return self::getProperty($elt);
        }, $this->entity->exposes());

        $array = [];

        if (empty($exposed))
        {
            $array = $this->data;
        }
        else
        {
            foreach ($this->data As $key => $value)
            {
                if (in_array($key, $exposed))
                {
                    $array[$key] = $value;
                }
            }
        }

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
	 * Renvoie les propriétés exposées de l'entité sous forme de chaine json
	 *
	 * @return string
	 */
	public function json() : string
	{
		return json_encode($this->toArray());
	}
	/**
	 * Renvoie les propriétés exposées de l'entité sous forme de chaine json
	 *
	 * @alias self::json()
	 * @return string
	 */
	public function toJson() : string
	{
		return $this->json();
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
	 * Retrouve une donnees en fonction de sa cle primaire
	 *
	 * @param mixed $id
	 * @alias self::find()
	 * @return mixed
	 */
	public function findByPk($id)
	{
		return $this->find($id);
	}
	/**
	 * Retrouve une donnees en fonction de sa cle primaire
	 *
	 * @param mixed $id
	 * @alias self::find()
	 * @return mixed
	 */
	public function findById($id)
	{
		return $this->find($id);
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

		return (new Result($this, $builder))->first();
	}
	/**
	 * Recupere les donnees du premier element dans la table d'entite
	 *
	 * @param array $columns
	 * @alias self::first()
	 * @return mixed
	 */
	public function one(array $columns = [])
	{
		return $this->first($columns);
	}

	/**
	 * Retourne la valeur d'un champ donné dans le premier enregistrement de la table d'entite
	 *
	 * @param string $field
	 * @return mixed
	 */
	public function pluck(string $field)
	{
		return $this->first([$field])->{$field};
	}
	/**
	 * Retourne la valeur d'un champ donné dans le premier enregistrement de la table d'entite
	 *
	 * @param string $field
	 * @alias self::pluck
	 * @return mixed
	 */
	public function value(string $field)
	{
		return $this->pluck($field);
	}

	/**
	 * Cree une entite ou modifie l'entite si elle existe deja
	 *
	 * @return mixed
	 */
	public function save(bool $from_accept = true)
	{
		$pk = $this->getPrimaryKey();
		$data = $this->loadData($from_accept);
		$builder = $this->builder();

		if (empty($data))
		{
			return false;
		}

		if ($this->exists)
		{
			if (method_exists($this->entity, 'beforeUpdate'))
			{
				$data = call_user_func([$this->entity, 'beforeUpdate'], $data);
			}
			return $builder->update($data, [$pk => $data[$pk]]);
		}

		if ( !$this->incrementing AND empty($data[$pk]))
		{
			return false;
		}
		if (method_exists($this->entity, 'beforeCreate'))
		{
			$data = call_user_func([$this->entity, 'beforeCreate'], $data);
		}

		$entry = $builder->insert($data);

		if ($entry !== false)
		{
			$this->exists = true;
			$this->setData($data);

			if ($this->incrementing)
			{
				$this->setData($pk, $builder->lastId());
			}
		}

		return $entry;
	}

	/**
	 * Cree une nouvelle entite
	 *
	 * @param array $data
	 * @return Entity|false
	 */
	public function create(array $data)
	{
		if (empty($data))
		{
			return false;
		}

		$entity = (new ReflectionClass($this->entity))->newInstance($data);
		$entity->save(true);

		return $entity;
	}

	/**
	 * Modifie une entite
	 *
	 * @param array $data
	 * @return mixed
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
			$per_page = $this->entity->getPerPage();
		}
		$offset = ($page - 1) * $per_page;

		$builder = $this->builder();
		$builder->limit($per_page, $offset);
		return $this->get();
	}

	/**
	 * Genere une navbar de pagination
	 *
	 * @param integer|null $per_page
	 * @return array
	 */
	public function paginate(?int $per_page = null)
	{
		$per_page = intval($per_page);
		if ($per_page <= 0)
		{
			$per_page = $this->entity->getPerPage();
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
	 * @param bool $has Specifie si on est dans une relation de type hasOne ou hasMany
	 * @param string $related Classe de relation
	 * @param string|null $foreign_key Cle par defaut
	 * @return string
	 */
	private function getRelationFk(bool $has, string $related, ?string $foreign_key = null) : string
	{
		if (empty($foreign_key) AND true == $has)
		{
			$foreign_key = $this->entity->getPrimaryKey();
		}
		if (empty($foreign_key))
		{
			$related = $this->makeTableFromClass($related);

			$pk = Database::indexes($related, 'PRIMARY');
			$foreign_key = $pk->fields[0] ?? 'id_' . singularize($related);
		}

		return $foreign_key;
	}

	/**
	 * Cree une relation de type 1-1
	 *
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasOne
	 */
	public function hasOne(string $related, ?string $foreign_key = null) : HasOne
	{
		return new Relations\HasOne($this->entity, $related, $this->getRelationFk(true, $related, $foreign_key));
	}

	/**
	 * Cree une relation de type 1-n
	 *
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasMany
	 */
	public function hasMany(string $related, ?string $foreign_key = null) : HasMany
	{
		return new Relations\HasMany($this->entity, $related, $this->getRelationFk(true, $related, $foreign_key));
	}

	/**
	 *
	 * @param string $related
	 * @param string|null $foreign_key
	 * @return HasMany
	 */
	public function belongsTo(string $related, ?string $foreign_key = null) : BelongsTo
	{
		return new Relations\BelongsTo($this->entity, $related, $this->getRelationFk(false, $related, $foreign_key));
	}

	/**
	 * Cree une relation de type n-n
	 *
	 * @param string $related
	 * @param string|null $pivot_table
	 * @param string|null $foreign_key
	 * @param string|null $other_key
	 * @return BelongsToMany
	 */
	public function belongsToMany(string $related, ?string $pivot_table = null, ?string $foreign_key = null, ?string $other_key = null) : BelongsToMany
	{
		if (empty($pivot_table))
		{
			$models = [$this->entity->getTable(), $this->makeTableFromClass($related)];
			sort($models);

			$pivot_table = strtolower(implode('_', $models));
		}

		$foreign_key = $this->getRelationFk(true, $related, $foreign_key);
		$other_key   = $this->getRelationFk(false, $related, $other_key);

		$pivot_builder = new QueryBuilder($this->entity, $pivot_table);

		return new Relations\BelongsToMany($this->entity, $related, $pivot_builder, $foreign_key, $other_key);
	}

	/**
	 * Fabrique un nom de table en fonction du nom de la classe d'entité donnée
	 *
	 * @param string $entityname Nom (forme absolue) de la classe d'entité
	 * @return string
	 */
	private function makeTableFromClass(string $entityname) : string
	{
		$entityname = explode('\\', preg_replace('#Entity$#', '', $entityname));
		$entityname = end($entityname);

		$table_name = Str::toSnake($entityname);

		if (!Database::tableExist($table_name))
		{
			$table_name = pluralize($table_name);
			if (!Database::tableExist($table_name))
			{
				$table_name = singularize($table_name);
			}
		}

		return $table_name;
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
		$this->relations[$name] = $relation->relate($this->entity);
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
	public function load(string $related)
	{
		if (!method_exists($this, $related))
		{
			return false;
		}

		$this->setRelation($related, $this->$related());
	}



	// ======================================
	// Utilities Methods
	// ======================================

	/**
	 * Check if a column with a specific value is present in current table
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return boolean
	 */
	final public function exist($key, $value = null) : bool
	{
		$process = false;
		$conditions = [];
		if (is_array($key))
		{
			$conditions = $key;
			$process = true;
		}
		else if (is_string($key) AND !empty($value))
		{
			$conditions = [$key => $value];
			$process = true;
		}
		if ($process)
		{
			return $this->where($conditions)->count() > 0;
		}
		throw new Exception("Mauvaise utilisation de la methode exist(). Consultez la doc pour plus d'informations", 1);
	}

	/**
     * Verifie si une valeur n'existe pas deja pour une cle donnee
     *
     * @param array $dif
     * @param array $eq
     * @return bool
     */
    final public function existOther(array $dif, array $eq) : bool
    {
        foreach ($dif As $key => $value)
        {
            $this->where($key . ' !=', $value);
        }
        foreach ($eq As $key => $value)
        {
            $this->where($key, $value);
        }

        return $this->count() > 0;
    }


	// ======================================
	// Aggregate Methods
	// ======================================

	protected function aggregates(string $function, string $field)
	{
		return call_user_func([$this->builder(), $function], $field);
	}

	/**
     * Gets the max value for a specified field.
     *
     * @param string $field Field name
     * @return mixed
     */
    final public function max(string $field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	/**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * @return mixed
     */
    final public function min(string $field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	/**
     * Gets the average value for a specified field.
     *
     * @param string $field Field name
     * @return mixed
     */
    final public function avg(string $field)
	{
		return round( $this->aggregates(__FUNCTION__, $field), 2);
	}

	/**
     * Gets the sum value for a specified field.
     *
     * @param string $field Field name
     * @return mixed
     */
	final public function sum(string $field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	/**
     * Gets a count of records for a table.
     *
     * @param string $field Field name
     * @return mixed
     */
    final public function count(?string $field = null)
	{
		if (empty($field))
		{
			$field = $this->getPrimaryKey();
		}

		return $this->aggregates(__FUNCTION__, $field);
	}


	/**
	 * Renvoi l'instance de QueryBuilder existant ou créé un nouveau si nécessaire
	 *
	 * @return QueryBuilder
	 */
	protected function builder() : QueryBuilder
	{
		if (!empty($this->queryBuilder))
		{
			return $this->queryBuilder;
		}
		return $this->queryBuilder = new QueryBuilder($this->entity);
	}

	/**
	 * Renvoi la cle primaire de la classe d'entite courrante
	 *
	 * @return string
	 */
	public function getPrimaryKey() : string
	{
		return $this->entity->getPrimaryKey();
	}
	/**
	 * Renvoi le nom de la classe
	 *
	 * @param bool $withNamespace
	 * @return string
	 */
	public function getClassName(bool $withNamespace = true) : string
	{
		$entity = new ReflectionClass($this->entity);
		return $withNamespace === true ? $entity->getName() : $entity->getShortName();
	}

	/**
	 * Load data from properties
	 */
	private function loadData(bool $from_accept = true) : array
	{
		$data = [];
		if (true === $from_accept)
		{
			foreach($this->entity->accepts() As $field)
			{
				$data[$field] = $this->getData($field);
			}

			$pk = $this->getPrimaryKey();
			$data[$pk] = $this->getData($pk);
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
