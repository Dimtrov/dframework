<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.2
 */

namespace dFramework\core;

use dFramework\core\db\Migrator;
use dFramework\core\db\Query;
use dFramework\core\exception\Exception;
use dFramework\core\loader\Load;
use dFramework\libraries\Api;
use Envms\FluentPDO\Query As FluentPDOQuery;

/**
 * Model
 *
 * A global model of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
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
     * @var Migrator|null
     */
    private $migrator = null;


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
     * Charge un model
     * 
     * @param string|array $model
     * @param string|null $alias
     * @since 3.2
     * @throws \ReflectionException
     */
    final protected function loadModel($model, ?string $alias = null)
    {
        Load::model($this, $model, $alias);
    }
    
    /**
     * Charge une api externe
     *
     * @param string $base_url
     * @param string $var
     * @since 3.2
     * @return void
     */
    final protected function useApi(string $base_url, string $var = 'api')
    {
        if (empty($this->{$var}) OR !$this->{$var} instanceof Api)
        {
            $this->{$var} = new Api;
        } 
        $this->{$var}->baseUrl($base_url);
    }
    /**
     * Injecte un objet d'Api au model
     *
     * @param Api $api
     * @param string $var
     * @since 3.2
     * @return void
     */
    final public function initApi(Api $api, string $var = 'api')
    {
        $this->{$var} = $api;
    }

    /**
     * Retourne l'objet Migrator pour faire les migrations des bases de donnees
     *
     * @return Migrator|null
     */
    protected function migrator() : ?Migrator
    {
        if(null === $this->migrator)
        {
            try 
            {
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
     *  Retourne le dernier ID inserer par autoincrement dans une table.
     *
     * @param string|null $name Nom de la table dans laquelle on veut recuperer le dernier Id
     * @return string
     */
    final public function lastId($name = null)
    {
        return $this->db->pdo()->lastInsertId($name);
    }
    /**
     * @alias lastId
     * @deprecated
     * @param null $name
     * @return string
     */
    final public function lastInsertId($name = null)
    {
        return $this->lastId($name);
    }

    /**
     * Initie une transaction
     *
     * @return bool
     */
    final public function beginTransaction() : bool
    {
        return $this->db->pdo()->beginTransaction();
    }

    /**
     * Valide une transaction
     *
     * @return bool
     */
    final public function commit() : bool
    {
        return $this->db->pdo()->commit();
    }

    /**
     * Annulle une transaction
     *
     * @return bool
     */
    final public function rollback() : bool
    {
        return $this->db->pdo()->rollback();
    }


    /**
     * Verifie s'il existe un champ avec une donnee specifique dans une table de la base de donnee
     *
     * @param string|array $key Le nom du champ de la table
     * @param mixed $value La valeur recherchee
     * @param string $table La table dans laquelle on veut faire la recherche
     * @throws Exception
     * @return bool
     */
    final public function exist($key, $value, string $table = null) : bool
    {
        $process = false;
        if (empty($table) AND is_array($key) AND is_string($value)) 
        {
            $process = true;
            $data  = $key;
            $table = $value;
        }
        if (!empty($table) AND is_string($key))
        {
            $process = true;
            $data = [$key => $value];
        }
        if (true === $process)
        {
            $this->free_db()->from($table);
            foreach ($data As $key => $value) 
            {
                $this->where($key . ' = ?')->params([$value]);
            }
            return $this->count() > 0;
        }
        throw new Exception("Mauvaise utilisation de la methode exist(). Consultez la doc pour plus d'informations", 1);
    }

    final public function existOther(array $dif, array $eq, string $table)
    {
        $this->db()->from($table);
        
        foreach ($dif As $key => $value) 
        {
            $this->where($key . ' != ?')->params([$value]);
        }
        foreach ($eq As $key => $value) 
        {
            $this->where($key .' = ?')->params([$value]);
        }
        
        return $this->count() > 0;
    }
}
