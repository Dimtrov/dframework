<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2
 */

namespace dFramework\core\db;

/**
 * Manager
 *
 * Database Manager Class
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.1
 * @file		/system/core/db/Manager.php
 */
class Manager
{    
    /**
     * @var Query
     */
    protected $db;

    public function __construct(string $db_setting = 'default')
    {
        $this->use($db_setting);
    }
    /**
     * @param string $db_setting
     * @return Database
     */
    public function use(string $db_setting) : Query
    {
        return $this->db = (new Query)->use($db_setting);
    }

    public static function instance(string $db_setting = 'default')
    {
        if (null === self::$_instance)
        {
            $class = __CLASS__;
            self::$_instance = new $class($db_setting);
        }
        return self::$_instance;
    }
    private static $_instance = null;

    /**
     * Renvoi la liste des tables de la base de donnees
     * 
     * @return array
     */
    public function listTables()
    {
        $request = $this->db->query('SHOW TABLES STATUS');
        $response = $request->fetchAll(\PDO::FETCH_OBJ);
        $request->closeCursor();
        return $response;
    }

    /**
     * Renvoi le nombre de table que compte une base de donnees
     * 
     * @return int
     */
    public function countTables()
    {
        $request = $this->db->query('SHOW TABLES STATUS');
        $response = $request->fetchColumn();
        $request->closeCursor();
        return $response;
    }

     /**
     * Renvoi la liste de toutes les colones d'une table
     * 
     * @param string $table
     * @return array
     */
    public function getColumns(string $table)
    {
        $request = $this->db->query('SHOW COLUMNS FROM '.$table);
        $response = $request->fetchAll(\PDO::FETCH_OBJ);
        $request->closeCursor();

        if (!empty($response))
        {
            foreach ($response As &$column) 
            {
                if (!empty($column))
                {
                    foreach ($column As $key => $value)
                    {
                        $column->{strtolower($key)} = $value;
                        if (strtolower($key) != $key)
                        {
                            unset($column->{$key});
                        }
                    }
                }   
            }
        }
        return $response;
    }

    /**
     * Renvoi le nom de tous les champs d'une table
     * 
     * @param string $table
     * @return array
     */
    public function getAttrs(string $table)
    {
        $response = [];
        $columns = $this->getColumns($table);
        foreach ($columns As $column) 
        {
            $response[] = $column->field;
        }
        return $response;
    }

    /**
     * Renvoi le nom de tous les champs qui sont cles primaire dans une table
     * 
     * @param string $table
     * @param string $key_type
     * @return array
     */
    public function getKeys(string $table, $key_type = 'PRI')
    {
        $response = [];
        $columns = $this->getColumns($table);
        foreach ($columns As $column) 
        {
            if (strtolower($column->key) === strtolower($key_type)) 
            {
                $response[] = $column->field;
            }
        }
        return $response;
    }

    //methodes tres puissante pour obtenir la liste detailler sur les clé (etrangere) d une table

    public function getFks(string $table, ?string $dbname = null)
    {
        $dbname = (empty($dbname)) ? $this->db->db->config['dbname'] : $dbname;

        $request = $this->db->query('
            SELECT k . *
            FROM INFORMATION_SCHEMA.key_column_usage AS k
            INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA
                AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
            WHERE c.CONSTRAINT_TYPE = \'FOREIGN KEY\'
                AND k.TABLE_SCHEMA = ? AND k.TABLE_NAME = ?
            ', 
            [$dbname, $table]
        );
        $response = $request->fetchAll(\PDO::FETCH_OBJ);
        $request->closeCursor();

        foreach ($response As &$fk) 
        {
            if (!empty($fk))
            {
                foreach ($fk As $key => $value) 
                {
                    $fk->{strtolower($key)} = $value;
                    if (strtolower($key) != $key)
                    {
                        unset($fk->{$key});
                    }
                }
            }
        }
        return $response;
    }
}
