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
 * @version     3.4.0
 */

namespace dFramework\core\db\connection;

use PDO;
use SQLite3;
use PDOException;
use dFramework\core\exception\DatabaseException;

/**
 * Sqlite
 *
 * Make a sqlite database connection
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Connection
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/connection/Sqlite.php
 */
class Sqlite extends BaseConnection
{
    protected $error = [
        'message' => '',
        'code' => 0
    ];

    /**
	 * Connect to the database.
	 *
	 * @param boolean $persistent
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function connect(bool $persistent = false)
	{
        $db = null;

        switch ($this->driver)
        {
            case 'sqlite3':
                $db = (! $this->password)
					? new SQLite3($this->database)
					: new SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);

                break;
            case 'pdosqlite':
            case 'pdo_sqlite':
                $db = new PDO('sqlite:/'.$this->database);

				break;
            default:
                # code...
                break;
        }
        $this->type = strpos($this->driver, 'pdo') !== false ? 'pdo' : $this->driver;

		return self::pushConnection('sqlite', $this, $db);
	}

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return void
	 */
	public function reconnect()
	{
		$this->close();
		$this->initialize();
	}

	/**
	 * Close the database connection.
	 *
	 * @return void
	 */
	protected function _close()
	{
		if ($this->type === 'pdo')
		{
			return $this->conn = null;
		}
		$this->conn->close();
	}

    /**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return boolean
	 */
	public function setDatabase(string $databaseName): bool
	{
		return false;
	}

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		if (isset($this->dataCache['version']))
		{
			return $this->dataCache['version'];
		}
		if ($this->type !== 'pdo' AND empty($this->conn))
		{
			$this->initialize();
		}
		$version = SQLite3::version();

		return $this->dataCache['version'] = $this->type !== 'pdo' ? $version['versionString'] : $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

    /**
	 * Executes the query against the database.
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function execute(string $sql, array $params = [])
	{
        $error = null;
        $result = false;
		$time = microtime(true);

        if ($this->type !== 'pdo')
        {
            $result = $this->isWriteType($sql) ? $this->conn->exec($sql) : $this->conn->query($sql);
            if (!$result)
            {
                $this->error['code'] = $this->conn->lastErrorCode();
                $this->error['message'] = $error = $this->conn->lastErrorMsg();
            }
        }
        else
        {
            try {
                $result = $this->conn->prepare($sql);

                if (!$result)
                {
                    $error = $this->conn->errorInfo();
                }
                else
                {
                    foreach ($params As $key => $value)
                    {
                        $result->bindValue(
                            is_int($key) ? $key + 1 : $key,
                            $value,
                            is_int($value) || is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                        );
                    }
                    $result->execute();
                }
            }
            catch (PDOException $ex) {
                $this->error['code'] = $ex->getCode();
                $this->error['message'] = $error = $ex->getMessage();
            }
        }
        if ($error !== null)
        {
            $error .= "\nSQL: ".$sql;
            throw new DatabaseException('Database error: '.$error);
        }

		$this->last_query = [
			'sql'      => $sql,
			'start' => $time,
			'duration'   => microtime(true) - $time,
        ];
        $this->stats['queries'][] = &$this->last_query;

        return $result;
	}

    /**
	 * Platform-dependant string escape
	 *
	 * @param  string $str
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		if (is_bool($str))
		{
			return $str;
		}
		if (! $this->conn)
		{
			$this->initialize();
		}
        if ($this->type !== 'pdo')
        {
            return $this->conn->escapeString($str);
        }
        return $this->conn->quote($str);
    }

    /**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables(bool $prefixLimit = false): string
	{
		return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\''
			   . ' AND "NAME" NOT LIKE \'sqlite!_%\' ESCAPE \'!\''
			   . (($prefixLimit !== false && $this->prefix !== '')
				? ' AND "NAME" LIKE \'' . $this->escapeLikeString($this->prefix) . '%\' ' . sprintf($this->likeEscapeStr,
					$this->likeEscapeChar)
				: '');
	}

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _listColumns(string $table = ''): string
	{
		return 'PRAGMA TABLE_INFO(' . $this->protectIdentifiers($this->prefixTable($table), true, null, false) . ')';
	}

	/**
	 * Fetch Field Names
	 *
	 * @param string $table Table name
	 *
	 * @return array|false
	 * @throws DatabaseException
	 */
	public function getFieldNames(string $table)
	{
		// Is there a cached result?
		if (isset($this->dataCache['field_names'][$table]))
		{
			return $this->dataCache['field_names'][$table];
		}

		if (empty($this->conn))
		{
			$this->initialize();
		}

		if (false === ($sql = $this->_listColumns($table)))
		{
			if ($this->debug)
			{
				throw new DatabaseException('Feature Unavailable');
			}

			return false;
		}

		$query                                  = $this->query($sql);
		$this->dataCache['field_names'][$table] = [];

		foreach ($query->getAsArray() as $row)
		{
			// Do we know from where to get the column's name?
			if (! isset($key))
			{
				if (isset($row['column_name']))
				{
					$key = 'column_name';
				}
				elseif (isset($row['COLUMN_NAME']))
				{
					$key = 'COLUMN_NAME';
				}
				elseif (isset($row['name']))
				{
					$key = 'name';
				}
				else
				{
					// We have no other choice but to just get the first element's key.
					$key = key($row);
				}
			}

			$this->dataCache['field_names'][$table][] = $row[$key];
		}

		return $this->dataCache['field_names'][$table];
	}

