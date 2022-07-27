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
use mysqli;
use PDOException;
use dFramework\core\exception\DatabaseException;

/**
 * Mysql
 *
 * Make a mysql database connection
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Connection
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/connection/Mysql.php
 */
class Mysql extends BaseConnection
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
            case 'mysqli':
                $db = new mysqli(
                    $this->host,
                    $this->username,
                    $this->password,
                    true === $this->with_database ? $this->database : null,
                    $this->port
                );

                if ($db->connect_error)
                {
                    throw new DatabaseException('Connection error: '.$db->connect_error);
                }

                break;
            case 'pdomysql':
            case 'pdo_mysql':
                $this->dsn = true === $this->with_database ? sprintf(
                    'mysql:host=%s;port=%d;dbname=%s',
                    $this->host,
                    $this->port,
                    $this->database
				) : sprintf(
                    'mysql:host=%s;port=%d',
                    $this->host,
                    $this->port
				);
				$db = new PDO($this->dsn, $this->username, $this->password);
				$this->commands[] = 'SET SQL_MODE=ANSI_QUOTES';

				break;
            default:
                # code...
                break;
        }
        if (!empty($this->charset))
        {
            $this->commands[] = "SET NAMES '{$this->charset}'" . (!empty($this->collation) ? " COLLATE '{$this->collation}'" : '');
        }
        $this->type = strpos($this->driver, 'pdo') !== false ? 'pdo' : $this->driver;

		return self::pushConnection('mysql', $this, $db);
	}

	//--------------------------------------------------------------------

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

    //--------------------------------------------------------------------

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
		if ($databaseName === '')
		{
			$databaseName = $this->database;
		}
		if (empty($this->conn))
		{
			$this->initialize();
        }
        if ($this->driver === 'mysqli')
        {
            if ($this->connID->select_db($databaseName))
            {
                $this->database = $databaseName;

                return true;
            }
            return false;
        }
		return true;
	}

    //--------------------------------------------------------------------

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 *
	 * @return string
	 */
	public function getPlatform(): string
	{
		if (isset($this->dataCache['platform']))
		{
			return $this->dataCache['platform'];
		}

		if (empty($this->conn))
		{
			$this->initialize();
        }

		return $this->dataCache['platform'] = $this->driver === 'mysqli' ? 'mysql' : $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
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

		if (empty($this->conn))
		{
			$this->initialize();
        }

		return $this->dataCache['version'] = $this->driver === 'mysqli' ? $this->conn->server_version : $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
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

        if ($this->driver === 'mysqli')
        {
            $result = $this->conn->query($sql);
            if (!$result)
            {
                $this->error['code'] = $this->conn->errno;
                $this->error['message'] = $error = $this->conn->error;
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
        if ($this->driver === 'mysqli')
        {
            return "'".$this->conn->real_escape_string($str)."'";
        }
        return $this->conn->quote($str);
    }

    //--------------------------------------------------------------------

	/**
	 * Escape Like String Direct
	 * There are a few instances where MySQLi queries cannot take the
	 * additional "ESCAPE x" parameter for specifying the escape character
	 * in "LIKE" strings, and this handles those directly with a backslash.
	 *
	 * @param  string|string[] $str Input string
	 * @return string|string[]
	 */
	public function escapeLikeStringDirect($str)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escapeLikeStringDirect($val);
			}

			return $str;
		}

		$str = $this->_escapeString($str);

		// Escape LIKE condition wildcards
		return str_replace([
			$this->likeEscapeChar,
			'%',
			'_',
		], [
			'\\' . $this->likeEscapeChar,
			'\\' . '%',
			'\\' . '_',
		], $str
		);
	}

    //--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 * Uses escapeLikeStringDirect().
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables(bool $prefixLimit = false): string
	{
		$sql = 'SHOW TABLES FROM ' . $this->escapeIdentifiers($this->database);

		if ($prefixLimit !== false AND $this->prefix !== '')
		{
			return $sql . " LIKE '" . $this->escapeLikeStringDirect($this->prefix) . "%'";
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _listColumns(string $table = ''): string
	{
		return 'SHOW COLUMNS FROM ' . $this->protectIdentifiers($this->prefixTable($table), true, null, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with field data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 * @throws DatabaseException
	 */
	public function _fieldData(string $table): array
	{
		$table = $this->protectIdentifiers($this->prefixTable($table), true, null, false);

		if (($query = $this->query('SHOW COLUMNS FROM ' . $table)) === false)
		{
			throw new DatabaseException('No data fied found');
		}
		$query = $query->getAsObject();

		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]       = new \stdClass();
			$retVal[$i]->name = $query[$i]->field ?? $query[$i]->Field;

			sscanf(($query[$i]->type ?? $query[$i]->Type), '%[a-z](%d)', $retVal[$i]->type, $retVal[$i]->max_length);

			$retVal[$i]->nullable    = ($query[$i]->null ?? $query[$i]->Null) === 'YES';
			$retVal[$i]->default     = $query[$i]->default ?? $query[$i]->Default;
			$retVal[$i]->primary_key = (int)(($query[$i]->key ?? $query[$i]->Key) === 'PRI');
		}

		return $retVal;
	}

    //--------------------------------------------------------------------

	/**
	 * Returns an array of objects with index data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 * @throws DatabaseException
	 * @throws \LogicException
	 */
	public function _indexData(string $table): array
	{
		$table = $this->protectIdentifiers($this->prefixTable($table), true, null, false);

		if (($query = $this->query('SHOW INDEX FROM ' . $table)) === false)
		{
			throw new DatabaseException('No index data found');
		}

		if (! $indexes = $query->getAsArray())
		{
			return [];
		}

		$keys = [];

		foreach ($indexes as $index)
		{
			if (empty($keys[$index['Key_name']]))
			{
				$keys[$index['Key_name']]       = new \stdClass();
				$keys[$index['Key_name']]->name = $index['Key_name'];

				if ($index['Key_name'] === 'PRIMARY')
				{
					$type = 'PRIMARY';
				}
				elseif ($index['Index_type'] === 'FULLTEXT')
				{
					$type = 'FULLTEXT';
				}
				elseif ($index['Non_unique'])
				{
					if ($index['Index_type'] === 'SPATIAL')
					{
						$type = 'SPATIAL';
					}
					else
					{
						$type = 'INDEX';
					}
				}
				else
				{
					$type = 'UNIQUE';
				}

				$keys[$index['Key_name']]->type = $type;
			}

			$keys[$index['Key_name']]->fields[] = $index['Column_name'];
		}

		return $keys;
	}

	/**
	 * Returns an array of objects with Foreign key data
	 *
	 * @param  string $table
	 * @return \stdClass[]
	 * @throws DatabaseException
	 */
	public function _foreignKeyData(string $table): array
	{
		$sql = '
                    SELECT
                        tc.CONSTRAINT_NAME,
                        tc.TABLE_NAME,
                        kcu.COLUMN_NAME,
                        rc.REFERENCED_TABLE_NAME,
                        kcu.REFERENCED_COLUMN_NAME
                    FROM information_schema.TABLE_CONSTRAINTS AS tc
                    INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS AS rc
                        ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                    INNER JOIN information_schema.KEY_COLUMN_USAGE AS kcu
                        ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                    WHERE
                        tc.CONSTRAINT_TYPE = ' . $this->escape('FOREIGN KEY') . ' AND
                        tc.TABLE_SCHEMA = ' . $this->escape($this->database) . ' AND
                        tc.TABLE_NAME = ' . $this->escape($this->prefixTable($table));

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException('No foreign keys found for table '.$table);
		}
		$query = $query->getAsObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj                      = new \stdClass();
			$obj->constraint_name     = $row->CONSTRAINT_NAME;
			$obj->table_name          = $row->TABLE_NAME;
			$obj->column_name         = $row->COLUMN_NAME;
			$obj->foreign_table_name  = $row->REFERENCED_TABLE_NAME;
			$obj->foreign_column_name = $row->REFERENCED_COLUMN_NAME;

			$retVal[] = $obj;
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
		return 'SET FOREIGN_KEY_CHECKS=0';
	}

	/**
	 * Returns platform-specific SQL to enable foreign key checks.
	 *
	 * @return string
	 */
	protected function _enableForeignKeyChecks()
	{
		return 'SET FOREIGN_KEY_CHECKS=1';
    }

	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	public function insertID(): int
	{
		if ($this->driver === 'mysqli')
		{
			return $this->conn->insert_id;
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
		if ($this->driver === 'mysqli')
		{
			return $this->conn->affected_rows ?? 0;
		}
		return $this->queryResult->rowCount();
	}

	/**
     * Renvoi le nombre de ligne retourné par la requete
     *
     * @return integer
	 */
	public function numRows(): int
	{
		if ($this->driver === 'mysqli')
		{
			return $this->queryResult->num_rows ?? 0;
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
        if ($this->driver === 'mysqli')
        {
            $this->conn->autocommit(false);
            return $this->conn->begin_transaction();
        }
        return $this->conn->beginTransaction();
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	protected function _transCommit(): bool
	{
        if ($this->conn->commit())
        {
            if ($this->driver === 'mysqli')
            {
                $this->conn->autocommit(true);
            }
            return true;
        }
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	protected function _transRollback(): bool
	{
		if ($this->conn->rollback())
		{
            if ($this->driver === 'mysqli')
            {
                $this->conn->autocommit(true);
            }
			return true;
		}
		return false;
	}
}
