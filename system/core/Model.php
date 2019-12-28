<?php

namespace dFramework\core;

use dFramework\core\db\Query;
use dFramework\dependencies\fluentpdo\Query as FluentQuery;

/**
 * Membres
 */
class Model extends Query
{

    public function __construct()
    {
        parent::__construct();
    }

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
     *  Returns the last inserted id.
     * @param $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->db->pdo()->lastInsertId($name);
    }

    /**
     * Starts the transaction
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