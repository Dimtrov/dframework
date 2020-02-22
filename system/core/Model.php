<?php

namespace dFramework\core;

use dFramework\core\db\Migrator;
use dFramework\core\db\Query;
use dFramework\dependencies\envms\fluentpdo\Query as FluentQuery;

/**
 * Membres
 */
class Model extends Query
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var FluentQuery|null
     */
    private $fluent = null;

    /**
     * @return FluentQuery|null
     */
    public function fluent()
    {
        if(null === $this->fluent)
        {
            try {
                $this->fluent = new FluentQuery($this->db->pdo());
            }
            catch (\Exception $e) {
                die('Impossible de charger &laquo;<b> FluentPDO </b>&raquo; : ' . $e->getMessage());
            }
        }
        return $this->fluent;
    }

    /**
     * @var Migrator|null
     */
    private $migrator = null;

    /**
     * @return Migrator|null
     */
    protected function migrator() : ?Migrator
    {
        if(null === $this->migrator)
        {
            try {
                $this->migrator = new Migrator($this->db);
            }
            catch (\Exception $e) {
                die('Impossible de charger l\'objet Migrator : ' . $e->getMessage());
            }
        }
        return $this->migrator;
    }

    /**
     * Do backup for database
     * @param string $version
     */
    public function downDbTo(string $version)
    {
        $this->migrator()->down($version);
    }

    /**
     * Update database from specific backup
     *
     * @param string $version
     * @throws exception\DatabaseException
     */
    public function upDbFrom(string $version)
    {
        $this->migrator()->up($version);
    }


    /**
     *  Returns the last inserted id.
     *
     * @param $name
     * @return string
     */
    public function lastId($name = null)
    {
        return $this->db->pdo()->lastInsertId($name);
    }
    /**
     * @alias lastId
     * @deprecated
     * @param null $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->lastId($name);
    }

    /**
     * Starts the transaction
     *
     * @return boolean, true on success or false on failure
     */
    public function beginTransaction()
    {
        return $this->db->pdo()->beginTransaction();
    }


    /**
     * @param $key
     * @param $value
     * @param $table
     * @return bool
     */
    public function exist($key, $value, $table)
    {
        return $this->free_db()->from($table)
                ->where($key . ' = ?')->params([$value])
                ->count() > 0;
    }



}