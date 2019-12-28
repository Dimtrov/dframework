<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Query
 *
 * Query Builder system
 *
 * @class       Query
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/database
 * @file		/system/core/db/Query.php
 */

namespace dFramework\core\db;

use dFramework\core\Config;
use PDO;

class Query
{
    /**
     * @var Database
     */
    public $db;

    private $execute = [];

    private $crud = 'select';

    private $query;

    private $fields = [];
    private $conditions = [];
    private  $params = [];
    private $table = [];
    private $group = [];
    private $limit;
    private $order = [];
    private $joins = [];



    public function __construct($db_setting = 'default')
    {
        $this->use($db_setting);
    }


    /**
     * @param string $db_setting
     * @return Database
     * @throws \dFramework\core\exception\DatabaseException
     */
    public function use(string $db_setting) : Database
    {
        return $this->db = new Database($db_setting);
    }


    /**
     * @return Query
     */
    protected function free_db() : self
    {
        $this->table = [];
        $this->fields = [];
        $this->conditions = [];
        $this->params = [];
        $this->order = [];
        $this->joins = [];
        $this->limit = null;
        $this->crud = 'select';
        return $this;
    }


    /**
     * @param string ...$fields
     * @return Query
     */
    protected function select(string ...$fields) : self
    {
        if(empty($fields)) {
            $fields = ['*'];
        }
        $this->fields = array_merge($this->fields, $fields);
        $this->crud = 'select';
        return $this;
    }

    /**
     * @param string $column
     * @param string $alias
     * @return int
     */
    protected function count(string $column = '*', string $alias = 'count'): int
    {
        $query = clone $this;
        $nbr = $query->select('COUNT(' . $column . ') As ' . $alias)->run()->fetchColumn();
        $query->free_db();
        unset($query);
        return $nbr;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Query
     */
    protected function limit(int $limit, int $offset = 0): self
    {
        $this->limit = "$offset, $limit";
        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return Query
     */
    protected function order(string $field, string $direction = 'ASC'): self
    {
        $field = explode(' ', $field);
        $direction = $field[1] ?? $direction;
        $field = $field[0];
        $direction = (!in_array(strtoupper($direction), ['ASC', 'DESC'])) ? 'ASC' : strtoupper($direction);
        $this->order[] = "$field $direction";
        return $this;
    }

    /**
     * @param string $field
     * @return Query
     */
    protected function group(string $field) : self
    {
        $this->group[] = $field;
        return $this;
    }


    /**
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return Query
     */
    protected function join(string $table, string $condition, string $type = 'left'): self
    {
        $type = (!in_array(strtolower($type), ['left', 'right', 'inner'])) ? 'left' : strtolower($type);
        $this->joins[$type][] = [$this->db->config['prefix'].$table, $condition];
        return $this;
    }

    /**
     * @param string|array $table
     * @param string|null $alias
     * @return Query
     */
    protected function from($table, string $alias = null) : self
    {
        if(is_string($table))
        {
            if(!is_null($alias)) {
                $this->table[($this->db->config['prefix'] ?? '').$table] = $alias;
            }
            else {
                $this->table[] = ($this->db->config['prefix'] ?? '').$table;
            }
        }
        if(is_array($table))
        {
            foreach ($table As $value) {
                $this->table[] = ($this->db->config['prefix'] ?? '').$value;
            }
        }
        return $this;
    }

    /**
     * @param string ...$conditions
     * @return Query
     */
    protected function where(string ...$conditions) : self
    {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }

    /**
     * @param string $conditions
     * @param array $param
     * @return Query
     */
    protected function whereIn(string $conditions, array $param) : self
    {
        $this->where($conditions.' IN ('.implode(',', $param).')');
        return $this;
    }

    /**
     * @param array $params
     * @return Query
     */
    protected function params(array $params) : self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }


    /**
     * @param string|array $field
     * @param mixed|null $value
     * @param bool|null $escape
     * @return Query
     */
    protected function insert($field, $value = null, ?bool $escape = false): self
    {
        $this->crud = 'insert';
        if (is_array($field))
        {
            foreach ($field As $key => $value)
            {
                $this->fields[$key] = $value;
            }
        }
        if (is_string($field))
        {
            $this->fields[$field] = $value;
        }
        return $this;
    }

    /**
     * @param string $table
     * @param array|null $data
     * @param bool $execute
     * @return Query|null|\PDOStatement
     */
    protected function into(string $table, ?array $data = null, bool $execute = true)
    {
        $this->crud = 'insert';
        $this->from($table);
        if (is_array($data))
        {
            foreach ($data As $key => $value)
            {
                $this->fields[$key] = $value;
            }
        }
        if(true === $execute)
        {
            return $this->run();
        }
        return $this;
    }


    /**
     * @param $field
     * @param $value
     * @param bool|null $escape
     * @return Query
     */
    protected function set($field, $value, ?bool $escape = false): self
    {
        $this->crud = 'update';
        if (is_array($field))
        {
            foreach ($field As $key => $value)
            {
                $this->fields[$key] = $value;
            }
        }
        if (is_string($field))
        {
            $this->fields[$field] = $value;
        }
        return $this;
    }

    /**
     * @param $table
     * @param array|null $data
     * @param bool $execute
     * @return Query|null|\PDOStatement
     */
    protected function update($table, ?array $data = null, bool $execute = true)
    {
        $this->crud = 'update';
        $this->from($table);
        if (is_array($data))
        {
            foreach ($data As $key => $value)
            {
                $this->set($key, $value);
            }
        }
        if(true === $execute)
        {
            return $this->run();
        }
        return $this;
    }


    /**
     * @param null|string $table
     * @param bool $execute
     * @return Query|null|\PDOStatement
     */
    protected function delete(?string $table = null, bool $execute = true)
    {
        $this->crud = 'delete';
        if (!empty($table))
        {
            $this->from($table);
        }
        if(true === $execute)
        {
            return $this->run();
        }
        return $this;
    }



    protected function getSql()
    {
        switch ($this->crud) {
            case 'insert' :
                return $this->getInsert();
            case 'update' :
                return $this->getUpdate();
            case 'delete' :
                return $this->getDelete();
            default :
                return $this->getSelect();
        }
    }

    private function getInsert()
    {
        $parts = ['INSERT INTO'];
        $parts[] = end($this->table);

        $columns = [];
        $values = [];
        foreach ($this->fields As $key => $value)
        {
            $columns[] = $key;
            $values[] = "?";
            $this->params([$value]);
        }
        $parts[] = '(' . join(', ', $columns) . ')';
        $parts[] = 'VALUES (' . join(', ', $values) . ')';

        return join(' ', $parts);
    }

    private function getUpdate()
    {
        $parts = ['UPDATE'];
        $parts[] = end($this->table);
        $parts[] = 'SET';

        $columns = [];
        $values = [];
        foreach ($this->fields As $key => $value)
        {
            $columns[] = $key . " = ?";
            $values[] = $value;
        }
        $this->params = array_merge($values, $this->params);
        $parts[] = join(', ', $columns);

        if (!empty($this->conditions))
        {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->conditions) . ')';
        }
        return join(' ', $parts);
    }

