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
 * @version     3.3.0
 */

namespace dFramework\core\db\query;

use dFramework\core\db\connection\BaseConnection;
use dFramework\core\Entity;
use dFramework\core\loader\Service;
use PDO;

/**
 * Result
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Query
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/query/Builder.php
 */
class Result
{
    /**
     * Details de la requete
     *
     * @var array
     */
    private $details = [
        'num_rows'      => 0,
		'affected_rows' => 0,
		'insert_id'     => -1
    ];

    /**
     * Database query object
     *
     * @var object|resource
     */
    protected $query;

    /**
     * @var BaseConnection
     */
    protected $db;

    /**
     * @var integer
     */
    private $currentRow = 0;


    /**
     * Constructor
     *
     * @param BaseConnection $db
     * @param object|resource $query
     */
    public function __construct(BaseConnection &$db, &$query)
    {
        $this->query = &$query;
        $this->db = &$db;

        Service::event()->trigger('db.query', $this);
    }

    /**
     * Verifie si on utilise un objet pdo pour la connexion a la base de donnees
     *
     * @return boolean
     */
    protected function is_pdo() : bool
    {
        return $this->db->getType() === 'pdo';
    }
    /**
     * Verifie le driver utilise
     *
     * @param string $driver
     * @return boolean
     */
    protected function driverIs(string $driver) : bool
    {
        return $this->db->getDriver() === $driver;
    }


    /**
     * Fetch multiple rows from a select query.
     *
     * @param int|string $type
     * @return array
     */
    final public function all($type = PDO::FETCH_OBJ) : array
    {
       return $this->result($type);
    }

    /**
     * Recupere le premier resultat d'une requete en BD
     *
     * @param int|string $type
     * @return mixed Row
     */
    final public function first($type = PDO::FETCH_OBJ)
    {
        return $this->all($type)[0] ?? null;
    }
    final public function one($type = PDO::FETCH_OBJ)
    {
        return $this->first($type);
    }
    /**
     * Recupere le dernier element des resultats d'une requete en BD
     *
     * @param int|string $type
     * @return mixed Row
     */
    final public function last($type = PDO::FETCH_OBJ)
    {
        $records = $this->all($type);
        if (empty($records))
        {
            return null;
        }
        return end($records);
    }

    /**
	 * Returns the "next" row of the current results.
	 *
	 * @param int|string $type
	 * @return mixed
	 */
	final public function next($type = PDO::FETCH_OBJ)
	{
        $result = $this->all($type);
		if (empty($result))
		{
			return null;
		}

		return isset($result[$this->currentRow + 1]) ? $result[++ $this->currentRow] : null;
	}
    /**
	 * Returns the "previous" row of the current results.
	 *
	 * @param int|string $type
	 * @return mixed
	 */
	final public function previous($type = PDO::FETCH_OBJ)
	{
		$result = $this->all($type);
		if (empty($result))
		{
			return null;
		}

		if (isset($result[$this->currentRow - 1]))
		{
			-- $this->currentRow;
		}

		return $result[$this->currentRow];
	}

    /**
     * Recupere un resultat precis dans les resultat d'une requete en BD
     *
     * @param int $index
     * @param int|string $type
     * @return mixed Row
     */
    final public function row(int $index, $type = PDO::FETCH_OBJ)
    {
        $records = $this->all($type);
        if (empty($records[$index]))
        {
            return null;
        }
        return $records[$this->currentRow = $index];
    }

    /**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	final public function countField(): int
	{
        if ($this->is_pdo())
        {
            return $this->query->columnCount();
        }
        if ($this->driverIs('mysqli'))
        {
            return $this->query->field_count;
        }
        return 0;
	}

    /**
     * Fetch multiple rows from a select query.
     *
     * @param int|string $type
     * @return array
     */
    final public function result($type = PDO::FETCH_OBJ) : array
    {
        $data = [];

        if ($type === PDO::FETCH_OBJ OR $type === 'object')
        {
            $data = $this->getAsObject();
        }
        else if ($type === PDO::FETCH_ASSOC OR $type === 'array')
        {
            $data = $this->getAsArray();
        }
        else if (is_int($type))
        {
            if ($this->is_pdo())
            {
                $this->query->setFetchMode($type);
                $data = $this->query->fetchAll();
                $this->query->closeCursor();
            }
        }
        else if (is_string($type))
        {
            if (preg_match('#Entity$#', $type) OR is_subclass_of($type, Entity::class))
            {
                $records = $this->getAsArray();
                foreach ($records As $key => $value)
                {
                    if (!isset($data[$key]))
                    {
                        $data[$key] = Hydrator::hydrate($value, $type);
                    }
                }
            }
            else if ($this->is_pdo())
            {
                $this->query->setFetchMode(PDO::FETCH_CLASS, $type);
                $data = $this->query->fetchAll();
                $this->query->closeCursor();
            }
            else if ($this->driverIs('mysqli'))
            {
                while ($row = $this->query->fetch_assoc($type))
                {
                    $data[] = $row;
                }
                $this->query->close();
            }
        }

        $this->details['num_rows'] = count($data);

        return $data;
    }

    /**
     * Retourne un table contenant les resultat de la requete sous forme d'objet
     *
     * @return array
     */
    final public function getAsObject() : array
    {
        if ($this->is_pdo())
        {
            $data = $this->query->fetchAll(PDO::FETCH_OBJ);
            $this->query->closeCursor();

            return $data;
        }
        if ($this->driverIs('mysqli'))
        {
            while ($row = $this->query->fetch_object())
            {
                $data[] = $row;
            }
            $this->query->close();

            return $data;
        }

        return array_map(function($data) {
            return (object) $data;
        }, $this->getAsArray());
    }
    /**
     * Retourne un table contenant les resultat de la requete sous forme de tableau associatif
     *
     * @return array
     */
    final public function getAsArray() : array
    {
        $data = [];

        if ($this->is_pdo())
        {
            $data = $this->query->fetchAll(PDO::FETCH_ASSOC);
            $this->query->closeCursor();

            return $data;
        }
        if ($this->driverIs('mysqli'))
        {
            while ($row = $this->query->fetch_assoc())
            {
                $data[] = $row;
            }
            $this->query->close();

            return $data;
        }
        if ($this->driverIs('pgsql'))
        {
            $data = pg_fetch_all($this->query);
            pg_free_result($this->query);

            return $data;
        }
        if ($this->driverIs('sqlite3') AND $this->query)
        {
            while ($row = $this->query->fetchArray(SQLITE3_ASSOC))
            {
                $data[] = $row;
            }
            $this->query->finalize();
        }

        return $data;
    }

    /**
     * Recupere les details de la requete courrante
     *
     * @return array
     */
    final public function details() : array
    {
        if (!$this->query)
        {
            return $this->details;
        }
        $last = $this->db->lastQuery();

        return $this->details = array_merge((array) $last, [
            'affected_rows' => $this->affectedRows(),
            'num_rows'      => $this->numRows(),
            'insert_id'     => $this->insertID(),
        ]);
    }

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return int
	 */
	final public function affectedRows() : int
	{
		return $this->db->affectedRows();
	}

	/**
	 * Returns the number of rows in the result set.
	 *
	 * @return int
	 */
	final public function numRows() : int
	{
		return $this->db->numRows();
	}

	/**
     * Return the last id generated by autoincrement
     *
     * @return int|null
     */
    public function insertID() : ?int
    {
        return $this->db->insertID();
    }
	/**
	 * Return the last id generated by autoincrement
	 *
	 * @alias self::insertID()
	 * @return int|null
	 */
	final public function lastId() : ?int
	{
		return $this->insertID();
	}
}
