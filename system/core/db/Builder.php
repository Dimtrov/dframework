<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */

namespace dFramework\core\db;

use dFramework\core\exception\DatabaseException;
use PDO;
use ReflectionClass;

/**
 * Builder
 *
 * Query Builder system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @file		/system/core/db/Builder.php
 */
class Builder
{
    protected $table;
    protected $fields = '*';
    protected $where;
    protected $joins;
    protected $order;
    protected $groups;
    protected $having;
    protected $distinct;
    protected $limit;
    protected $offset;
    protected $sql;


    private $query_keys = [];
    private $query_values = [];

    /**
     * @var Query
     */
    protected $query;

    private $crud = 'select';

    protected $query_details = [];
    protected $stats;

    /**
     * @var string
     */
    protected $db_group = 'default';

    protected $db_type;
    protected $class;
    
    /**
         * Class constructor.
         */
    public function __construct() 
    {
        $this->setQuery($this->db_group);
    }

    private function setQuery(string $db_group)
    {
        $this->query = new Query($db_group);

        $this->db_config = $this->query->db_config;
    }
    /**
     * Sets the database connection.
     *
     * @param string $db Database configuration name
     * @throws Exception For connection error
     */
    public function setDb(string $db_group) : self
    {
        $this->setQuery($db_group);

        return $this;
    }
    /**
     * Alias of self::setDb
     *
     * @param string $db_group
     * @return self
     */
    public function use(string $db_group) : self 
    {
        return $this->setDb($db_group);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name))
        {
            return call_user_func([$this->query, $name], $arguments);
        }
        throw new DatabaseException("Unknow method <".$name.">");
        
    }
    
    /**
     * Checks whether the table property has been set.
     */
    public function checkTable() 
    {
        if (!$this->table) 
        {
            throw new DatabaseException('Table is not defined.');
        }
    }

    /**
     * Checks whether the class property has been set.
     */
    public function checkClass() 
    {
        if (!$this->class) 
        {
            throw new DatabaseException('Class is not defined.');
        }
    }

    /**
     * Resets class properties.
     */
    public function reset() 
    {
        $this->crud = 'select';
        $this->table = '';
        $this->where = '';
        $this->fields = '*';
        $this->joins = '';
        $this->order = '';
        $this->groups = '';
        $this->having = '';
        $this->distinct = '';
        $this->limit = '';
        $this->offset = '';
        $this->sql = '';
    }
    
    /*** SQL Builder Methods ***/

    /**
     * Parses a condition statement.
     *
     * @param string $field Database field
     * @param string $value Condition value
     * @param string $join Joining word
     * @param boolean $escape Escape values setting
     * @return string Condition as a string
     * @throws DatabaseException For invalid where condition
     */
    protected function parseCondition($field, $value = null, $join = '', $escape = true) 
    {
        if (is_string($field)) 
        {
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

            if (empty($join)) 
            { 
                $join = ($field[0] == '|') ? ' OR' : ' AND';
            }

            if (is_array($value)) 
            {
                if (strpos($operator, '@') === false) 
                {
                    $condition = ' IN ';
                }
                if (is_array($value)) 
                {
                    $value = '('.implode(',', array_map(array($this, 'quote'), $value)).')';
                }
                else if (is_string($value)) 
                {
                    $value = '('.$value.')';
                }
            }
            else 
            {
                $value = ($escape AND !is_numeric($value)) ? $this->quote($value) : $value;
            }

            return $join.' '.str_replace('|', '', $field).$condition.$value;
        }
        else if (is_array($field)) 
        {
            $str = '';
            foreach ($field as $key => $value) 
            {
                $str .= $this->parseCondition($key, $value, $join, $escape);
                $join = '';
            }
            return $str;
        }
        else 
        {
            throw new DatabaseException('Invalid where condition.');
        }
    }

    /**
     * Sets the table.
     *
     * @param string $table Table name
     * @param boolean $reset Reset class properties
     * @return object Self reference
     */
    public function from($table) : self
    {
        $this->table = $this->db_config['prefix'].$table;

        return $this;
    }

    /**
     * Adds a table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @param string $type Type of join
     * @return object Self reference
     * @throws Exception For invalid join type
     */
    public function join($table, array $fields, $type = 'INNER') : self
    {
        $this->crud = 'select';

        static $joins = [
            'INNER',
            'LEFT',
            'RIGHT',
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
     * Adds a left table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return object Self reference
     */
    public function leftJoin($table, array $fields) : self
    {
        return $this->join($table, $fields, 'LEFT');
    }
    
    /**
     * Adds a right table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return object Self reference
     */
    public function rightJoin($table, array $fields) : self 
    {
        return $this->join($table, $fields, 'RIGHT OUTER');
    }
    
    /**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return object Self reference
     */
    public function where($field, $value = null) : self
    {
        $join = (empty($this->where)) ? 'WHERE' : '';
        $this->where .= $this->parseCondition($field, $value, $join);

        return $this;
    }
    /**
     * Définit une contion pour la sélection des données
     * 
     * @param string $conditions
     * @param Bulder|array|string $param
     * @return object
     */
    protected function whereIn(string $conditions, $param) : self
    {
        if (is_array($param)) 
        {
            $param = implode(',', $param);
        }
        else if ($param instanceof Builder) 
        {
            $param = $param->sql();
        }
        else if (!is_string($param)) 
        {
            throw new DatabaseException("Mauvaise utilisation de la methode ".__CLASS__."::whereIn");
        }
        
        return $this->where($conditions.' IN ('.$param.')');
    }

    /**
     * Adds an ascending sort for a field.
     *
     * @param string $field Field name
     * @return object Self reference
     */ 
    public function sortAsc($field) : self
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string $field Field name
     * @return object Self reference
     */ 
    public function sortDesc($field) : self
    {
        return $this->orderBy($field, 'DESC');        
    }

    /**
     * Adds fields to order by.
     *
     * @param string $field Field name
     * @param string $direction Sort direction
     * @return object Self reference
     */
    public function orderBy($field, $direction = 'ASC') : self
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
            $field .= ' '.$direction;
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
    public function order($field, string $direction = 'ASC') : self 
    {
        return $this->orderBy($field, $direction);
    }

    /**
     * Adds fields to group by.
     *
     * @param string|array $field Field name or array of field names
     * @return object Self reference
     */
    public function groupBy($field) :self 
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
    public function group($field) : self 
    {
        return $this->groupBy($field);
    }

    /**
     * Adds having conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return object Self reference
     */
    public function having($field, $value = null) : self 
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
     * @param int $offset Number of rows to offset
     * @return object Self reference
     */
    public function limit($limit, $offset = null) : self 
    {
        $this->crud = 'select';
        
        if ($limit !== null) 
        {
            $this->limit = 'LIMIT '.$limit;
        }
        if ($offset !== null) {
            $this->offset($offset);
        }

        return $this;
    }
    
    /**
     * Adds an offset to the query.
     *
     * @param int $offset Number of rows to offset
     * @param int $limit Number of rows to limit
     * @return object Self reference
     */
    public function offset($offset, $limit = null) : self 
    {
        $this->crud = 'select';
        
        if ($offset !== null) 
        {
            $this->offset = 'OFFSET '.$offset;
        }
        if ($limit !== null) {
            $this->limit($limit);
        }

        return $this;
    }

    /**
     * Sets the distinct keyword for a query.
     */
    public function distinct(bool $value = true) : self 
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
     */
    public function between($field, $value1, $value2) 
    {
        $this->where(sprintf(
            '%s BETWEEN %s AND %s',
            $field,
            $this->quote($value1),
            $this->quote($value2)
        ));
    }

    /**
     * Builds a select query.
     *
     * @param array|string $fields Array of field names to select
     * @param int $limit Limit condition
     * @param int $offset Offset condition
     * @return object Self reference
     */
    public function select($fields = '*', $limit = null, $offset = null) : self 
    {
        $this->crud = 'select';
        
        $this->fields = (is_array($fields)) ? implode(',', $fields) : $fields;
        $this->limit($limit, $offset);
        
        return $this;
    }
    
    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @return mixed 
     */
    public function insert(array $data, bool $execute = true)  
    {
        $this->crud = 'insert';
        
        $this->checkTable();

        if (empty($data))
        {
            return $this;
        } 
            
        $this->query_keys = array_keys($data);
        $this->query_values = array_values(array_map([$this->query, 'quote'], $data));
        
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
     * @return mixed
     */
    public function update($data, bool $execute = true) 
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
                $values[] = (is_numeric($key)) ? $value : $key.'='.$this->quote($value);
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
     * @param array $where Where conditions
     * @return mixed
     */
    public function delete(?array $where = null, bool $execute = true) 
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

    /**
     * Get the current SQL statement and reset builder.
     *
     * @return string SQL statement
     */
    public function sql() : string
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
                $this->table,
                '('.$keys.')',
                'VALUES',
                '('.$values.')'
            ]);
        }

        if ($this->crud === 'delete')
        {     
            $this->setSql([
                'DELETE FROM',
                $this->table,
                $this->where
            ]);
        }

        if ($this->crud === 'update')
        {    
            $this->setSql([
                'UPDATE',
                $this->table,
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
                $this->fields,
                'FROM',
                $this->table,
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
            is_array($sql) ? array_reduce($sql, [$this->query, 'build']) : $sql
        );
    }

    /**
     * Truncate a table
     *
     * @param string $table
     * @return void
     */
    public function truncate(string $table)
    {
        $table = $this->db_config['prefix'].$table;

        return $this->query->query('TRUNCATE TABLE '.$table)->execute();
    }
    /**
     * Describe a table
     *
     * @param string $table
     * @return void
     */
    public function describe(string $table)
    {
        $table = $this->db_config['prefix'].$table;

        return $this->query->query('DESCRIBE '.$table)->result();
    }
    
    /*** Database Access Methods ***/

    
    public function query(string $sql) : Query
    {
        return $this->query->query($this->makeSql($sql));
    }
    
    /**
     * Executes a sql statement.
     *
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return object Query results object
     * @throws Exception When database is not defined
     */
    public function execute($key = null, $expire = 0) 
    {
        $result = $this->query->query($this->sql())->execute($key, $expire);

        $this->query_details = $this->query->details();
        $this->stats = $this->query->stats();

        return $result;
    }

    /**
     * Fetch multiple rows from a select query.
     *
     * @param int|string $fetch_mode
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return array Rows
     */
    public function all($fetch_mode = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0) : array 
    {
        $data = $this->query->query($this->sql())->result($fetch_mode, $key, $expire);
        
        $this->query_details = $this->query->details();
        $this->stats = $this->query->stats();

        return $data;
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param int|string $fetch_mode 
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed
     */
    public function one($fetch_mode = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0) 
    {
        if (!empty($this->sql)) 
        {
            $this->limit(1);
        }

        return $this->all($fetch_mode, $key, $expire)[0] ?? null;
    }
    /**
     * Recupere le premier resultat d'une requete en BD
     *
     * @alias self::one()
     */
    public function first($fetch_mode = PDO::FETCH_OBJ, ?string $key = null, int $expire = 0)
    {
        return $this->one($fetch_mode, $key, $expire);
    }
    /**
     * Recupere un resultat precis dans les resultat d'une requete en BD
     *
     * @param int|string $fetch_mode
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed Row
     */
    public function row(int $index, $fetch_mode = PDO::FETCH_OBJ, ?string $key, int $expire)
    {
        return $this->all($fetch_mode, $key, $expire)[$index] ?? null;
    }



    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed Row value
     */
    public function value($name, $key = null, $expire = 0) 
    {
        $row = $this->one(PDO::FETCH_OBJ, $key, $expire);

        return $row->{$name} ?? null;
    }

    /**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return mixed
     */
    public function min($field, $key = null, $expire = 0) 
    {
        $this->select('MIN('.$field.') min_value');

        return $this->value(
            'min_value',
            $key,
            $expire
        );
    }

    /**
     * Gets the max value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return mixed
     */
    public function max($field, $key = null, $expire = 0) 
    {
        $this->select('MAX('.$field.') max_value');

        return $this->value(
            'max_value',
            $key,
            $expire
        );
    }

    /**
     * Gets the sum value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return mixed
     */
    public function sum($field, $key = null, $expire = 0) 
    {
        $this->select('SUM('.$field.') sum_value');

        return $this->value(
            'sum_value',
            $key,
            $expire
        );
    }
    
    /**
     * Gets the average value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return mixed
     */
    public function avg($field, $key = null, $expire = 0) 
    {
        $this->select('AVG('.$field.') avg_value');

        return $this->value(
            'avg_value',
            $key,
            $expire
        ); 
    }
    
    /**
     * Gets a count of records for a table.
     *
     * @param string $field Field name
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed
     */
    public function count($field = '*', ?string $key = null, int $expire = 0) 
    {
        $this->select('COUNT('.$field.') num_rows');

        return $this->value(
            'num_rows',
            $key,
            $expire
        );
    }
    
    /**
     * Wraps quotes around a string and escapes the content for a string parameter.
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     */
    public function quote($value) 
    {
        return $this->query->quote($value);
    }

        
    /*** Object Methods ***/

    /**
     * Sets the class.
     *
     * @param string|object $class Class name or instance
     * @return object Self reference
     */
    public function using($class) 
    {
        if (is_string($class)) 
        {
            $this->class = $class;
        }
        else if (is_object($class)) 
        {
            $this->class = get_class($class);
        }
    
        $this->reset();
    
        return $this;
    }
    
    /**
     * Loads properties for an object.
     *
     * @param object $object Class instance
     * @param array $data Property data
     * @return object Populated object
     */
    public function load(object $object, array $data) : object 
    {
        foreach ($data As $key => $value) 
        {
            if (property_exists($object, $key)) 
            {
                $object->{$key} = $value;
            }
        }

        return $object;
    }
       
    /**
     * Finds and populates an object.
     *
     * @param int|string|array Search value
     * @param string $key Cache key
     * @return object|object[] Populated object
     */
    public function find($value = null, $key = null)
    {
        $this->checkClass();

        $properties = $this->getProperties();

        $this->from($properties->table, false);

        if ($value !== null) 
        {
            if (is_int($value) AND property_exists($properties, 'id_field')) 
            {
                $this->where($properties->id_field, $value);
            }
            else if (is_string($value) AND property_exists($properties, 'name_field')) 
            {
                $this->where($properties->name_field, $value);
            }
            else if (is_array($value)) 
            {
                $this->where($value);
            }
        }

        if (empty($this->sql)) {
            $this->select();
        }
    
        $data = $this->all($key);
        $objects = [];
    
        foreach ($data as $row) 
        {
            $objects[] = $this->load(new $this->class, $row);
        }
    
        return (count($objects) == 1) ? $objects[0] : $objects;
    }
    
    /**
     * Saves an object to the database.
     *
     * @param object $object Class instance
     * @param array $fields Select database fields to save
     */
    public function save(object $object, array $fields = null) 
    {
        $this->using($object);

        $properties = $this->getProperties();

        $this->from($properties->table);

        $data = get_object_vars($object);
        $id = $object->{$properties->id_field};

        unset($data[$properties->id_field]);

        if ($id === null) 
        {
            $this->insert($data);

            $object->{$properties->id_field} = $this->insert_id;
        }
        else 
        {
            if ($fields !== null) 
            {
                $keys = array_flip($fields);
                $data = array_intersect_key($data, $keys);
            }

            $this->where($properties->id_field, $id)
                ->update($data);
        }

        return $this->class;
    }

    /**
     * Removes an object from the database.
     *
     * @param object $object Class instance
     */
    public function remove($object) 
    {
        $this->using($object);

        $properties = $this->getProperties();

        $this->from($properties->table);

        $id = $object->{$properties->id_field};

        if ($id !== null) 
        {
            $this->where($properties->id_field, $id)
                ->delete();
        }
    }

    /**
     * Gets class properties.
     *
     * @return object Class properties
     */
    public function getProperties() 
    {
        static $properties = [];

        if (!$this->class) 
        {
            return [];
        }

        if (!isset($properties[$this->class])) 
        {
            static $defaults = array(
                'table' => null,
                'id_field' => null,
                'name_field' => null
            );
            
            $reflection = new ReflectionClass($this->class);
            $config = $reflection->getStaticProperties();

            $properties[$this->class] = (object)array_merge($defaults, $config);
        }

        return $properties[$this->class];
    }
}