	/**
	 * Returns an array of objects with field data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 * @throws DatabaseException
	 */
	public function _fieldData(string $table): array
	{
		if (($query = $this->query('PRAGMA TABLE_INFO(' . $this->protectIdentifiers($this->prefixTable($table), true, null,
					false) . ')')) === false)
		{
			throw new DatabaseException('No data fied found');
		}
		$query = $query->getResultObject();

		if (empty($query))
		{
			return [];
		}
		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]              = new \stdClass();
			$retVal[$i]->name        = $query[$i]->name;
			$retVal[$i]->type        = $query[$i]->type;
			$retVal[$i]->max_length  = null;
			$retVal[$i]->default     = $query[$i]->dflt_value;
			$retVal[$i]->primary_key = isset($query[$i]->pk) ? (bool)$query[$i]->pk : false;
			$retVal[$i]->nullable    = isset($query[$i]->notnull) ? ! (bool)$query[$i]->notnull : false;
		}

		return $retVal;
	}

	/**
	 * Returns an array of objects with index data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 * @throws DatabaseException
	 */
	public function _indexData(string $table): array
	{
		// Get indexes
		// Don't use PRAGMA index_list, so we can preserve index order
		$sql = "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=" . $this->escape($this->prefixTable(strtolower($table)));
		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException('No index data found');
		}
		$query = $query->getAsObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj       = new \stdClass();
			$obj->name = $row->name;

			// Get fields for index
			$obj->fields = [];
			if (($fields = $this->query('PRAGMA index_info(' . $this->escape(strtolower($row->name)) . ')')) === false)
			{
				throw new DatabaseException('No index data found');
			}
			$fields = $fields->getAsObject();

			foreach ($fields as $field)
			{
				$obj->fields[] = $field->name;
			}

			$retVal[$obj->name] = $obj;
		}

		return $retVal;
	}

	/**
	 * Returns an array of objects with Foreign key data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 */
	public function _foreignKeyData(string $table): array
	{
		if ($this->supportsForeignKeys() !== true)
		{
			return [];
		}

		$tables = $this->listTables();

		if (empty($tables))
		{
			return [];
		}

		$retVal = [];

		foreach ($tables as $table)
		{
			$query = $this->query("PRAGMA foreign_key_list({$table})")->result();

			foreach ($query as $row)
			{
				$obj                     = new \stdClass();
				$obj->constraint_name    = $row->from . ' to ' . $row->table . '.' . $row->to;
				$obj->table_name         = $table;
				$obj->foreign_table_name = $row->table;
				$obj->sequence           = $row->seq;

				$retVal[] = $obj;
			}
		}

		return $retVal;
	}

	/**
	 * Returns platform-specific SQL to disable foreign key checks.
	 *
	 * @return string
	 */
	protected function _disableForeignKeyChecks()
	{
		return 'PRAGMA foreign_keys = OFF';
	}

	/**
	 * Returns platform-specific SQL to enable foreign key checks.
	 *
	 * @return string
	 */
	protected function _enableForeignKeyChecks()
	{
		return 'PRAGMA foreign_keys = ON';
	}

	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	public function insertID(): int
	{
		if ($this->type !== 'pdo')
		{
			return $this->conn->lastInsertRowID();
		}
		return $this->conn->lastInsertId();
	}

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return integer
	 */
	public function affectedRows(): int
	{
		if ($this->type !== 'pdo')
		{
			return $this->conn->changes();
		}
		return $this->conn->rowCount();
	}

	/**
     * Renvoi le nombre de ligne retourné par la requete
     *
     * @return integer
	 */
	public function numRows(): int
	{
		if ($this->type !== 'pdo')
		{
			return 0;
		}
		return $this->queryResult->rowCount();
	}

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	protected function _transBegin(): bool
	{
        if ($this->type !== 'pdo')
        {
            return $this->conn->exec('BEGIN TRANSACTION');
        }
        return $this->conn->beginTransaction();
	}

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	protected function _transCommit(): bool
	{
		if ($this->type !== 'pdo')
		{
			return $this->conn->exec('END TRANSACTION');
		}
        return $this->conn->commit();
	}

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	protected function _transRollback(): bool
	{
		if ($this->type !== 'pdo')
		{
			return $this->conn->exec('ROLLBACK');
		}
		return $this->conn->rollback();
	}


	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return boolean
	 */
	public function isWriteType($sql): bool
	{
		return (bool)preg_match(
			'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i',
			$sql);
	}

	/**
	 * Checks to see if the current install supports Foreign Keys
	 * and has them enabled.
	 *
	 * @return boolean
	 */
	public function supportsForeignKeys(): bool
	{
		$result = $this->query('PRAGMA foreign_keys');

		return (bool)$result;
	}
}
