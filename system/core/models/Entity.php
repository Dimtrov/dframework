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

namespace dFramework\core\models;

use dFramework\core\db\Database;
use dFramework\core\db\orm\Model;
use dFramework\core\exception\DatabaseException;
use dFramework\core\models\cast\ArrayCast;
use dFramework\core\models\cast\BaseCast;
use dFramework\core\models\cast\BooleanCast;
use dFramework\core\models\cast\CSVCast;
use dFramework\core\models\cast\DatetimeCast;
use dFramework\core\models\cast\FloatCast;
use dFramework\core\models\cast\IntegerCast;
use dFramework\core\models\cast\JsonCast;
use dFramework\core\models\cast\ObjectCast;
use dFramework\core\models\cast\StringCast;
use dFramework\core\models\cast\TimestampCast;
use dFramework\core\models\cast\URICast;
use dFramework\core\utilities\Str;
use ReflectionClass;

/**
 * Entity
 *
 * A global Entity system of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Models
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1.0
 * @file		/system/core/models/Entity.php
 */
abstract class Entity
{
    /**
     * @var Model
     */
    private $orm;

	/**
	 * @var string parametre de connexion a la bd a utiliser
	 */
	protected $group = null;

	/**
	 * @var string Table a utiliser
	 */
	protected $table = null;

	/**
	 * @var array
	 *
     * Contient des copies originales de tous les attributs de la classe afin que nous puissions déterminer
     * ce qui a réellement été modifié et ne pas écrire accidentellement
	 * des valeurs nulles là où nous ne devrions pas.
     */
    private $originals = [];
	/**
	 * @var array colonnes de l'entite
	 */
	protected $columns = [];
	/**
	 * @var array les colonnes à mappées par rapport à la table
     *
     * Example:
     *  $dataMap = [
     *      'class_name' => 'db_name'
     *  ];
     */
	protected $dataMap = [];
	/**
	 * @var string Cle primaire de la table
	 */
	protected $primaryKey = null;

	/**
	 * @var integer Nombre de ligne par page pour la pagination
	 */
	protected $perPage = 25;

	/**
	 * @var array Attributs autorisés
	 */
	protected $accepts = [];
	/**
	 * @var array Attributs rejectés
	 */
	protected $rejects = [];
    /**
     * @var array Attributs exposés
     */
	protected $exposes = '*';

	protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * Array of field names and the type of value to cast them as when
     * they are accessed.
     */
    protected $casts = [];
    /**
     * Custom convert handlers
     *
     * @var array<string, string>
     */
    protected $castHandlers = [];
    /**
     * Default convert handlers
     *
     * @var array<string, string>
     */
    private $defaultCastHandlers = [
        'array'     => ArrayCast::class,
        'bool'      => BooleanCast::class,
        'boolean'   => BooleanCast::class,
        'csv'       => CSVCast::class,
        'datetime'  => DatetimeCast::class,
        'double'    => FloatCast::class,
        'float'     => FloatCast::class,
        'int'       => IntegerCast::class,
        'integer'   => IntegerCast::class,
        'json'      => JsonCast::class,
        'object'    => ObjectCast::class,
        'string'    => StringCast::class,
        'timestamp' => TimestampCast::class,
        'uri'       => URICast::class,
    ];
	/**
     * Holds info whenever properties have to be casted
     *
     * @var bool
     */
    private $_cast = true;


	/**
	 * Constructor
	 *
	 * @param array|null $data
	 */
	public function __construct(array $data = [], bool $strict = true)
	{
		if (true === $strict AND (!$this->isAccepts($data) OR $this->isRejects($data)))
		{
			throw new DatabaseException("Attribut non autorisé trouvé");
		}
		$this->orm = new Model($this, []);
		$this->_assignData($data);
    }

	/**
	 * Recuperes les attributs autorises
	 *
	 * @return array
	 */
	private function _accepts() : array
	{
		return (array) ($this->accepts == '*' ? $this->columns : $this->accepts);
	}
	/**
	 * Verifie si un attributs est autorisé
	 *
	 * @param string|array $attributes
	 * @return boolean
	 */
	private function _isAccepts($attributes) : bool
	{
		$attributes = (array) $attributes;
		$isAccepts = true;
		$accepted = $this->_accepts();

		foreach ($attributes As $key => $value)
		{
			if (!in_array($key, $accepted))
			{
				$isAccepts = false;
				break;
			}
		}
		return $isAccepts;
	}

	/**
	 * Recuperes les attributs  rejetés
	 *
	 * @return array
	 */
	private function _rejects() : array
	{
		return (array) ($this->rejects == '*' ? $this->columns : $this->rejects);
	}
	/**
	 * Verifie si un attributs est rejeté
	 *
	 * @param string|array $attributes
	 * @return boolean
	 */
	private function _isRejects($attributes) : bool
	{
		$attributes = (array) $attributes;
		$isRejects = false;
		$rejected = $this->_rejects();

		foreach ($attributes As $key => $value)
		{
			if (in_array($key, $rejected))
			{
				$isRejects = false;
				break;
			}
		}
		return $isRejects;
	}

