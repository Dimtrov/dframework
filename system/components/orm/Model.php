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

use dFramework\components\orm\QueryBuilder;
use dFramework\components\orm\Result;
use dFramework\components\orm\Helper;
use dFramework\components\orm\DefinitionReader;
use dFramework\core\Entity;
use dFramework\core\exception\Errors;
use dFramework\core\loader\Load;
use ReflectionClass;

/**
 * Model
 *
 * A database access layer for system orm
 *
 * @package		dFramework
 * @subpackage	Components
 * @category 	Orm
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit		rabbit-orm <https://github.com/fabiocmazzo/rabbit-orm>
 * @file		/system/components/orm/Model.php
 */
class Model 
{
	protected $db_group = "default";
	protected $table = "";

	protected $primary;
	protected $incrementing = true;

	protected $per_page = 20;

	private $queryBuilder = null;
	private $pagingBuilder = null;

	public $exists = false;

	protected $data = array();

	// To stored loaded relation
	protected $relations = array();

	/**
	 * All properties definitions in model class.
	 */
	public $propertiesDefinition = array();

	public $classDefinition = array();

	public $class;

	public function __construct($class, array $newData = [])
	{
		$this->class = $class;

		if (is_array($newData)) 
		{ 
			$this->setData( $newData ); 
		}

		$reflectionClass = new ReflectionClass($this->class);

		$definitionReader = new DefinitionReader($reflectionClass);

		$propertiesDefinitionsArray = [];
		
		foreach ($reflectionClass->getProperties() As $reflectionProperty) 
		{
			$definitionsObject = $definitionReader->getPropertyDefinition($reflectionProperty);

			if (is_object($definitionsObject)) 
			{
				$propertiesDefinitionsArray[$definitionsObject->name] = $definitionsObject->column;
				
				if (isset($definitionsObject->primaryKey) AND $definitionsObject->primaryKey == 'true') 
				{
					$this->primary = $definitionsObject->column;
				}
			}
		}

		$this->propertiesDefinition = $propertiesDefinitionsArray;

		$this->table = $definitionReader->getTableDefinition();

		// Reset variable
		$this->exists = false;
		$this->queryBuilder = null;
	}


	private function translateDataToDatabase(array $data) : array
	{
		$translatedData = [];
	
		foreach($data As $key => $value) 
		{
			$translatedData[$this->propertiesDefinition[$key]] = $value;
		}
		
		return $translatedData;
	}

	protected function getColumns(array $properties = []) : array 
	{
		$columns = [];

		// if is not empty, return only properties in array
		if (!empty($properties)) 
		{
			foreach ($properties As $property) 
			{
				$columns[$property] = $this->propertiesDefinition[$property];
			}
			
			return $columns;
		}

		foreach ($this->propertiesDefinition As $key => $value) 
		{
			$columns[$key] = $value;
		}

		return $columns;
	}

	protected function newQuery() : QueryBuilder
	{
		return new QueryBuilder($this->db_group, $this->table);
	}

	protected function query() : self
	{
		$this->newQuery();

		return $this;
	}

	public function all(array $properties = []) 
	{
		$builder = $this->newQuery();
		$columns = $this->getColumns($properties);
		
		if (!empty($columns)) 
		{
			$builder->select($columns);
		}

		$result = new Result( $this, $builder );

		return $result->rows();
	}

	public function get(array $properties = []) 
	{
		$columns = $this->getColumns($properties);
		if (is_null( $this->queryBuilder )) 
		{
			return $this->all($columns);
		}

		if (!empty($columns)) 
		{
			$this->queryBuilder->select($columns);
		}

		$this->pagingBuilder = clone $this->queryBuilder;

		$result = new Result( $this, $this->queryBuilder );
		
		return $result->rows();
	}

	public function first(array $properties = [])
	{
		$columns = $this->getColumns($properties);

		$builder = $this->queryBuilder ?: $this->newQuery();

		if (!empty($columns)) 
		{
			$builder->select($columns);
		}

		$result = new Result( $this, $builder );

		return $result->first();
	}

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

		$builder = $this->newQuery();

		if (is_array($id)) 
		{
			$builder->where($this->primary.' @', $id);
		} 
		else 
		{
			$builder->where($this->primary, $id);
		}

		$result = new Result( $this, $builder);
		
