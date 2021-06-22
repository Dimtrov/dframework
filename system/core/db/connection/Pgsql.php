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

namespace dFramework\core\db\connection;

use PDO;
use PDOException;
use dFramework\core\exception\DatabaseException;

/**
 * Pgsql
 *
 * Make a postgre sql database connection
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Connection
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/connection/Pgsql.php
 */
class Pgsql extends BaseConnection
{
    /**
	 * Database driver
	 *
	 * @var string
	 */
	public $driver = 'postgre';

	//--------------------------------------------------------------------

	/**
	 * Database schema
	 *
	 * @var string
	 */
	public $schema = 'public';

	/**
	 * Identifier escape character
	 *
	 * @var string
	 */
	public $escapeChar = '"';

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
            case 'pgsql':
				if (empty($this->dsn))
				{
					$this->buildDSN();
				}
				// Strip pgsql if exists
				if (mb_strpos($this->dsn, 'pgsql:') === 0)
				{
					$this->dsn = mb_substr($this->dsn, 6);
				}
				// Convert semicolons to spaces.
				$this->dsn = str_replace(';', ' ', $this->dsn);

				$db = $persistent === true ? pg_pconnect($this->dsn) : pg_connect($this->dsn);

				if ($db !== false)
				{
					if ($persistent === true AND pg_connection_status($db) === PGSQL_CONNECTION_BAD AND pg_ping($db) === false)
					{
						return false;
					}

					empty($this->schema) OR $this->query("SET search_path TO {$this->schema},public");

					if ($this->setClientEncoding($db, $this->charset) === false)
					{
						return false;
					}
				}