    private function getDelete()
    {
        $parts = ['DELETE FROM'];
        $parts[] = end($this->table);

        if (!empty($this->conditions))
        {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->conditions) . ')';
        }
        return join(' ', $parts);
    }

    private function getSelect()
    {
        $parts = ['SELECT'];
        if(!empty($this->fields))
        {
            $parts[] = join(', ', $this->fields);
        }

        $parts[] = 'FROM';
        $parts[] = $this->buildTable();

        if (!empty($this->joins))
        {
            foreach ($this->joins As $type => $joins)
            {
                foreach ($joins As [$table, $condition])
                {
                    $parts[] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }
        if(!empty($this->conditions))
        {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->conditions) . ')';
        }
        if(!empty($this->group))
        {
            $parts[] = 'GROUP BY';
            $parts[] = join(', ', $this->group);
        }
        if (!empty($this->order))
        {
            $parts[] = 'ORDER BY';
            $parts[] = join(', ', $this->order);
        }
        if ($this->limit)
        {
            $parts[] = "LIMIT $this->limit";
        }
        return join(' ', $parts);
    }


    /**
     * @param int $mode
     * @param null|string $class
     * @param null|string $dir
     * @return array
     * @throws \dFramework\core\exception\Exception
     */
    protected function result(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = ENTITY_DIR): array
    {
        $query = $this->run();
        $this->free_db();

        if($mode !== DF_FCLA)
        {
            if($mode === DF_FARR)
            {
                $query->setFetchMode(PDO::FETCH_ASSOC);
            }
            else if ($mode === DF_FNUM)
            {
                $query->setFetchMode(PDO::FETCH_NUM);
            }
            else
            {
                $query->setFetchMode(PDO::FETCH_OBJ);
            }
            $query = $query->fetchAll();
            return $query;
        }
        if(empty($class))
        {
           Hydrator::Exception('Veuillez specifier la classe a charger');
        }
        $records = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records As $key => $value)
        {
            if(!isset($this->h_hidratedRecords[$key]))
            {
                $this->h_hidratedRecords[$key] = Hydrator::hydrate($value, $class, $dir);
            }
        }
        return $this->h_hidratedRecords;
    }
    private $h_hidratedRecords = [];


    /**
     * @param string $statement
     * @param array $datas
     * @return \PDOStatement|null
     */
    public function query(string $statement, array $datas = [])
    {
        $pdoStatement = $this->db->pdo()->prepare($statement);
        foreach ($datas as $key => $value)
        {
            $pdoStatement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                is_int($value) || is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $pdoStatement->execute();
        return $pdoStatement;
    }

    /**
     * @return \PDOStatement|null
     */
    protected function run()
    {
        return $this->query($this->getSql(), $this->params);
    }

    private function buildTable() : string
    {
        $from = [];
        foreach ($this->table As $key => $value)
        {
            if(is_string($key)) {
                $from[] = "$key As $value";
            }
            else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }


}