		return is_array($id) 
			? $result->rows() 
			: $result->first();
	}

	protected function pluck(string $field)
	{
		$row = $this->first([$field]);
		
		if (in_array($field, $this->propertiesDefinition))
		{
			$reflectionClass = new ReflectionClass($row);
			$reflectionProperty = $reflectionClass->getProperty(array_search($field, $this->propertiesDefinition));
			$reflectionProperty->setAccessible(true);

			return $reflectionProperty->getValue($row);
		}
		else 
		{
			return $row->$field;
		}
	}

	public static function create(array $data)
	{
		if (empty($data)) 
		{
			return false;
		}

		$class = new static($data);
		$class->save();

		return $class;
	}

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

			return $this->newQuery()->update($data, $where);
		}
		else if (is_object($this->queryBuilder))
		{
			return $this->queryBuilder->update($data);
		}
	}

	public function save()
	{
		$this->loadData();

		if (empty($this->data)) 
		{
			return false;
		}

		$builder = $this->newQuery();

		// Do an insert statement
		if (!$this->exists)
		{
			if ( !$this->incrementing AND empty($this->data[$this->primary])) 
			{
				return false;
			}

			$return = $builder->insert($this->translateDataToDatabase($this->data));

			if ($return !== false)
			{
				$this->exists = true;

				if ($this->incrementing ) 
				{
					$this->setData($this->primary, $builder->lastId());
				}
			}

			return $return;
		}
		else
		{
			$where = [$this->propertiesDefinition[$this->primary] => $this->getData($this->primary)];

			return $builder->update($this->translateDataToDatabase($this->getData()), $where);
		}
	}

	public function delete()
	{
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

			$builder = $this->newQuery();

			if (count($where) <= 1) 
			{
				$builder->where($this->primary, reset($where));
			} 
			else 
			{
				$builder->where_in($this->primary, $where);
			}

			return $builder->delete();
		}

		if ($this->exists) 
		{
			$this->where($this->primary, $this->getData( $this->primary ));
		}

		if (is_object($this->queryBuilder))
		{
			$this->queryBuilder->delete();
		}
	}

	public function paging($page, $per_page = null)
	{
		$page = intval($page);
		if (empty($page) OR $page < 0) 
		{
			$page = 1;
		}
		if (empty($per_page)) 
		{
			$per_page = $this->per_page;
		}
		$offset = ($page - 1) * $per_page;

		if (!$this->queryBuilder)
		{
			$this->queryBuilder = $this->newQuery();
		}

		$this->queryBuilder->limit($per_page, $offset);
	}

	public function for_page($page, $per_page = null)
	{
		$this->paging($page, $per_page);

		return $this->get();
	}

	protected function paginate($per_page = 20, $uri_key = 'page', $link_suffix = '')
	{
		$per_page = intval($per_page);
		
		if ($per_page <= 0) 
		{
			$per_page = 20;
		}

		/*
		$uri_segment = null;
		$uri_array = Uri::instance()->segment_array();

		foreach ($uri_array as $i => $segment_name)
		{
			if ($uri_key == $segment_name)
			{
				$uri_segment = $i;
				break;
			}
		}

		$is_odd = (!empty($uri_segment) and $uri_segment % 2 == 0);

		$uri = Uri::instance()->uri_to_assoc( (!$is_odd ? 1 : 2) );
		unset($uri[$uri_key]);

		if (count($uri) == 1 AND reset($uri) == false)
		{
			$key = reset( array_keys($uri) );
			$uri[ $key ] = 'index';
		}
		$base_url = Uri::instance()->assoc_to_uri($uri).'/'.$uri_key;
		if ($is_odd) 
		{
			$base_url = Uri::instance()->segment(1) . '/' . $base_url;
		}
		*/
		
		$builder = $this->pagingBuilder ?: ($this->queryBuilder ?: $this->newQuery());
		$builder->offset(false);
		
		//$config['base_url'] = site_url( $base_url );
		$config['limit'] = $per_page;
		$config['max_item'] = $builder->count();
		$config['run_query'] = false;
		
		$paginator = Load::library('Paginator');
		$paginator->init($config);

		return $paginator->pagine();
	}

	public function getPrimaryKey()
	{
		return $this->primary;
	}

	public function getData($field = null)
	{
		return !empty($field) ? $this->data[ $field ] : $this->data;
	}

	/**
	 * Load data from properties
	 */
	private function loadData() 
	{
		foreach ($this->propertiesDefinition As $key => $value) 
		{
			$reflectionClass = new ReflectionClass(get_class($this));
			$reflectionProperty = $reflectionClass->getProperty($key);
			$reflectionProperty->setAccessible(true);
			$this->data[$key] = $reflectionProperty->getValue($this);
		}
	}

	public function setData($field, $value = null)
	{
		$reflectionClass = new ReflectionClass($this->class);	
			
		if (is_array($field))
		{
			foreach ($field As $key => $value) 
			{
				$this->setData($key, $value);
			}
		}
		else 
		{
			$field = Entity::getProperty($field);

			if (array_key_exists($field, $reflectionClass->getConstant('properties'))) 
			{
				$this->data[$field] = $value;

				$reflectionProperty = $reflectionClass->getProperty($field);
				$reflectionProperty->setAccessible(true);
				$reflectionProperty->setValue($this->class, $value);
			}
		}
		
		return clone $this->class;
	}

	public function toArray() : array
	{
		$array = $this->data;

		foreach ($this->relations As $relation => $models)
		{
			foreach ($models as $model)
			{
				$array[ $relation ][] = $model->toArray();
			}
		}

		return $array;
	}

	public function json() : string
	{
		return json_encode( $this->toArray() );
	}

	// ======================================
	// Relationship Methods
	// ======================================

	public function hasOne($related, $foreign_key = null)
	{
		if (empty($foreign_key))
		{
			$foreign_key = strtolower(get_called_class()) . '_id';
		}

		return new Relations\HasOne($this, new $related, $foreign_key);
	}

	public function hasMany($related, $foreign_key = null)
	{
		if (empty($foreign_key))
		{
			$foreign_key = strtolower(get_called_class()) . '_id';
		}

		return new Relations\HasMany($this, new $related, $foreign_key);
	}

	public function belongsTo($related, $foreign_key = null)
	{
		if(empty($foreign_key))
			$foreign_key = strtolower($related) . '_id';

		return new Relations\BelongsTo($this, new $related, $foreign_key);
	}

	function belongsToMany($related, $pivot_table = null, $foreign_key = null, $other_key = null)
	{
		if(empty($pivot_table))
		{
			$models = array( strtolower( get_called_class() ), strtolower( $related ) );
			sort($models);

			$pivot_table = strtolower( implode('_', $models) );
		}

		if(empty($foreign_key))
			$foreign_key = strtolower(get_called_class()) . '_id';

		if(empty($other_key))
			$other_key = strtolower($related) . '_id';

		$pivot_builder = new QueryBuilder($this->db_group, $pivot_table);

		return new Relations\BelongsToMany($this, new $related, $pivot_builder, $foreign_key, $other_key);
	}

	function setRelation($name, Relations\Relation $relation)
	{
		$this->relations[ $name ] = $relation->relate( $this );
	}

	function getRelation($name)
	{
		return isset( $this->relations[ $name ] ) ? $this->relations[ $name ] : null;
	}

	// Eager loading for a single row? Just call the method
	function load($related)
	{
		if(!method_exists($this, $related)) return false;

		$this->setRelation( $related, $this->$related() );
	}

	// ======================================
	// Aggregate Methods
	// ======================================

	protected function aggregates($function, $field)
	{
		if (empty($this->queryBuilder))
		{
			$this->queryBuilder = $this->newQuery();
		}

		return call_user_func([$this->queryBuilder, $function], $field);
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

	// ======================================
	// Magic Methods
	// ======================================

	public function __call($name, $arguments)
	{
		// Check if the method is available in this model
		if(method_exists($this, $name))
			return call_user_func_array( array($this, $name), $arguments );

		// Check if the method is a "scope" method
		// Read documentation about scope method
		$scope = "scope" . Helper::studlyCase($name);

		if(method_exists($this, $scope))
		{
			array_unshift($arguments, $this);

			return call_user_func_array( array($this, $scope), $arguments );
		}

		if(is_null( $this->queryBuilder )) $this->queryBuilder = $this->newQuery();

		if(is_callable( array($this->queryBuilder, $name) ))
		{
			call_user_func_array( array($this->queryBuilder, $name), $arguments );
			return $this;
		}

		Errors::show_error('Unknown function '.$name, 'Unknow function');
	}

	public static function __callStatic($name, $arguments)
	{
		$model = get_called_class();

		return call_user_func_array( array(new $model, $name), $arguments );
	}

	function __get($field)
    {
    	if(!isset( $this->data[ $field ] )) $value = '';
		else
 		$value = $this->data[ $field ];

		$accessor = "getAttr". Helper::camelCase( $field );

		return method_exists($this, $accessor) ? call_user_func(array($this, $accessor), $value, $this) : $value;
	}

    function __set($field, $value)
    {
		$mutator = "setAttr". Helper::camelCase( $field );

		if( method_exists($this, $mutator) )
			$value = call_user_func(array($this, $mutator), $value, $this);

		$this->setData( $field, $value );
    }

	function __isset($field)
	{
		return !empty($this->data[ $field ]);
	}
}