<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2
 */

namespace dFramework\core\db;

use dFramework\core\exception\HydratorException;
use PDO;

/**
 * Query
 *
 * Query Builder system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/db/Query.php
 */

class Query
{
    /**
     * @var Database
     */
    public $db;

    private $crud = 'select';
    /**
     * Champs a selectionnés
     *
     * @var array
     */
    private $fields = [];
    /**
     * Conditions de selections
     *
     * @var array
     */
    private $conditions = [];
    /**
     * Paramètres ratachés aux conditions
     *
     * @var array
     */
    private  $params = [];
    /**
     * Tables de selection
     *
     * @var array
     */
    private $table = [];
    /**
     * Groupements de selections
     *
     * @var array
     */
    private $group = [];
    /**
     * Limites de seletion
     *
     * @var string
     */
    private $limit;
    /**
     * Ordre de selection
     *
     * @var array
     */
    private $order = [];
    /**
     * Jointures de tables
     *
     * @var array
     */
    private $joins = [];


    /**
     * Contructeur
     *
     * @param string $db_setting
     */
    public function __construct($db_setting = 'default')
    {
        $this->use($db_setting);
    }
    /**
     * __toString Magic Method
     *
     * @since 3.2
     * @return string
     */
    public function __toString()
    {
        return $this->getSql();
    }

    /**
     * Définit la configuration de base de données à utiliser
     * 
     * @param string $db_setting
     * @return Database
     * @throws \dFramework\core\exception\DatabaseException
     */
    public function use(string $db_setting) : Database
    {
        return $this->db = new Database($db_setting);
    }


    /**
     * Reinitialise les donnees du QueryBuilder
     * 
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
        $this->results = null;
        return $this;
    }


    /**
     * Specifie les champs a selectionner en base de donnees
     * 
     * @param string ...$fields
     * @return Query
     */
    protected function select(string ...$fields) : self
    {
        $empty = true;
        foreach ($fields As $value) 
        {
            if (!empty($value))
            {
                $empty = false;
                break;
            }
        }
        if($empty) {
            $fields = ['*'];
        }
        $this->fields = array_merge($this->fields, $fields);
        $this->crud = 'select';
        return $this;
    }

    /**
     * Compte le nombre de ligne dans une table
     * 
     * @param string $column
     * @param string $alias
     * @return int
     */
    protected function count(string $column = '*', string $alias = 'count'): int
    {
        $query = clone $this;
        $query->fields = [];
        $nbr = $query->select('COUNT(' . $column . ') As ' . $alias)->run()->fetchColumn();
        $query->free_db();
        unset($query);
        return $nbr;
    }

    /**
     * Definit les limites de selection des donnees
     * 
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
     * Definit l'ordre de sélection des données
     * 
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
     * Definit un groupement pour les données sélectionnées
     * 
     * @param string $field
     * @return Query
     */
    protected function group(string $field) : self
    {
        $this->group[] = $field;
        return $this;
    }

    /**
     * Fait une jointure de table
     * 
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return Query
     */
    protected function join(string $table, string $condition, string $type = 'inner'): self
    {
        $type = (!in_array(strtolower($type), ['left', 'right', 'inner'])) ? 'inner' : strtolower($type);
        $this->joins[$type][] = [$this->db->config['prefix'].$table, $condition];
        return $this;
    }

    /**
     * Définit la table de sélection des données
     * 
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
     * Définit une condition pour la sélection des données
     * 
     * @param string ...$conditions
     * @return Query
     */
    protected function where(string ...$conditions) : self
    {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }

    /**
     * Définit une contion pour la sélection des données
     * 
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
     * Définit une condition pour lq sélection des données
     * 
     * @param string $conditions
     * @param array $param
     * @return Query
     */
    protected function whereNotIn(string $conditions, array $param) : self
    {
        $this->where($conditions.' NOT IN ('.implode(',', $param).')');
        return $this;
    }

