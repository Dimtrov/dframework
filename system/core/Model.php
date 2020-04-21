<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core;

use dFramework\core\db\Migrator;
use dFramework\core\db\Query;
use dFramework\core\exception\Exception;
use Envms\FluentPDO\Query As FluentPDOQuery;

/**
 * Model
 *
 * A global model of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/Model.php
 */

class Model extends Query
{
    /**
     * @var FluentQuery|null
     */
    private $fluent = null;

    /**
     * Renvoie une instance de l'objet FluentPDO a utiliser pour faire des query builder avances
     *
     * @return FluentPDOQuery|null
     */
    public function fluent()
    {
        if(null === $this->fluent)
        {
            try {
                $this->fluent = new FluentPDOQuery($this->db->pdo());
            }
            catch (\Exception $e) {
                Exception::show('Impossible de charger &laquo;<b> FluentPDO </b>&raquo; : ' . $e->getMessage());
            }
        }
        return $this->fluent;
    }

    /**
     * @var Migrator|null
     */
    private $migrator = null;

    /**
     * Retourne l'objet Migrator pour faire les migrations des bases de donnees
     *
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
                Exception::show('Impossible de charger l\'objet Migrator : ' . $e->getMessage());
            }
        }
        return $this->migrator;
    }

    /**
     * Do backup for database
     *
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
     * @param string|null $name Nom de la table dans laquelle on veut recuperer le dernier Id
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
     * Start a transaction
     *
     * @return bool
     */
    public function beginTransaction() : bool
    {
        return $this->db->pdo()->beginTransaction();
    }

    /**
     * Validate a transaction
     *
     * @return bool
     */
    public function commit() : bool
    {
        return $this->db->pdo()->commit();
    }

    /**
     * Cancel a transaction
     *
     * @return bool
     */
    public function rollback() : bool
    {
        return $this->db->pdo()->rollback();
    }


    /**
     * Verifie s'il existe un champ avec une donnee specifique dans une table de la base de donnee
     *
     * @param string $key Le nom du champ de la table
     * @param mixed $value La valeur recherchee
     * @param string $table La table dans laquelle on veut faire la recherche
     * @return bool
     */
    public function exist(string $key, $value, string $table) : bool
    {
        return $this->free_db()->from($table)
                ->where($key . ' = ?')->params([$value])
                ->count() > 0;
    }

}