                break;
            case 'pdopgsql':
            case 'pdo_pgsql':
				$this->dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                    $this->host,
                    !empty($this->port) ? $this->port : 5432,
                    $this->database,
                    $this->username,
                    $this->password
                );
    			$db = new PDO($this->dsn);

				break;
            default:
                # code...
                break;
        }
		if (!empty($this->charset))
        {
            $this->commands[] = "SET NAMES '{$this->charset}'";
        }
		$this->driver = 'postgre';
        $this->type = strpos($this->driver, 'pdo') !== false ? 'pdo' : $this->driver;

		return self::pushConnection('pgsql', $this, $db);
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
		if ($this->type === 'pdo')
		{
			$this->close();
			$this->initialize();
		}
		else if (pg_ping($this->conn) === false)
		{
			$this->conn = false;
		}
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
		pg_close($this->conn);
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

		if (empty($this->conn) OR ($this->type !== 'pdo' AND ( $pgVersion = pg_version($this->conn)) === false))
		{
			$this->initialize();
        }
		return $this->dataCache['version'] = $this->type !== 'pdo' ? ($pgVersion['server'] ?? false) : $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
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

       	if ($this->driver === 'pgsql')
        {
            $result = pg_query($this->conn, $sql);
            if (!$result)
            {
                $this->error['code'] = 0;
                $this->error['message'] = $error = pg_last_error($this->conn);
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
        if ($this->driver === 'pgsql')
        {
			return pg_escape_string($this->conn, $str);
        }
        return $this->conn->quote($str);
    }

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables(bool $prefixLimit = false): string
	{
		$sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \'' . $this->schema . "'";

		if ($prefixLimit !== false AND $this->prefix !== '')
		{
			return $sql . ' AND "table_name" LIKE \''
					. $this->escapeLikeString($this->prefix) . "%' "
					. sprintf($this->likeEscapeStr, $this->likeEscapeChar);
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
		return 'SELECT "column_name"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = '
				. $this->escape($this->prefixTable(strtolower($table)));
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
		$sql = 'SELECT "column_name", "data_type", "character_maximum_length", "numeric_precision", "column_default"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = '
				. $this->escape($this->prefixTable(strtolower($table)));

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException('No data fied found');
		}
		$query = $query->getAsObject();

		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i ++)
		{
			$retVal[$i]             = new \stdClass();
			$retVal[$i]->name       = $query[$i]->column_name;
			$retVal[$i]->type       = $query[$i]->data_type;
			$retVal[$i]->default    = $query[$i]->column_default;
			$retVal[$i]->max_length = $query[$i]->character_maximum_length > 0 ? $query[$i]->character_maximum_length : $query[$i]->numeric_precision;
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
		$sql = 'SELECT "indexname", "indexdef"
			FROM "pg_indexes"
			WHERE LOWER("tablename") = ' . $this->escape($this->prefixTable(strtolower($table))) . '
			AND "schemaname" = ' . $this->escape('public');

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException('No index data found');
		}
		$query = $query->getAsObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj         = new \stdClass();
			$obj->name   = $row->indexname;
			$_fields     = explode(',', preg_replace('/^.*\((.+?)\)$/', '$1', trim($row->indexdef)));
			$obj->fields = array_map(function ($v) {
				return trim($v);
			}, $_fields);

			if (strpos($row->indexdef, 'CREATE UNIQUE INDEX pk') === 0)
			{
				$obj->type = 'PRIMARY';
			}
			else
			{
				$obj->type = (strpos($row->indexdef, 'CREATE UNIQUE') === 0) ? 'UNIQUE' : 'INDEX';
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
	 * @throws DatabaseException
	 */
	public function _foreignKeyData(string $table): array
	{
		$sql = 'SELECT
                            tc.constraint_name, tc.table_name, kcu.column_name,
                            ccu.table_name AS foreign_table_name,
                            ccu.column_name AS foreign_column_name
                        FROM information_schema.table_constraints AS tc
                        JOIN information_schema.key_column_usage AS kcu
                            ON tc.constraint_name = kcu.constraint_name
                        JOIN information_schema.constraint_column_usage AS ccu
                            ON ccu.constraint_name = tc.constraint_name
                        WHERE constraint_type = ' . $this->escape('FOREIGN KEY') . ' AND
                            tc.table_name = ' . $this->escape($this->prefixTable($table));

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException('No foreign keys found for table '.$table);
		}
		$query = $query->getAsObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj                      = new \stdClass();
			$obj->constraint_name     = $row->constraint_name;
			$obj->table_name          = $row->table_name;
			$obj->column_name         = $row->column_name;
			$obj->foreign_table_name  = $row->foreign_table_name;
			$obj->foreign_column_name = $row->foreign_column_name;
			$retVal[]                 = $obj;
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
		return 'SET CONSTRAINTS ALL DEFERRED';
	}

	/**
	 * Returns platform-specific SQL to enable foreign key checks.
	 *
	 * @return string
	 */
	protected function _enableForeignKeyChecks()
	{
		return 'SET CONSTRAINTS ALL IMMEDIATE;';
	}

	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	public function insertID(): int
	{
		if ($this->type === 'pdo')
		{
			return $this->conn->lastInsertId();
		}
		$v = pg_version($this->conn);
		// 'server' key is only available since PostgreSQL 7.4
		$v = explode(' ', $v['server'])[0] ?? 0;

		$table  = func_num_args() > 0 ? func_get_arg(0) : null;
		$column = func_num_args() > 1 ? func_get_arg(1) : null;

		if ($table === null AND $v >= '8.1')
		{
			$sql = 'SELECT LASTVAL() AS ins_id';
		}
		elseif ($table !== null)
		{
			if ($column !== null AND $v >= '8.0')
			{
				$sql   = "SELECT pg_get_serial_sequence('{$table}', '{$column}') AS seq";
				$query = $this->query($sql);
				$query = $query->getRow();
				$seq   = $query->seq;
			}
			else
			{
				// seq_name passed in table parameter
				$seq = $table;
			}

			$sql = "SELECT CURRVAL('{$seq}') AS ins_id";
		}
		else
		{
			return (int) pg_last_oid($this->queryResult);
		}

		$query = $this->query($sql);
		$query = $query->row();
		return (int) $query->ins_id;
	}
	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return integer
	 */
	public function affectedRows(): int
	{
		if ($this->type === 'pdo')
		{
			return $this->queryResult->rowCount();
		}
		return pg_affected_rows($this->queryResult);
	}
	/**
     * Renvoi le nombre de ligne retourné par la requete
     *
     * @return integer
	 */
	public function numRows(): int
	{
		if ($this->type === 'pdo')
		{
			return $this->queryResult->rowCount();
		}
		return pg_num_rows($this->queryResult);
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	protected function _transBegin(): bool
	{
        if ($this->type !== 'pdo')
        {
            return (bool) pg_query($this->conn, 'BEGIN');
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
			return (bool) pg_query($this->conn, 'COMMIT');
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
			return (bool) pg_query($this->conn, 'ROLLBACK');
		}
		return $this->conn->rollback();
	}

	//--------------------------------------------------------------------

	/**
	 * Build a DSN from the provided parameters
	 *
	 * @return void
	 */
	protected function buildDSN()
	{
		$this->dsn === '' OR $this->dsn = '';

		// If UNIX sockets are used, we shouldn't set a port
		if (strpos($this->host, '/') !== false)
		{
			$this->port = '';
		}

		$this->host === '' OR $this->dsn = "host={$this->host} ";

		if (! empty($this->port) AND ctype_digit($this->port))
		{
			$this->dsn .= "port={$this->port} ";
		}

		if ($this->username !== '')
		{
			$this->dsn .= "user={$this->username} ";

			// An empty password is valid!
			// password must be set to null to ignore it.

			$this->password === null OR $this->dsn .= "password='{$this->password}' ";
		}

		$this->database === '' OR $this->dsn .= "dbname={$this->database} ";

		// We don't have these options as elements in our standard configuration
		// array, but they might be set by parse_url() if the configuration was
		// provided via string> Example:
		//
		// postgre://username:password@localhost:5432/database?connect_timeout=5&sslmode=1
		foreach (['connect_timeout', 'options', 'sslmode', 'service'] As $key)
		{
			if (isset($this->{$key}) AND is_string($this->{$key}) AND $this->{$key} !== '')
			{
				$this->dsn .= "{$key}='{$this->{$key}}' ";
			}
		}

		$this->dsn = rtrim($this->dsn);
	}

	//--------------------------------------------------------------------

	/**
	 * Set client encoding
	 *
	 * @param  string $charset The client encoding to which the data will be converted.
	 * @return boolean
	 */
	protected function setClientEncoding(&$db, string $charset): bool
	{
		return pg_set_client_encoding($db, $charset) === 0;
	}
}