    /**
     * Attache les paramètres aux conditions définies pour la sélection des données
     * 
     * @param array $params
     * @return Query
     */
    protected function params(array $params) : self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }


    /**
     * Définit les données à insérer dans une table
     * 
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
     * Insère des données dans une table
     * 
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
            $this->insert($data);
        }
        if(true === $execute)
        {
            return $this->run();
        }
        return $this;
    }


    /**
     * Définit les données à modifier dans une table
     *  
     * @param $field
     * @param $value
     * @param bool|null $escape
     * @return Query
     */
    protected function set($field, $value = null, ?bool $escape = false): self
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
     * Modifie les données d'une table
     * 
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
            $this->set($data);
        }
        if(true === $execute)
        {
            return $this->run();
        }
        return $this;
    }


    /**
     * Supprime les données dans une table
     * 
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


    /**
     * Decrit la structure d'une table
     * 
     * @param string $table
     * @since 3.1
     * @return mixed
     */
    protected function describe(string $table)
    {
        return $this->query('DESCRIBE '.($this->db->config['prefix'] ?? '').$table)->fetchAll();
    }

    /**
     * Vide toute la table
     * 
     * @param string $table
     * @since 3.1
     * @return mixed
     */
    protected function truncate(string $table)
    {
        return $this->query('TRUNCATE '.($this->db->config['prefix'] ?? '').$table)->fetchAll();
    }


    /**
     * Renvoie le statement d'une requete
     *
     * @return string
     */
    protected function getSql() : string
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

    /**
     * Recupere tous les resultats d'une requete en BD
     *
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @return array
     * @throws \dFramework\core\exception\Exception
     */
    protected function result(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '') : array
    {
        return $this->buildResult($mode, $class, $dir);
    }
    /**
     * Recupere tous les resultats d'une requete en BD
     *
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @since 3.1
     * @alias result()
     * @return array
     * @throws \dFramework\core\exception\Exception
     */
    protected function all(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '') : array
    {
        return $this->result($mode, $class, $dir);
    }
    /**
     * Recupere le premier resultat d'une requete en BD
     *
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @return object|array|null
     * @throws \dFramework\core\exception\Exception
     */
    protected function first(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '')
    {
        return $this->result($mode, $class, $dir)[0] ?? null;
    }
    /**
     * Recupere le premier resultat d'une requete en BD
     *
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @since 3.1
     * @alias first()
     * @return object|array|null
     * @throws \dFramework\core\exception\Exception
     */
    protected function one(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '')
    {
        return $this->first($mode, $class, $dir);
    }
    /**
     * Recupere le dernier resultat d'une requete en BD
     *
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @since 3.1
     * @return array
     * @throws \dFramework\core\exception\Exception
     */
    protected function last(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '')
    {
        return $this->result($mode, $class, $dir)[-1] ?? null;
    }
    /**
     * Recupere un resultat precis dans les resultat d'une requete en BD
     *
     * @param int $index L'index de l'enregistrement a recupperer
     * @param int $mode Le style de recuperation des donnees (objet, tableau numeroté, tableau associatif)
     * @param null|string $class
     * @param null|string $dir
     * @since 3.1
     * @return array
     * @throws \dFramework\core\exception\Exception
     */
    protected function row(int $index, int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '')
    {
        return $this->result($mode, $class, $dir)[$index] ?? null;
    }


    /**
     * Définit et execute une requête SQL
     * 
     * @param string $statement
     * @param array $datas
     * @return \PDOStatement|null
     */
    public function query(string $statement, array $datas = [])
    {
        $pdoStatement = $this->db->pdo()->prepare($statement);
        foreach ($datas As $key => $value)
        {
            $pdoStatement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                (is_int($value) OR is_bool($value)) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $pdoStatement->execute();
        $pdoStatement->closeCursor();
        return $pdoStatement;
    }

    /**
     * Execute la requête SQL généré par le QueryBuilder
     * 
     * @return \PDOStatement|null
     */
    protected function run()
    {
        $response = $this->query($this->getSql(), $this->params);
        $this->free_db();
        return $response;
    }


    /**
     * Compile et renvoie un tableau  contenant les resultats issus de la bD
     *
     * @return array
     */
    private function buildResult(int $mode = DF_FOBJ, ?string $class = null, ?string $dir = '')
    {
        $query = $this->run();

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
            return $query->fetchAll();
        }
        if(empty($class))
        {
           HydratorException::show('Veuillez specifier la classe a charger');
        }
        $records = $query->fetchAll(PDO::FETCH_ASSOC);
        $hydratedRecords = [];

        foreach ($records As $key => $value)
        {
            if(!isset($hydratedRecords[$key]))
            {
                $hydratedRecords[$key] = Hydrator::hydrate($value, $class, $dir);
            }
        }
        return $hydratedRecords;
    }

    /**
     * Compile et renvoie la liste des tables pour le statement des requetes INSERT
     *
     * @return string
     */
    private function buildTable() : string
    {
        $from = [];
        foreach ($this->table As $key => $value)
        {
            if(is_string($key))
            {
                $from[] = "$key As $value";
            }
            else
            {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }

    /**
     * Renvoie le statement d'une requete INSERT
     *
     * @return string
     */
    private function getInsert() : string
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
    /**
     * Renvoie le statement d'une requete UPDATE
     *
     * @return string
     */
    private function getUpdate() : string
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
    /**
     * Renvoie le statement d'une requete DELETE
     *
     * @return string
     */
    private function getDelete() : string
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
    /**
     * Renvoie le statement d'une requete SELECT
     *
     * @return string
     */
    private function getSelect() : string
    {
        $parts = ['SELECT'];
        if (empty($this->fields))
        {
            $this->fields = ['*'];
        }
        $parts[] = join(', ', $this->fields);
        
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
        if (!empty($this->conditions))
        {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->conditions) . ')';
        }
        if (!empty($this->group))
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
}
