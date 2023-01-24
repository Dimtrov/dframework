<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.4
 */

namespace dFramework\core\db\query;

use BadMethodCallException;
use PDO;
use dFramework\core\db\Database;
use dFramework\core\exception\DatabaseException;
use dFramework\core\utilities\Arr;
use dFramework\core\utilities\Str;
use InvalidArgumentException;

/**
 * Builder
 *
 * Query Builder system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @file		/system/core/db/query/Builder.php
 */
class Builder
{
    protected $table = [];
    protected $fields = [];
    protected $where;
    protected $params = [];
    protected $joins;
    protected $order;
    protected $groups;
    protected $having;
    protected $distinct;
    protected $limit;
    protected $offset;
    protected $sql;

    private $crud = 'select';

    private $query_keys = [];
    private $query_values = [];

    /**
     * @var Result
     */
    protected $result;

    protected $db_type;
    protected $class;

    /**
     * @var Database Instance de la base de donnees courrante
     */
    protected $db;
    /**
     * @var array Parametres de configuration de la base de donnees
     */
    protected $db_config = [];

    /**
     * @var self
     */
    private static $_instance = null;

    /**
     * Constructor
     *
     * @param string $group
     */
    public function __construct(?string $group = null)
    {
		$this->useConnection(empty($group) ? '' : $group, [], true);
    }
    public static function instance(?string $group = null) : self
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self($group);
        }
        return self::$_instance;
    }

	/**
	 * Defini la configuration de la base de donnees a utiliser
	 *
	 * @param string $group
	 * @param array $customConfig
	 * @param boolean $shared
	 * @return void
	 */
	public function useConnection(string $group, array $customConfig = [], bool $shared = false)
	{
		$this->db = true === $shared ? Database::instance($group) : new Database($group, $customConfig);
        $this->db_config = $this->db->config();
	}

    public function __clone()
    {
        $new = $this;
        return $new->reset();
    }

    /*************************** SQL Utilities Methods ********************/

    public function __call($name, $arguments)
    {
        if (in_array($name, Database::allowedFacadeMethods))
        {
            return call_user_func_array([$this->db, $name], $arguments);
        }
		if (Str::startsWith($name, 'where'))
		{
            return $this->dynamicWhere($name, $arguments);
        }
    }

    /*************************** SQL Builder Methods ********************/

    /**
     * Sets the table.
     *
     * @param string|string[] $table Table name
     * @param boolean $reset Reset class properties
     * @return self
     */
    final public function from($tables, bool $reset = false) : self
    {
		if (true === $reset)
		{
			$this->table = [];
		}
        $tables = (array) $tables;
        foreach ($tables As $table)
        {
            $this->table[] = $this->db_config['prefix'].$table;
        }

        return $this;
    }
    /**
     * Sets the table
     *
     * @param string|string[] $tables
     * @alias self::from()
     * @return self
     */
    final public function table($tables) : self
    {
        return $this->from($tables);
    }
    /**
     * Sets the table
     *
     * @param string $table
     * @alias self::from()
     * @return self
     */
    final public function into(string $table) : self
    {
        return $this->from($table);
    }

    /**
     * Adds a table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @param string $type Type of join
     * @return self
     * @throws DatabaseException For invalid join type
     */
    final public function join(string $table, array $fields, string $type = 'INNER') : self
    {
        $this->crud = 'select';

        static $joins = [
            'INNER',
            'LEFT',
            'RIGHT',
			'FULL OUTER',
            'LEFT OUTER',
            'RIGHT OUTER',
        ];
        if (!in_array($type, $joins))
        {
            throw new DatabaseException('Invalid join type.');
        }

        $this->joins .= ' '.$type.' JOIN '.$this->db_config['prefix'].$table.
        $this->parseCondition($fields, null, ' ON', false);

        return $this;
    }

    /**
     * Adds a full table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self
     */
    final public function fullJoin(string $table, array $fields) : self
	{
        return $this->join($table, $fields, 'FULL OUTER');
    }

	/**
     * Adds a inner table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self
     */
    final public function innerJoin(string $table, array $fields) : self
	{
        return $this->join($table, $fields, 'INNER');
    }

	/**
     * Adds a left table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self
     */
    final public function leftJoin(string $table, array $fields) : self
    {
        return $this->join($table, $fields, 'LEFT');
    }

    /**
     * Adds a right table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self
     */
    final public function rightJoin(string $table, array $fields) : self
    {
        return $this->join($table, $fields, 'RIGHT');
    }

    /**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
	 * @param boolean $escape Escape values setting
     * @return self
     */
    final public function where($field, $value = null, bool $escape = true) : self
    {
        $join = empty($this->where) ? 'WHERE' : '';
        $this->where .= $this->parseCondition($field, $value, $join, $escape);

        return $this;
    }
    /**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function notWhere($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where($key . ' !=', $value, $escape);
        }
        return $this;
    }
	/**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function orWhere($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where('|' . $key, $value, $escape);
        }
        return $this;
    }
	/**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function orNotWhere($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where('|' . $key . ' !=', $value, $escape);
        }
        return $this;
    }

    /**
     * Définit une condition pour la sélection des données
     *
     * @param string $field
     * @param self|array|callable|string $param
     * @return self
     */
    final public function in(string $field, $param) : self
    {
        if (is_callable($param))
        {
            $param = call_user_func($param, clone $this);
        }

        if (is_array($param))
        {
			$param = implode(',', array_map([$this->db, 'quote'], $param));
        }
        else if ($param instanceof self)
        {
            $param = $param->sql();
        }
        else if (is_string($param))
        {
            throw new DatabaseException("Mauvaise utilisation de la methode ".__CLASS__."::in");
        }

        return $this->where($field.' IN ('.$param.')');
    }
    /**
     * Retrocompatibilite de la methode in()
     *
     * @alias self::in()
     * @param string $field
     * @param self|array|string $param
     * @return self
     */
    final public function whereIn(string $field, $param) : self
    {
        return $this->in($field, $param);
    }
    /**
     * Définit une condition pour la sélection des données
     *
     * @param string $field
     * @param self|array|callable|string $param
     * @return self
     */
    final public function orIn(string $field, $param) : self
    {
        if (is_callable($param))
        {
            $param = call_user_func($param, clone $this);
        }

        if (is_array($param))
        {
            $param = implode(',', $param);
        }
        else if ($param instanceof self)
        {
            $param = $param->sql();
        }
        else if (!is_string($param))
        {
            throw new DatabaseException("Mauvaise utilisation de la methode ".__CLASS__."::orIn");
        }

        return $this->where('|' . $field . ' IN ('.$param.')');
    }
    /**
     * Définit une condition pour la sélection des données
     *
     * @param string $field
     * @param self|array|callable|string $param
     * @return self
     */
    final public function notIn(string $field, $param) : self
    {
        if (is_callable($param))
        {
            $param = call_user_func($param, clone $this);
        }

        if (is_array($param))
        {
            $param = implode(',', $param);
        }
        else if ($param instanceof self)
        {
            $param = $param->sql();
        }
        else if (!is_string($param))
        {
            throw new DatabaseException("Mauvaise utilisation de la methode ".__CLASS__."::notIn");
        }

        return $this->where($field.' NOT IN ('.$param.')');
    }
    /**
     * Définit une condition pour la sélection des données
     *
     * @param string $field
     * @param self|array|callable|string $param
     * @return self
     */
    final public function orNotIn(string $field, $param) : self
    {
        if (is_callable($param))
        {
            $param = call_user_func($param, clone $this);
        }

        if (is_array($param))
        {
            $param = implode(',', $param);
        }
        else if ($param instanceof self)
        {
            $param = $param->sql();
        }
        else if (!is_string($param))
        {
            throw new DatabaseException("Mauvaise utilisation de la methode ".__CLASS__."::orNotIn");
        }

        return $this->where('|' . $field . ' NOT IN ('.$param.')');
    }

     /**
     * Définit les parametres de la requete en cas d'utilisation de requete preparees classiques
     *
     * @param array $params
     * @return self
     */
    final public function params(array $params) : self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Adds like conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function like($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where($key . ' %', $value, $escape);
        }
        return $this;
    }
    /**
     * Adds not like conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function notLike($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where($key . ' !%', $value, $escape);
        }
        return $this;
    }
    /**
     * Adds or-like conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function orLike($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where('|' . $key . ' %', $value, $escape);
        }
        return $this;
    }
    /**
     * Adds or-not-like conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param mixed $value A field value to compare to
     * @param boolean $escape Escape values setting
     * @return self
     */
    final public function orNotLike($field, $value = null, bool $escape = true) : self
    {
        if (!is_array($field))
        {
            $field = [$field => $value];
        }
        foreach ($field As $key => $value)
        {
            $this->where('|' . $key . ' !%', $value, $escape);
        }
        return $this;
    }

	/**
     * Définit une condition pour la sélection des données
     *
     * @param string|array $field A field name or an array of fields name.
     * @return self
     */
    final public function whereNull($field) : self
    {
		$field = (array) $field;

		foreach ($field As $value)
        {
            $this->where($value . ' IS NULL');
        }
        return $this;
    }
	/**
     * Définit une condition pour la sélection des données
     *
     * @param string|array $field A field name or an array of fields name.
     * @return self
     */
    final public function whereNotNull($field) : self
    {
		$field = (array) $field;

		foreach ($field As $value)
        {
            $this->where($value . ' IS NOT NULL');
        }
        return $this;
    }
	/**
     * Définit une condition pour la sélection des données
     *
     * @param string|array $field A field name or an array of fields name.
     * @return self
     */
    final public function orWhereNull($field) : self
    {
		$field = (array) $field;

		foreach ($field As $value)
        {
            $this->where('|'. $value . ' IS NULL');
        }
        return $this;
    }
	/**
     * Définit une condition pour la sélection des données
     *
     * @param string|array $field A field name or an array of fields name.
     * @return self
     */
    final public function orWhereNotNull($field) : self
    {
		$field = (array) $field;

		foreach ($field As $value)
        {
            $this->where('|'. $value . ' IS NOT NULL');
        }
        return $this;
    }

    /**
     * Adds fields to order by.
     *
     * @param string|array $field Field name
     * @param string $direction Sort direction
     * @return self
     */
    final public function orderBy($field, string $direction = 'ASC') : self
    {
        $this->crud = 'select';

        $join = (empty($this->order)) ? 'ORDER BY' : ',';

        if (is_array($field))
        {
            foreach ($field as $key => $value)
            {
                $field[$key] = $value.' '.$direction;
            }
        }
        else
        {
            if ($field !== 'RAND()')
            {
                $field .= ' '.$direction;
            }
        }

        $fields = (is_array($field)) ? implode(', ', $field) : $field;

        $this->order .= $join.' '.$fields;

        return $this;
    }
    /**
     * Retro-compatibilité avec la version 3.2.2
     *
     * @param string|array $field
     * @param string $direction
     * @alias self::orderBy()
     * @return self
     */
    final public function order($field, string $direction = 'ASC') : self
    {
        return $this->orderBy($field, $direction);
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string|array $field Field name
     * @return self
     */
    final public function sortAsc($field) : self
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string|array $field Field name
     * @return self
     */
    final public function sortDesc($field) : self
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * Adds an random sort for fields.
     *
     * @return object
     */
    final public function rand() : self
    {
        return $this->orderBy('RAND()');
    }

    /**
     * Adds fields to group by.
     *
     * @param string|array $field Field name or array of field names
     * @return self
     */
    final public function groupBy($field) :self
    {
        $this->crud = 'select';

        $join = (empty($this->groups)) ? 'GROUP BY' : ',';
        $fields = (is_array($field)) ? implode(',', $field) : $field;

        $this->groups .= $join.' '.$fields;

        return $this;
    }
    /**
     * Retro-compatibilité avec la version 3.2.2
     *
     * @param string|array $field
     * @alias self::orderBy()
     * @return self
     */
    final public function group($field) : self
    {
        return $this->groupBy($field);
    }

    /**
     * Adds having conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return self
     */
    final public function having($field, $value = null) : self
    {
        $this->crud = 'select';

        $join = (empty($this->having)) ? 'HAVING' : '';
        $this->having .= $this->parseCondition($field, $value, $join);

        return $this;
    }

    /**
     * Adds a limit to the query.
     *
     * @param int $limit Number of rows to limit
     * @param int|null $offset Number of rows to offset
     * @return self
     */
    final public function limit(int $limit, ?int $offset = null) : self
    {
        $this->crud = 'select';

        if ($offset !== null)
		{
			$this->offset($offset);
        }
		$this->limit = 'LIMIT '.$limit;

        return $this;
    }

    /**
     * Adds an offset to the query.
     *
     * @param int $offset Number of rows to offset
     * @param int|null $limit Number of rows to limit
     * @return self
     */
    final public function offset(int $offset, ?int $limit = null) : self
    {
        $this->crud = 'select';

        if ($limit !== null)
		{
			$this->limit($limit);
        }
		$this->offset = 'OFFSET '.$offset;

        return $this;
    }

    /**
     * Sets the distinct keyword for a query.
     *
     * @param boolean $value
     * @return self
     */
    final public function distinct(bool $value = true) : self
    {
        $this->distinct = ($value) ? 'DISTINCT' : '';

        return $this;
    }

    /**
     * Sets a between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     * @return self
     */
    final public function between(string $field, $value1, $value2) : self
    {
        return $this->where(sprintf(
            '%s BETWEEN %s AND %s',
            $field,
            $this->db->quote($value1),
            $this->db->quote($value2)
        ));
    }

    /**
     * Builds a select query.
     *
     * @param array|string $fields Array of field names to select
     * @param int|null $limit Limit condition
     * @param int|null $offset Offset condition
     * @return self
     */
    final public function select($fields = '*', ?int $limit = null, ?int $offset = null) : self
    {
        $this->crud = 'select';

        $this->fields[] = is_array($fields) ? implode(',', $fields) : $fields;
		if ($limit !== null)
		{
			$this->limit($limit, $offset);
		}

        return $this;
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
	 * @param bool $execute Specified if we want to directly execute the query
     * @return Result|self
     */
    final public function insert(array $data, bool $execute = true)
    {
        $this->crud = 'insert';

        $this->checkTable();

        if (empty($data))
        {
            return $this;
        }

        $this->query_keys = array_keys($data);
        $this->query_values = array_values(array_map([$this->db, 'quote'], $data));

        if (true === $execute)
        {
            return $this->execute();
        }

        return $this;
    }

    /**
     * Builds an update query.
     *
     * @param string|array $data Array of keys and values, or string literal
     * @param bool $execute Specified if we want to directly execute the query
     * @return Result|self
     */
    final public function update($data, bool $execute = true)
    {
        $this->crud = 'update';

        $this->checkTable();

        if (empty($data))
        {
            return $this;
        }
        $values = [];

        if (is_array($data))
        {
            foreach ($data As $key => $value)
            {
                $values[] = (is_numeric($key)) ? $value : $key.'='.$this->db->quote($value);
            }
        }
        else
        {
            $values[] = (string)$data;
        }
        $this->query_values = $values;

        if (true === $execute)
        {
            return $this->execute();
        }

        return $this;
    }

    /**
     * Builds a delete query.
     *
     * @param bool $execute Specified if we want to directly execute the query
     * @param array $where Where conditions
     * @return Result|self
     */
    final public function delete(bool $execute = true, ?array $where = null)
    {
        $this->crud = 'delete';

        if ($where !== null)
        {
            $this->where($where);
        }

        if (true === $execute)
        {
            return $this->execute();
        }

        return $this;
    }


    /*************************** SQL Aggregate Methods ********************/


    /**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return float
     */
    final public function min(string $field, ?string $key = null, int $expire = 0) : float
    {
        $this->select('MIN('.$field.') min_value');

        return $this->value(
            'min_value',
            $key,
            $expire
        ) ?? 0;
    }

    /**
     * Gets the max value for a specified field.
     *
     * @param string $field Field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return float
     */
    final public function max(string $field, ?string $key = null, int $expire = 0) : float
    {
        $this->select('MAX('.$field.') max_value');

        return $this->value(
            'max_value',
            $key,
            $expire
        ) ?? 0;
    }

    /**
     * Gets the sum value for a specified field.
     *
     * @param string $field Field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return float
     */
    final public function sum(string $field, ?string $key = null, int $expire = 0) : float
    {
        $this->select('SUM('.$field.') sum_value');

        return $this->value(
            'sum_value',
            $key,
            $expire
        ) ?? 0;
    }

    /**
     * Gets the average value for a specified field.
     *
     * @param string $field Field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return float
     */
    final public function avg(string $field, ?string $key = null, int $expire = 0) : float
    {
        $this->select('AVG('.$field.') avg_value');

        return $this->value(
            'avg_value',
            $key,
            $expire
        ) ?? 0;
    }

    /**
     * Gets a count of records for a table.
     *
     * @param string $field Field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return int
     */
    final public function count(string $field = '*', ?string $key = null, int $expire = 0) : int
    {
        $this->select('COUNT('.$field.') num_rows');

        return $this->value(
            'num_rows',
            $key,
            $expire
        ) ?? 0;
    }


    /*************************** Fetch Data Methods ********************/

    /**
     * Execute une requete sql donnée
     *
     * @param string $sql
     * @param array $params
     * @return Result
     */
    final public function query(string $sql, array $params = [])
    {
        return Database::query($sql, $params);
    }

    /**
     * Executes a sql statement.
     *
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return Result
     */
    final public function execute(?string $key = null, int $expire = 0)
    {
        return $this->result = $this->query($this->sql(), $this->params);
    }

    /**
     * Fetch multiple rows from a select query.
     *
     * @param int|string $type
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return array Rows
     */
    final public function all($type = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0) : array
    {
        $this->execute($key, $expire);
        return $this->result->all($type);
    }
    /**
     * @alias self::all()
     * @param int|string $type
     * @param string|null $key
     * @param integer $expire
     * @return array
     */
    final public function result($type = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0) : array
    {
        return $this->all($type, $key, $expire);
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param int|string $type
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed
     */
    final public function one($type = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0)
    {
		$this->limit(1);
        $this->execute($key, $expire);

        return $this->result->first($type);
    }
    /**
     * Recupere le premier resultat d'une requete en BD
     *
     * @alias self::one()
     * @param int|string $type
     * @param string|null $key
     * @param int $expire
     * @return mixed
     */
    final public function first($type = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0)
    {
        return $this->one($type, $key, $expire);
    }

    /**
     * Recupere un resultat precis dans les resultat d'une requete en BD
     *
     * @param int|string $type
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed Row
     */
    final public function row(int $index, $type = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0)
    {
        $this->execute($key, $expire);
        return $this->result->row($index, $type);
    }

    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * @param string|null $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed Row value
     */
    final public function value(string $name, ?string $key = null, int $expire = 0)
    {
        $row = $this->one(PDO::FETCH_OBJ, $key, $expire);

        return $row->{$name} ?? null;
    }


	/*************************** Advanced finders methods ********************/


	/**
	 * Find all elements in database
	 *
	 * @param array|string $fields Array of field names to select
	 * @param array $options Array of selecting options
	 * 					- @var int limit
	 * 					- @var int offset
	 * 					- @var array where
	 * @param int|string $type
	 * @return array
	 */
	final public function findAll($fields = '*', array $options = [], $type = PDO::FETCH_OBJ) : array
	{
		$this->select($fields);
		if (isset($options['limit']))
		{
			$this->limit($options['limit']);
		}
		if (isset($options['offset']))
		{
			$this->offset($options['offset']);
		}
		if (isset($options['where']) AND is_array($options['where']))
		{
			$this->where($options['where']);
		}

		return $this->all($type);
	}

	/**
	 * Find one element in database
	 *
	 * @param array|string $fields Array of field names to select
	 * @param array $options Array of selecting options
	 * 					- @var int offset
	 * 					- @var array where
	 * @param int|string $type
	 * @return mixed
	 */
	final public function findOne($fields = '*', array $options = [], $type = PDO::FETCH_OBJ)
	{
		$this->select($fields);
		if (isset($options['offset']))
		{
			$this->offset($options['offset']);
		}
		if (isset($options['where']) AND is_array($options['where']))
		{
			$this->where($options['where']);
		}

		return $this->one($type);
	}

	/**
     * Handles dynamic "where" clauses to the query.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return self
     */
    public function dynamicWhere(string $method, array $parameters) : self
    {
        $finder = substr($method, 5);

        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments As $segment)
		{
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment !== 'And' AND $segment !== 'Or')
			{
                $this->addDynamic($segment, $connector, $parameters, $index);

                $index++;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else
			{
                $connector = $segment;
            }
        }

        return $this;
    }

	 /**
     * Builds an multi insert query.
     *
     * @param array $data Array of key and values to insert
     * @param string|null $table Table to insert data
     * @return array
     */
    final public function bulckInsert(array $data, ?string $table = null) : array
    {
		if (2 !== Arr::maxDimensions($data))
		{
			throw new BadMethodCallException("Mauvaise utilisation de la méthode " . __METHOD__);
		}

		if (empty($table))
		{
			$table = $this->table;
		}
		$table = (array) $table;
		$table = array_pop($table);
		if (empty($table) OR !is_string($table))
		{
			throw new InvalidArgumentException("Aucune table d'insertion trouvée");
		}

		$insered = [];
		foreach ($data As $item)
		{
			if (is_array($item))
			{
				$result = $this->into($table)->insert($item, true);
				if ($result instanceof Result)
				{
					$insert_id = $result->insertID();
					if (!empty($insert_id))
					{
						$insered[] = $insert_id;
					}
				}
			}
		}

		return $insered;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     *
     * @param  string  $segment
     * @param  string  $connector
     * @param  array   $parameters
     * @param  int     $index
     * @return void
     */
    protected function addDynamic(string $segment, string $connector, array $parameters, int $index)
    {
		$field = Str::toSnake($segment);

        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.

		if ('or' === strtolower($connector))
		{
			$this->orWhere($field, $parameters[$index]);
		}
        else
		{
			$this->where($field, $parameters[$index]);
		}
    }

    /*************************** SQL Statement Generator Methods ********************/


    /**
     * Get the current SQL statement and reset builder.
     *
     * @return string SQL statement
     */
    final public function sql() : string
    {
        $sql = $this->statement()->sql;
        $this->reset();

        return $sql;
    }

    /**
     * Create a sql statement for query
     *
     * @return self
     */
    private function statement() : self
    {
        $this->checkTable();

        if ($this->crud === 'insert')
        {
            $keys = implode(',', $this->query_keys);
            $values = implode(',', $this->query_values);

            $this->setSql([
                'INSERT INTO',
                $this->table[0],
                '('.$keys.')',
                'VALUES',
                '('.$values.')'
            ]);
        }

        if ($this->crud === 'delete')
        {
            $this->setSql([
                'DELETE FROM',
                $this->table[0],
                $this->where,
				$this->order,
                $this->limit,
                $this->offset
            ]);
        }

        if ($this->crud === 'update')
        {
            $this->setSql([
                'UPDATE',
                $this->table[0],
                'SET',
                implode(',', $this->query_values),
                $this->where
            ]);
        }

        if ($this->crud === 'select')
        {
            $this->setSql([
                'SELECT',
                $this->distinct,
                implode(', ', !empty($this->fields) ? $this->fields : ['*']),
                'FROM',
                implode(',', $this->table),
                $this->joins,
                $this->where,
                $this->groups,
                $this->having,
                $this->order,
                $this->limit,
                $this->offset
            ]);
        }

        return $this;
    }
    /**
     * Define statement
     *
     * @param string|array $sql
     * @return void
     */
    private function setSql($sql)
    {
        $this->sql = $this->makeSql($sql);
    }
    private function makeSql($sql) : string
    {
        return trim(
            is_array($sql) ? array_reduce($sql, [$this, 'build']) : $sql
        );
    }

    /**
     * Joins string tokens into a SQL statement.
     *
     * @param string $sql SQL statement
     * @param string $input Input string to append
     * @return string New SQL statement
     */
    private function build(?string $sql, ?string $input) : string
    {
        return (strlen($input ?? '') > 0) ? ($sql.' '.$input) : $sql;
    }


    /*************************** SQL Statement Generator Methods ********************/


    /**
     * Parses a condition statement.
     *
     * @param string|string[] $field Database field
     * @param string $value Condition value
     * @param string $join Joining word
     * @param boolean $escape Escape values setting
     * @return string Condition as a string
     * @throws DatabaseException For invalid where condition
     */
    final protected function parseCondition($field, $value = null, $join = '', $escape = true)
    {
        if (is_string($field))
        {
			if (empty($join))
            {
                $join = ($field[0] == '|') ? ' OR ' : ' AND ';
            }
			$field = str_replace('|', '', $field);

            if ($value === null)
            {
				return $join.' '.trim($field);
            }
            $operator = '';

            if (strpos($field, ' ') !== false)
            {
                list($field, $operator) = explode(' ', $field);
            }

            if (!empty($operator))
            {
                switch ($operator)
                {
                    case '%':
                        $condition = ' LIKE ';
                        break;

                    case '!%':
                        $condition = ' NOT LIKE ';
                        break;

                    case '@':
                        $condition = ' IN ';
                        break;

                    case '!@':
                        $condition = ' NOT IN ';
                        break;

                    default:
                        $condition = $operator;
                }
            }
            else
            {
                $condition = '=';
            }

           	if (is_array($value))
            {
                if (strpos($operator, '@') === false)
                {
                    $condition = ' IN ';
                }
                if (is_array($value))
                {
                    $value = '('.implode(',', array_map([$this->db, 'quote'], $value)).')';
                }
                else if (is_string($value))
                {
                    $value = '('.$value.')';
                }
            }
            else
            {
                $value = ($escape AND !is_numeric($value)) ? $this->db->quote($value) : $value;
            }

            return $join.' '.$field.$condition.$value;
        }
        else if (is_array($field))
        {
            $str = '';
            foreach ($field as $key => $value)
            {
                if (!empty($value)) {
                    $str .= $this->parseCondition($key, $value, $join, $escape);
                    $join = '';
                }
            }
            return $str;
        }
        else
        {
            throw new DatabaseException('Invalid where condition.');
        }
    }

    /**
     * Resets class properties.
     */
    final public function reset() : self
    {
        $this->crud = 'select';
        $this->table = [];
        $this->params = [];
        $this->where = '';
        $this->fields = [];
        $this->joins = '';
        $this->order = '';
        $this->groups = '';
        $this->having = '';
        $this->distinct = '';
        $this->limit = '';
        $this->offset = '';
        $this->sql = '';

        return $this;
    }

    /**
     * Checks whether the table property has been set.
     */
    final protected function checkTable()
    {
        if (empty($this->table))
        {
            throw new DatabaseException('Table is not defined.');
        }
    }
    /**
     * Checks whether the class property has been set.
     */
    final protected function checkClass()
    {
        if (!$this->class)
        {
            throw new DatabaseException('Class is not defined.');
        }
    }
}