    /**
     * Recuperes les attributs  exposés
     *
     * @return array
     */
    private function _exposes() : array
    {
        return (array) ($this->exposes == '*' ? $this->columns : $this->exposes);
    }
    /**
     * Verifie si un attributs est exposé
     *
     * @param string|array $attributes
     * @return boolean
     */
    private function _isExposes($attributes) : bool
    {
        $attributes = (array) $attributes;
        $isExposes = true;
        $exposed = $this->_exposes();

        foreach ($attributes As $key => $value)
        {
            if (!in_array($key, $exposed))
            {
                $isExposes = false;
                break;
            }
        }
        return $isExposes;
    }

	/**
	 * Assigne les donnees a l'entite
	 *
	 * @param array $data
	 * @return void
	 */
	private function _assignData(array $data)
	{
		$columns = $this->_getColumns();
		$attributes = [];

		foreach ($data As $key => $value)
		{
			if (in_array($key, $columns))
			{
				$attributes[$key] = $value;
			}
			else
			{
				$this->{$key} = $value;
			}
		}
		if (!empty($attributes[$this->_getPrimaryKey()]))
		{
			$this->orm->setExist(true);
		}
		$this->originals = $attributes;
		$this->orm->setData($attributes);
	}
	/**
	 * Recupere le groupe de connexion a utiliser
	 *
	 * @return string|null
	 */
	private function _getGroup() : ?string
	{
		return $this->group;
	}

	/**
	 * Retoure le nom de la table de l'entite courrante
	 *
	 * @return string
	 */
	private function _getTable() : string
	{
		if (!empty($this->table))
		{
			return $this->table;
		}
		$table = Str::toSnake(preg_replace('#Entity$#', '', get_called_class()));
		helper('inflector');

		$table = Database::tableExist(plural($table)) ? plural($table) : $table;

		return $this->table = $table;
	}
	/**
	 * Renvoi le nom des colonne de la table d'entite courrante
	 *
	 * @return array
	 */
	private function _getColumns() : array
	{
		if (!empty($this->columns))
		{
			return $this->columns;
		}
		return Database::columnsName($this->_getTable());
	}

	/**
	 * Retourne la cle primaire de la table de l'entite courrante
	 *
	 * @return string
	 */
	private function _getPrimaryKey() : string
	{
		if (!empty($this->primaryKey))
		{
			return $this->primaryKey;
		}
		$table = $this->_getTable();
		$pk = Database::indexes($table, 'PRIMARY');

		return $pk->fields[0] ?? singular('id_'.$table);
	}

	/**
	 * Retourne le nombre d'element a afficher lors d'une pagination
	 *
	 * @return integer
	 */
	private function _getPerPage() : int
	{
		return $this->perPage;
	}

	private function _dataMap() : array
	{
		return (array) $this->dataMap;
	}


	/**
     * Checks a property to see if it has changed since the entity
     * was created. Or, without a parameter, checks if any
     * properties have changed.
     *
     * @param string $key
	 * @return bool
     */
    public function hasChanged(?string $key = null) : bool
    {
		$attributes = $this->orm->getData();

        // If no parameter was given then check all attributes
        if ($key === null)
		{
            return $this->originals !== $attributes;
        }

        // Key doesn't exist in either
        if (!array_key_exists($key, $this->originals) AND !array_key_exists($key, $attributes))
		{
            return false;
        }

        // It's a new element
        if (!array_key_exists($key, $this->originals) AND array_key_exists($key, $attributes))
		{
            return true;
        }

        return $this->originals[$key] !== $attributes[$key];
    }

