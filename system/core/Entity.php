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

namespace dFramework\core;

use dFramework\core\db\Database;
use dFramework\core\db\orm\Model;
use dFramework\core\exception\DatabaseException;
use dFramework\core\utilities\Str;
use ReflectionClass;

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
	 * @var string parametre de connexion a la bd a utiliser
	 */
	protected $group = null;

	/**
	 * @var string Table a utiliser
	 */
	protected $table = null;
	/**
	 * @var array colonnes de l'entite
	 */
	protected $columns = [];
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
		$this->orm = $this->_assignData($data);
    }

	/**
	 * Recuperes les attributs autorises
	 *
	 * @return array
	 */
	private function _accepts() : array 
	{
		return $this->accepts;
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

		foreach ($attributes As $key => $value) 
		{
			if (!in_array($key, $this->accepts))
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
		return $this->rejects;
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

		foreach ($attributes As $key => $value) 
		{
			if (in_array($key, $this->rejects))
			{
				$isRejects = false;
				break;
			}
		}
		return $isRejects;
	}
	
	/**
	 * Assigne les donnees a l'entite
	 *
	 * @param array $data
	 * @return Model
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
        return new Model($this, $attributes);
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
	 * @param string $field
	 * @return mixed
	 */
	public function __get(string $field)
    {
		$value = $this->orm->getData($field);
		$accessor = 'getAttr'.Str::toCamel($field);

		return method_exists($this, $accessor) ? call_user_func([$this, $accessor], $value) : $value;
	}
	/**
	 * @param string $field
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $field, $value)
    {
		$mutator = 'setAttr'.Str::toCamel($field);

		if (method_exists($this, $mutator))
		{
			$value = call_user_func([$this, $mutator], $value);
		}
		$this->orm->setData($field, $value);
    }
}
