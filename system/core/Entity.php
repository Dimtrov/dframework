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

namespace dFramework\core;

use dFramework\components\orm\Helper;
use dFramework\components\orm\Model;
use dFramework\components\orm\Relations\Relation;
use dFramework\core\utilities\Chaine;

/**
 * Entity
 *
 * A global Entity system of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1.0
 * @file		/system/core/Entity.php
 */
abstract class Entity
{
    /**
     * @var Model
     */
    private $orm;

	/**
	 * Constructor
	 *
	 * @param array|null $data
	 */
	public function __construct(?array $data = [])
	{
        $this->orm = new Model($this, $data);
    }
    

    protected function all(array $properties = [])
    {
        return $this->orm->all($properties);
    }

    protected function get(array $properties = [])
    {
        return $this->orm->get($properties);
    }

    protected function first(array $properties = [])
    {
        return $this->orm->first($properties);
    }

    protected function find($id) 
    {
        return $this->orm->find($id);
    }

    protected function pulck(string $field)
    {
        return $this->orm->pulck($field);
    }

    public static function create(array $data)
    {
        return Model::create($data);
    }

    protected function update($data)
    {
        return $this->orm->update($data);
    }

    protected function save()
    {
        return $this->orm->save();
    }

    protected function delete()
    {
        return $this->delete();
    }

    protected function paging($page, $per_page = null)
    {
        return $this->orm->paging($page, $per_page);
    }

    protected function for_page($page, $per_page = null)
    {
        return $this->orm->for_page($page, $per_page);
    }

    protected function getPrimaryKey()
	{
		return $this->orm->getPrimaryKey();
	}

	protected function getData($field = null)
	{
        return $this->orm->getData($field);
	}

    protected function setData($field, $value = null)
	{
        return $this->orm->setData($field, $value);
	}

	protected function toArray()
	{
        return $this->orm->toArray();
    }

	protected function json()
	{
        return $this->orm->json();
	}


    // ======================================
	// Relationship Methods
	// ======================================

	protected function hasOne($related, $foreign_key = null)
	{
        return $this->orm->hasOne($related, $foreign_key);
	}

	protected function hasMany($related, $foreign_key = null)
	{
        return $this->orm->hasMany($related, $foreign_key);
    }

	protected function belongsTo($related, $foreign_key = null)
	{
        return $this->orm->belongsTo($related, $foreign_key);
	}

	protected function belongsToMany($related, $pivot_table = null, $foreign_key = null, $other_key = null)
	{
        return $this->orm->belongsToMany($related, $pivot_table, $foreign_key, $other_key);
	}

	protected function setRelation($name, Relation $relation)
	{
        return $this->orm->setRelation($name, $relation);
	}

	protected function getRelation($name)
	{
		return $this->orm->getRelation($name);
	}

	protected function load($related)
	{
        return $this->orm->load($related);
	}


    // ======================================
	// Aggregate Methods
	// ======================================

	protected function aggregates($function, $field)
	{
        return $this->orm->aggregates($function, $field);
	}

	protected function max($field)
	{
		return $this->orm->max($field);
	}

	protected function min($field)
	{
		return $this->orm->min($field);
	}

	protected function avg($field)
	{
		return $this->orm->avg($field);
	}

	protected function sum($field)
	{
		return $this->orm->sum($field);
	}

	protected function count($field = null)
	{
        return $this->orm->count($field);
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

        return $this->orm->__call($name, $arguments);
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




	/**
     * Hydratateur d'objet
     * 
	 * @param array $data
	 */
	public function hydrate(array $data)
	{
        if (!empty($data)) 
        {
			foreach ($data as $key => $value) 
            {
				$key = self::getProperty($key);
				$method = 'set'.ucfirst($key);
                if (method_exists($this, $method)) 
                {
					$this->{$method}($value);
                } 
                else 
                {
					$this->{$key} = $value;
				}
			}
		}
	}



    /**
     * getProperty
     *
     * @param string $fieldName
     * @return string
     */
    public static function getProperty(string $fieldName) : string
    {
        $case = Config::get('data.hydrator.case');
        $case = \strtolower($case);

        if (\in_array($case, ['camel', 'pascal', 'snake', 'ada', 'macro']))
        {
            $case = 'to'.$case;
            return Chaine::{$case}($fieldName);
        }        
        return $fieldName;
    }
}