	/**
     * Converts the given string|timestamp|DateTime|Time instance
     * into the "dFramewore\core\utilities\Date" object.
     *
     * @param mixed $value
     *
     * @throws Exception
     *
     * @return mixed|\dFramewore\core\utilities\Date
     */
    protected function mutateDate($value)
    {
        return DatetimeCast::get($value);
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of $type  (i.e. ?string) to get NULL
     * instead of casting $value if $value === null
     *
     * @param mixed  $value     Attribute value
     * @param string $attribute Attribute name
     * @param string $method    Allowed to "get" and "set"
     *
     * @throws CastException
     *
     * @return mixed
     */
    protected function castAs($value, string $attribute, string $method = 'get')
    {
        if (empty($this->casts[$attribute]))
		{
            return $value;
        }

        $type = $this->casts[$attribute];

        $isNullable = false;

        if (strpos($type, '?') === 0)
		{
            $isNullable = true;

            if ($value === null)
			{
                return null;
            }

            $type = substr($type, 1);
        }

        // In order not to create a separate handler for the
        // json-array type, we transform the required one.
        $type = $type === 'json-array' ? 'json[array]' : $type;

        if (!in_array($method, ['get', 'set'], true))
		{
			throw new \InvalidArgumentException('The "'.$method.'" is invalid cast method, valid methods are: ["get", "set"].');

			/**
			 * @todo
			 *
			 * internationaliser les messages d'exception et regrouper les methodes d'exception par groupe et avec des appels statiques comme sur CodeIgniter
			 */
            // throw CastException::forInvalidMethod($method);
        }

        $params = [];

        // Attempt to retrieve additional parameters if specified
        // type[param, param2,param3]
        if (preg_match('/^(.+)\[(.+)\]$/', $type, $matches))
		{
            $type   = $matches[1];
            $params = array_map('trim', explode(',', $matches[2]));
        }

        if ($isNullable) {
            $params[] = 'nullable';
        }

        $type = trim($type, '[]');

        $handlers = array_merge($this->defaultCastHandlers, $this->castHandlers);

        if (empty($handlers[$type]))
		{
            return $value;
        }

        if (!is_subclass_of($handlers[$type], BaseCast::class))
		{
            throw new \InvalidArgumentException('The "'.$handlers[$type].'" class must inherit the "dFramework\core\models\cast\BaseCast" class.');
        }

        return $handlers[$type]::$method($value, $params);
    }

    /**
     * Support for json_encode()
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Change the value of the private $_cast property
     *
     * @return bool|Entity
     */
    public function cast(?bool $cast = null)
    {
        if ($cast === null)
		{
            return $this->_cast;
        }

        $this->_cast = $cast;

        return $this;
    }

	/**
     * Checks the datamap to see if this column name is being mapped,
     * and returns the mapped name, if any, or the original name.
     *
	 * @param string $key
     * @return mixed|string
     */
    private function mapProperty(string $key)
    {
        if (empty($this->dataMap))
		{
            return $key;
        }
		if (!empty($this->dataMap[$key]))
		{
            return $this->dataMap[$key];
        }
        return $key;
    }



	// ======================================
	// Magic Methods
	// ======================================

	private function execFacade(string $name, array $arguments)
	{
		// Check if the method is available in this model
		if (method_exists($this, $name))
		{
			return call_user_func_array([$this, $name], $arguments);
		}
		if (method_exists($this, '_'.$name))
		{
			return call_user_func_array([$this, '_'.$name], $arguments);
		}
		// Check if the method is a "scope" method
        // Read documentation about scope method
        $scope = "scope" . Str::toPascalCase($name);
		if (method_exists($this, $scope))
		{
			array_unshift($arguments, $this->orm);

			return call_user_func_array([$this, $scope], $arguments);
		}
		return $this->orm->__call($name, $arguments);
	}
	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->execFacade($name, $arguments);
	}
	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		return (new ReflectionClass(get_called_class()))->newInstance()->execFacade($name, $arguments);
	}

	/**
     * Magic method to allow retrieval of protected and private class properties
     * either by their name, or through a `getCamelCasedProperty()` method.
     *
     * Examples:
     *  $p = $this->my_property
     *  $p = $this->getMyProperty()
     *
	 * @param string $field
	 * @return mixed
	 */
	public function __get(string $field)
    {
		$field = $this->mapProperty($field);

		$value = $this->orm->getData($field);
		if (empty($value))
		{
			if (method_exists($this, $field))
			{
				return call_user_func([$this, $field], $value);
			}
		}

		$accessor = 'getAttr'.Str::toPascalCase($field);
		if (method_exists($this, $accessor))
		{
			$value = call_user_func([$this, $accessor], $value);
		}

		// Do we need to mutate this into a date?
        if (in_array($field, $this->dates, true))
		{
            $value = $this->mutateDate($value);
        }
        // Or cast it as something?
        else if ($this->_cast)
		{
            $value = $this->castAs($value, $field, 'get');
        }

		return $value;
	}

	/**
	 * Magic method to all protected/private class properties to be
     * easily set, either through a direct access or a
     * `setCamelCasedProperty()` method.
     *
     * Examples:
     *  $this->my_property = $p;
     *  $this->setMyProperty($p);
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $field, $value)
    {
		$field = $this->mapProperty($field);

		// Check if the field should be mutated into a date
        if (in_array($field, $this->dates, true))
		{
            $value = $this->mutateDate($value);
        }
        $value = $this->castAs($value, $field, 'set');

		// if a setAttr* method exists for this key, use that method to
        // insert this value. should be outside $isNullable check,
        // so maybe wants to do sth with null value automatically
		$mutator = 'setAttr'.Str::toPascalCase($field);
		if (method_exists($this, $mutator))
		{
			$value = call_user_func([$this, $mutator], $value);
		}

		$this->orm->setData($field, $value);
    }

    /**
     * Returns true if a property exists names $key, or a getter method
     * exists named like for __get().
     */
    public function __isset(string $key) : bool
    {
        $key = $this->mapProperty($key);

        $method = 'getAttr' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

        if (method_exists($this, $method))
		{
            return true;
        }

        return isset($this->originals[$key]);
    }
}
