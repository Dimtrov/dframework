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

use dFramework\core\db\query\Result;
use dFramework\core\debug\Timer;
use dFramework\core\loader\Service;
use dFramework\core\exception\DatabaseException;
use PDO;

/**
 * BaseConnection
 *
 * Abstract class to make a database connection
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Connection
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit 		CodeIgniter 4.0 (\CodeIgniter\Database\BaseConnection - https://codeigniter.com)
 * @file		/system/core/db/connection/BaseConnection.php
 */
abstract class BaseConnection
{
    /**
	 * Data Source Name / Connect string
	 *
	 * @var string
	 */
	protected $dsn;

	/**
	 * Database port
	 *
	 * @var integer
	 */
	protected $port = '';

	/**
	 * Hostname
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Username
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected $database;

    /**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'pdomysql';

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	protected $prefix = '';

    /**
	 * Persistent connection flag
	 *
	 * @var boolean
	 */
	protected $pConnect = false;

	/**
	 * Debug flag
	 *
	 * Whether to display error messages.
	 *
	 * @var boolean|'auto'
	 */
	protected $debug = 'auto';

	/**
	 * Should we cache results?
	 *
	 * @var boolean
	 */
	protected $cache = true;

	/**
	 * Character set
	 *
	 * @var string
	 */
	protected $charset = 'utf8';

	/**
	 * Collation
	 *
	 * @var string
	 */
    protected $collation = 'utf8_general_ci';

    protected $options = [
        'column_case' => 'inherit',
        'enable_stats' => false,
        'enable_cache' => true,
    ];

	/**
	 * Swap Prefix
	 *
	 * @var string
	 */
	protected $swapPre = '';

	/**
	 * Encryption flag/data
	 *
	 * @var mixed
	 */
	protected $encrypt = false;

	/**
	 * Compression flag
	 *
	 * @var boolean
	 */
	protected $compress = false;

	/**
	 * Strict ON flag
	 *
	 * Whether we're running in strict SQL mode.
	 *
	 * @var boolean
	 */
    protected $strictOn;

	//--------------------------------------------------------------------

    /**
     * @var string type de pilote
     */
    protected $type = 'pdo';

	/**
	 * @var array Statistiques de la requete
	 */
    protected $stats = [
		'queries' => []
	];

	/**
	 * @var array commandes sql a executer a l'initialisation de la connexion a la base de donnees
	 */
    protected $commands = [];

	protected $error = [
        'message' => '',
        'code' => 0
    ];


	/**
	 * The last query object that was executed
	 * on this connection.
	 *
	 * @var array
	 */
	protected $last_query = [];

	/**
	 * Connection ID
	 *
	 * @var object|resource
	 */
	public $conn = false;

	/**
	 * @var object|resource
	 */
	public $queryResult;

	/**
	 * Protect identifiers flag
	 *
	 * @var boolean
	 */
	public $protectIdentifiers = true;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var array
	 */
	protected $reservedIdentifiers = ['*'];

	/**
	 * Identifier escape character
	 *
	 * @var string
	 */
	public $escapeChar = '"';

	/**
	 * ESCAPE statement string
	 *
	 * @var string
	 */
	public $likeEscapeStr = " ESCAPE '%s' ";

	/**
	 * ESCAPE character
	 *
	 * @var string
	 */
	public $likeEscapeChar = '!';

	/**
	 * Holds previously looked up data
	 * for performance reasons.
	 *
	 * @var array
	 */
	public $dataCache = [];


	/**
	 * How long it took to establish connection.
	 *
	 * @var float
	 */
	protected $connectDuration;

	/**
	 * If true, no queries will actually be
	 * ran against the database.
	 *
	 * @var boolean
	 */
	protected $pretend = false;

	/**
	 * Transaction enabled flag
	 *
	 * @var boolean
	 */
	public $transEnabled = true;

	/**
	 * Strict transaction mode flag
	 *
	 * @var boolean
	 */
	public $transStrict = true;

	/**
	 * Transaction depth level
	 *
	 * @var integer
	 */
	protected $transDepth = 0;

	/**
	 * Transaction status flag
	 *
	 * Used with transactions to determine if a rollback should occur.
	 *
	 * @var boolean
	 */
	protected $transStatus = true;

	/**
	 * Transaction failure flag
	 *
	 * Used with transactions to determine if a transaction has failed.
	 *
	 * @var boolean
	 */
	protected $transFailure = false;

    /**
     * Benchmark
     *
     * @var \dFramework\core\debug\Timer
     */
	protected $timer;

	/**
	 * Liste des connexions etablies
	 *
	 * @var array
	 */
	protected static $allConnections = [];

	/**
	 * Specifie si on doit ouvrir la connexion au serveur en se connectant automatiquement à la base de donnees
	 */
	protected $with_database = true;


	//--------------------------------------------------------------------

	/**
	 * Saves our connection settings.
	 *
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		foreach ($params as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
        }

		$this->timer = is_cli() ? new Timer : Service::timer();
    }

	/**
	 * On ouvre la connexion au serveur en se connectant directement à la base de donnees
	 *
	 * @return self
	 */
	public function withDatabase() : self
	{
		$this->with_database = true;

		return $this;
	}

	/**
	 * On ouvre la connexion au serveur sans se connecter à la base de donnees
	 *
	 * @return self
	 */
	public function withoutDatabase() : self
	{
		$this->with_database = false;

		return $this;
	}

    public function getType() : string
    {
        return $this->type;
    }
    public function getDriver() : string
    {
        return $this->driver;
    }

    /**
     * Gets the query statistics.
     */
    public function stats()
    {
        $this->stats['total_time'] = 0;
        $this->stats['num_queries'] = 0;
        $this->stats['num_rows'] = 0;
        $this->stats['num_changes'] = 0;

        if (isset($this->stats['queries']))
        {
            foreach ($this->stats['queries'] as $query)
            {
                $this->stats['total_time'] += $query['time'];
                $this->stats['num_queries'] += 1;
                $this->stats['num_rows'] += $query['rows'] ?? 0;
                $this->stats['num_changes'] += $query['changes'] ?? 0;
            }
        }

        $this->stats['avg_query_time'] =
            $this->stats['total_time'] /
            (float)(($this->stats['num_queries'] > 0) ? $this->stats['num_queries'] : 1);

        return $this->stats;
    }

	/**
	 * Renvoi la liste des toutes les connexions a la base de donnees
	 *
	 * @return array
	 */
	public static function getAllConnections() : array
    {
        return static::$allConnections;
	}
	/**
	 * Ajoute une connexion etablie
	 *
	 * @param string $name
	 * @param BaseConnection $driver
	 * @param object|resource $conn
	 * @return object|resource
	 */
	protected static function pushConnection(string $name, BaseConnection $driver, $conn)
	{
		static::$allConnections[$name] = compact('driver', 'conn');

		return $conn;
	}



	//--------------------------------------------------------------------

	/**
	 * Initializes the database connection/settings.
	 *
	 * @return mixed|void
	 * @throws DatabaseException
	 */
	public function initialize()
	{
		/* If an established connection is available, then there's
		 * no need to connect and select the database.
		 *
		 * Depending on the database driver, conn_id can be either
		 * boolean TRUE, a resource or an object.
		 */
		if ($this->conn)
		{
			return;
		}

		//--------------------------------------------------------------------

        $this->timer->start('database.init');

		// Connect to the database and set the connection ID
		$this->conn = $this->connect($this->pConnect);

        $this->execCommands();

		// No connection resource? Check if there is a failover else throw an error
		if (! $this->conn)
		{
			throw new DatabaseException('Unable to connect to the database.');
		}

		$this->connectDuration = $this->timer->getElapsedTime('database.init');
	}

	//--------------------------------------------------------------------

    /**
     * Execute les commandes sql
     *
     * @return void
     */
    private function execCommands()
    {
        if (!empty($this->conn) AND $this->type === 'pdo')
        {
            foreach ($this->commands AS $command)
            {
                $this->conn->exec($command);
            }
            if ($this->debug === true)
            {
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            if (isset($this->options['column_case']))
            {
                switch (strtolower($this->options['column_case']))
                {
                    case 'lower' :
                        $casse = PDO::CASE_LOWER;
                        break;
                    case 'upper' :
                        $casse = PDO::CASE_UPPER;
                        break;
                    default:
                        $casse = PDO::CASE_NATURAL;
                        break;
                }
                $this->conn->setAttribute(PDO::ATTR_CASE, $casse);
            }
        }
    }

	/**
	 * Connect to the database.
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	abstract public function connect(bool $persistent = false);

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
	 *
	 * @return void
	 */
	public function close()
	{
		if ($this->conn)
		{
			$this->_close();
			$this->conn = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Platform dependent way method for closing the connection.
	 *
	 * @return mixed
	 */
	abstract protected function _close();

	//--------------------------------------------------------------------

	/**
	 * Create a persistent database connection.
	 *
	 * @return mixed
	 */
	public function persistentConnect()
	{
		return $this->connect(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	abstract public function reconnect();

	//--------------------------------------------------------------------

	/**
	 * Returns the actual connection object. If both a 'read' and 'write'
	 * connection has been specified, you can pass either term in to
	 * get that connection. If you pass either alias in and only a single
	 * connection is present, it must return the sole connection.
	 *
	 * @param string|null $alias
	 *
	 * @return mixed
	 */
	public function getConnection(string $alias = null)
	{
		//@todo work with read/write connections
		return $this->conn;
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	abstract public function setDatabase(string $databaseName);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the current database being used.
	 *
	 * @return string
	 */
	public function getDatabase(): string
	{
		return empty($this->database) ? '' : $this->database;
	}

	//--------------------------------------------------------------------

	/**
	 * Set DB Prefix
	 *
	 * Set's the DB Prefix to something new without needing to reconnect
	 *
	 * @param string $prefix The prefix
	 *
	 * @return string
	 */
	public function setPrefix(string $prefix = ''): string
	{
		return $this->prefix = $prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the database prefix.
	 *
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error encountered by this connection.
	 *
	 * @return mixed
	 */
	public function getError()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 *
	 * @return string
	 */
	public function getPlatform(): string
	{
		return $this->driver;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	abstract public function getVersion(): string;


	/**
	 * Executes the query against the database.
	 *
	 * @param string $sql
	 * @param array $params
	 * @return object|resource|null
	 */
	abstract protected function execute(string $sql, array $params = []);

	//--------------------------------------------------------------------

	/**
	 * Performs a basic query against the database. No binding or caching
	 * is performed, nor are transactions handled. Simply takes a raw
	 * query string and returns the database-specific result id.
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function query(string $sql, array $params = [])
	{
		if (empty($this->conn))
		{
			$this->initialize();
		}

	    $this->queryResult = $this->execute($sql, $params);

        return !empty($this->queryResult) ? new Result($this, $this->queryResult) : $this->queryResult;
	}

	//--------------------------------------------------------------------

	/**
	 * Disable Transactions
	 *
	 * This permits transactions to be disabled at run-time.
	 *
	 * @return void
	 */
	public function transOff()
	{
		$this->transEnabled = false;
	}

	//--------------------------------------------------------------------

	/**
	 * Enable/disable Transaction Strict Mode
	 *
	 * When strict mode is enabled, if you are running multiple groups of
	 * transactions, if one group fails all subsequent groups will be
	 * rolled back.
	 *
	 * If strict mode is disabled, each group is treated autonomously,
	 * meaning a failure of one group will not affect any others
	 *
	 * @param boolean $mode = true
	 *
	 * @return $this
	 */
	public function transStrict(bool $mode = true)
	{
		$this->transStrict = $mode;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Start Transaction
	 *
	 * @param  boolean $test_mode = FALSE
	 * @return boolean
	 */
	public function transStart(bool $test_mode = false): bool
	{
		if (! $this->transEnabled)
		{
			return false;
		}

		return $this->transBegin($test_mode);
	}

	//--------------------------------------------------------------------

	/**
	 * Complete Transaction
	 *
	 * @return boolean
	 */
	public function transComplete(): bool
	{
		if (! $this->transEnabled)
		{
			return false;
		}

		// The query() function will set this flag to FALSE in the event that a query failed
		if ($this->transStatus === false || $this->transFailure === true)
		{
			$this->transRollback();

			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of
			// transactions will be permitted.
			if ($this->transStrict === false)
			{
				$this->transStatus = true;
			}

			//            log_message('debug', 'DB Transaction Failure');
			return false;
		}

		return $this->transCommit();
	}

	//--------------------------------------------------------------------

	/**
	 * Lets you retrieve the transaction flag to determine if it has failed
	 *
	 * @return boolean
	 */
	public function transStatus(): bool
	{
		return $this->transStatus;
	}

	/**
	 * Begin Transaction
	 *
	 * @param  boolean $test_mode
	 * @return boolean
	 */
	public function transBegin(bool $test_mode = false): bool
	{
		if (! $this->transEnabled)
		{
			return false;
		}
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->transDepth > 0)
		{
			$this->transDepth ++;
			return true;
		}
		if (empty($this->conn))
		{
			$this->initialize();
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->transFailure = ($test_mode === true);

		if ($this->_transBegin())
		{
			$this->transDepth ++;
			return true;
		}

		return false;
	}
	public function beginTransaction(bool $test_mode = false) : bool
	{
		return $this->transBegin($test_mode);
	}
	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transBegin(): bool;

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	public function transCommit(): bool
	{
		if (! $this->transEnabled OR $this->transDepth === 0)
		{
			return false;
		}
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->transDepth > 1 OR $this->_transCommit())
		{
			$this->transDepth --;
			return true;
		}
		return false;
	}
	public function commit() : bool
	{
		return $this->transCommit();
	}
	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transCommit(): bool;

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	public function transRollback(): bool
	{
		if (! $this->transEnabled OR $this->transDepth === 0)
		{
			return false;
		}
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->transDepth > 1 OR $this->_transRollback())
		{
			$this->transDepth --;
			return true;
		}

		return false;
	}
	public function roolback() : bool
	{
		return $this->transRollback();
	}
	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transRollback(): bool;

	/**
     * Renvoi la dernier requete executée avant la requete courante
     *
     * @return array
     */
    public function getLastQuery() : array
	{
		return $this->last_query;
	}
	public function lastQuery() : array
    {
        return $this->getLastQuery();
    }
	/**
	 * Returns a string representation of the last query's statement object.
	 *
	 * @return string
	 */
	public function showLastQuery(): string
	{
		return $this->last_query['query'] ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the time we started to connect to this database in
	 * seconds with microseconds.
	 *
	 * Used by the Debug Toolbar's timeline.
	 *
	 * @return float|null
	 */
	public function getConnectStart(): ?float
	{
		return $this->connectTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of seconds with microseconds that it took
	 * to connect to the database.
	 *
	 * Used by the Debug Toolbar's timeline.
	 *
	 * @param integer $decimals
	 *
	 * @return string
	 */
	public function getConnectDuration(int $decimals = 6): string
	{
		return number_format($this->connectDuration, $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Query Builder class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @param string|array $item
	 * @param boolean      $prefixSingle
	 * @param boolean      $protectIdentifiers
	 * @param boolean      $fieldExists
	 *
	 * @return string|array
	 */
	public function protectIdentifiers($item, bool $prefixSingle = false, bool $protectIdentifiers = null, bool $fieldExists = true)
	{
		if (! is_bool($protectIdentifiers))
		{
			$protectIdentifiers = $this->protectIdentifiers;
		}

		if (is_array($item))
		{
			$escaped_array = [];
			foreach ($item as $k => $v)
			{
				$escaped_array[$this->protectIdentifiers($k)] = $this->protectIdentifiers($v, $prefixSingle, $protectIdentifiers, $fieldExists);
			}

			return $escaped_array;
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings.
		if (strcspn($item, "()'") !== strlen($item))
		{
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/\s+/', ' ', trim($item));

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ($offset = strripos($item, ' AS '))
		{
			$alias = ($protectIdentifiers) ? substr($item, $offset, 4) . $this->escapeIdentifiers(substr($item, $offset + 4)) : substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		elseif ($offset = strrpos($item, ' '))
		{
			$alias = ($protectIdentifiers) ? ' ' . $this->escapeIdentifiers(substr($item, $offset + 1)) : substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		else
		{
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if (! empty($this->aliasedTables) && in_array($parts[0], $this->aliasedTables))
			{
				if ($protectIdentifiers === true)
				{
					foreach ($parts as $key => $val)
					{
						if (! in_array($val, $this->reservedIdentifiers))
						{
							$parts[$key] = $this->escapeIdentifiers($val);
						}
					}

					$item = implode('.', $parts);
				}

				return $item . $alias;
			}

			// Is there a table prefix defined in the config file? If not, no need to do anything
			if ($this->DBPrefix !== '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset($parts[3]))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ($fieldExists === false)
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if ($this->swapPre !== '' && strpos($parts[$i], $this->swapPre) === 0)
				{
					$parts[$i] = preg_replace('/^' . $this->swapPre . '(\S+?)/', $this->DBPrefix . '\\1', $parts[$i]);
				}
				// We only add the table prefix if it does not already exist
				elseif (strpos($parts[$i], $this->DBPrefix) !== 0)
				{
					$parts[$i] = $this->DBPrefix . $parts[$i];
				}

				// Put the parts back together
				$item = implode('.', $parts);
			}

			if ($protectIdentifiers === true)
			{
				$item = $this->escapeIdentifiers($item);
			}

			return $item . $alias;
		}

		// In some cases, especially 'from', we end up running through
		// protect_identifiers twice. This algorithm won't work when
		// it contains the escapeChar so strip it out.
		$item = trim($item, $this->escapeChar);

		// Is there a table prefix? If not, no need to insert it
		if ($this->prefix !== '')
		{
			// Verify table prefix and replace if necessary
			if ($this->swapPre !== '' && strpos($item, $this->swapPre) === 0)
			{
				$item = preg_replace('/^' . $this->swapPre . '(\S+?)/', $this->prefix . '\\1', $item);
			}
			// Do we prefix an item with no segments?
			elseif ($prefixSingle === true && strpos($item, $this->prefix) !== 0)
			{
				$item = $this->prefix . $item;
			}
		}

		if ($protectIdentifiers === true && ! in_array($item, $this->reservedIdentifiers))
		{
			$item = $this->escapeIdentifiers($item);
		}

		return $item . $alias;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param mixed $item
	 *
	 * @return mixed
	 */
	public function escapeIdentifiers($item)
	{
		if ($this->escapeChar === '' || empty($item) || in_array($item, $this->reservedIdentifiers))
		{
			return $item;
		}
		elseif (is_array($item))
		{
			foreach ($item as $key => $value)
			{
				$item[$key] = $this->escapeIdentifiers($value);
			}

			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		elseif (ctype_digit($item) || $item[0] === "'" || ( $this->escapeChar !== '"' && $item[0] === '"') ||
				strpos($item, '(') !== false
		)
		{
			return $item;
		}

		static $preg_ec = [];

		if (empty($preg_ec))
		{
			if (is_array($this->escapeChar))
			{
				$preg_ec = [
					preg_quote($this->escapeChar[0], '/'),
					preg_quote($this->escapeChar[1], '/'),
					$this->escapeChar[0],
					$this->escapeChar[1],
				];
			}
			else
			{
				$preg_ec[0] = $preg_ec[1] = preg_quote($this->escapeChar, '/');
				$preg_ec[2] = $preg_ec[3] = $this->escapeChar;
			}
		}

		foreach ($this->reservedIdentifiers as $id)
		{
			if (strpos($item, '.' . $id) !== false)
			{
				return preg_replace('/' . $preg_ec[0] . '?([^' . $preg_ec[1] . '\.]+)' . $preg_ec[1] . '?\./i', $preg_ec[2] . '$1' . $preg_ec[3] . '.', $item);
			}
		}

		return preg_replace('/' . $preg_ec[0] . '?([^' . $preg_ec[1] . '\.]+)' . $preg_ec[1] . '?(\.)?/i', $preg_ec[2] . '$1' . $preg_ec[3] . '$2', $item);
	}

	//--------------------------------------------------------------------

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param string $table the table
	 *
	 * @return string
	 * @throws DatabaseException
	 */
	public function prefixTable(string $table = ''): string
	{
		if ($table === '')
		{
			throw new DatabaseException('A table name is required for that operation.');
		}

		return $this->prefix . $table;
	}

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types
	 *
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public function escape($str)
	{
		if (is_array($str))
		{
			$str = array_map([&$this, 'escape'], $str);

			return $str;
		}
		if (is_string($str) || ( is_object($str) && method_exists($str, '__toString')))
		{
			return $this->escapeString($str);
		}
		if (is_bool($str))
		{
			return ($str === false) ? 0 : 1;
		}
		if (is_numeric($str) && $str < 0)
		{
			return "'{$str}'";
		}
		if ($str === null)
		{
			return 'NULL';
		}

		return $str;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param  string|string[] $str  Input string
	 * @param  boolean         $like Whether or not the string will be used in a LIKE condition
	 * @return string|string[]
	 */
	public function escapeString($str, bool $like = false)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escapeString($val, $like);
			}

			return $str;
		}

		$str = $this->_escapeString($str);

		// escape LIKE condition wildcards
		if ($like === true)
		{
			return str_replace([
				$this->likeEscapeChar,
				'%',
				'_',
			], [
				$this->likeEscapeChar . $this->likeEscapeChar,
				$this->likeEscapeChar . '%',
				$this->likeEscapeChar . '_',
			], $str
			);
		}

		return $str;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @param  string|string[]
	 * @return string|string[]
	 */
	public function escapeLikeString($str)
	{
		return $this->escapeString($str, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Platform independent string escape.
	 *
	 * Will likely be overridden in child classes.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		return str_replace("'", "''", remove_invisible_characters($str, false));
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Custom META Methods
	//--------------------------------------------------------------------

	/**
	 * Truncate a table
	 *
	 * @param string $table
	 * @return void
	 */
	public function truncate(string $table)
	{
		$table = $this->prefix.$table;

        if (preg_match('#pgsql#', $this->driver))
        {
            $sql = 'TRUNCATE ' . $table . ' RESTART IDENTITY';
        }
        else if (preg_match('#sqlite#', $this->driver))
        {
            $sql = 'DELETE FROM ' . $table;
        }
        else
        {
            $sql = 'TRUNCATE TABLE ' . $table;
        }

        return $this->query($sql);
	}






	//--------------------------------------------------------------------
	// META Methods
	//--------------------------------------------------------------------

	/**
	 * Create a database
	 *
	 * @param string $dbname
	 * @return int|false
	 */
	public function createDatabase(string $dbname)
	{
		$this->reconnect();
		if (!empty($this->conn) AND $this->type === 'pdo')
        {
			return $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname`;");
		}
	}

	/**
	 * List databases
	 *
	 * @return array|boolean
	 * @throws DatabaseException
	 */
	public function listDatabases()
	{
		// Is there a cached result?
		if (isset($this->dataCache['db_names']))
		{
			return $this->dataCache['db_names'];
		}
		if (preg_match('#sqlite#', $this->driver))
		{
			if ($this->debug)
			{
				throw new DatabaseException('Unsupported feature of the database platform you are using.');
			}
			return false;
		}

		$this->dataCache['db_names'] = [];

		$query = $this->query(preg_match('#mysql#', $this->driver) ? 'SHOW DATABASES' : 'SELECT datname FROM pg_database');
		if ($query === false)
		{
			return $this->dataCache['db_names'];
		}

		for ($i = 0, $query = $query->getAsArray(), $c = count($query); $i < $c; $i ++)
		{
			$this->dataCache['db_names'][] = current($query[$i]);
		}

		return $this->dataCache['db_names'];
	}
	public function databases()
	{
		return $this->listDatabases();
	}

	/**
	 * Returns an array of table names
	 *
	 * @param  boolean $constrainByPrefix = FALSE
	 * @return boolean|array
	 * @throws DatabaseException
	 */
	public function listTables(bool $constrainByPrefix = false)
	{
		// Is there a cached result?
		if (isset($this->dataCache['table_names']) && $this->dataCache['table_names'])
		{
			return $constrainByPrefix ?
				preg_grep("/^{$this->prefix}/", $this->dataCache['table_names'])
				: $this->dataCache['table_names'];
		}

		if (false === ($sql = $this->_listTables($constrainByPrefix)))
		{
			if ($this->debug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}
			return false;
		}

		$this->dataCache['table_names'] = [];
		$query                          = $this->query($sql);

		foreach ($query->getAsArray() as $row)
		{
			// Do we know from which column to get the table name?
			if (! isset($key))
			{
				if (isset($row['table_name']))
				{
					$key = 'table_name';
				}
				elseif (isset($row['TABLE_NAME']))
				{
					$key = 'TABLE_NAME';
				}
				else
				{
					/* We have no other choice but to just get the first element's key.
					 * Due to array_shift() accepting its argument by reference, if
					 * E_STRICT is on, this would trigger a warning. So we'll have to
					 * assign it first.
					 */
					$key = array_keys($row);
					$key = array_shift($key);
				}
			}

			$this->dataCache['table_names'][] = $row[$key];
		}

		return $this->dataCache['table_names'];
	}
	public function tables()
	{
		return $this->listTables();
	}

	//--------------------------------------------------------------------

	/**
	 * Determine if a particular table exists
	 *
	 * @param  string $tableName
	 * @return boolean
	 */
	public function tableExists(string $tableName): bool
	{
		return in_array($this->protectIdentifiers($this->prefixTable($tableName), true, false, false), $this->listTables());
	}
	public function tableExist(string $table) : bool
	{
		return $this->tableExists($table);
	}

	//--------------------------------------------------------------------

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
				throw new DatabaseException('This feature is not available for the database you are using.');
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
	public function columnsName(string $table)
	{
		return $this->getFieldNames($table);
	}

	//--------------------------------------------------------------------

	/**
	 * Determine if a particular field exists
	 *
	 * @param  string $fieldName
	 * @param  string $tableName
	 * @return boolean
	 */
	public function fieldExists(string $fieldName, string $tableName): bool
	{
		return in_array($fieldName, $this->getFieldNames($tableName));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param  string $table the table name
	 * @return array|false
	 */
	public function getFieldData(string $table)
	{
		$fields = $this->_fieldData($this->protectIdentifiers($table, true, false, false));

		return $fields ?? false;
	}
	public function columns(string $table)
	{
		return $this->getFieldData($table);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an object with key data
	 *
	 * @param  string $table the table name
	 * @return array|false
	 */
	public function getIndexData(string $table)
	{
		$fields = $this->_indexData($this->protectIdentifiers($table, true, false, false));

		return $fields ?? false;
	}
	public function indexes(string $table, ?string $type = null)
	{
		$indexes = $this->getIndexData($table);
		if (empty($type))
		{
			return $indexes;
		}
		return $indexes[strtoupper($type)] ?? false;
	}
	/**
	 * Platform-specific index data.
	 *
	 * @param  string $table
	 * @see    getIndexData()
	 * @return array
	 */
	abstract protected function _indexData(string $table): array;

	//--------------------------------------------------------------------

	/**
	 * Returns an object with foreign key data
	 *
	 * @param  string $table the table name
	 * @return array|false
	 */
	public function getForeignKeyData(string $table)
	{
		$fields = $this->_foreignKeyData($this->protectIdentifiers($table, true, false, false));

		return $fields ?? false;
	}
	public function foreignKeys(string $table)
	{
		return $this->getForeignKeyData($table);
	}

	//--------------------------------------------------------------------

	/**
	 * Disables foreign key checks temporarily.
	 */
	public function disableForeignKeyChecks()
	{
		$sql = $this->_disableForeignKeyChecks();

		return $this->query($sql);
    }
	public function disableFk()
	{
		return $this->disableForeignKeyChecks();
	}
    /**
	 * Returns platform-specific SQL to disable foreign key checks.
	 *
	 * @return string
	 */
	abstract protected function _disableForeignKeyChecks();

	//--------------------------------------------------------------------

	/**
	 * Enables foreign key checks temporarily.
	 */
	public function enableForeignKeyChecks()
	{
		$sql = $this->_enableForeignKeyChecks();

		return $this->query($sql);
	}
	public function enableFk()
	{
		return $this->enableForeignKeyChecks();
	}
    /**
	 * Returns platform-specific SQL to disable foreign key checks.
	 *
	 * @return string
	 */
    abstract protected function _enableForeignKeyChecks();

	//--------------------------------------------------------------------

	/**
	 * Allows the engine to be set into a mode where queries are not
	 * actually executed, but they are still generated, timed, etc.
	 *
	 * This is primarily used by the prepared query functionality.
	 *
	 * @param boolean $pretend
	 *
	 * @return $this
	 */
	public function pretend(bool $pretend = true)
	{
		$this->pretend = $pretend;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Empties our data cache. Especially helpful during testing.
	 *
	 * @return $this
	 */
	public function resetDataCache()
	{
		$this->dataCache = [];

		return $this;
	}

	/**
	 * Returns the last error code and message.
	 *
	 * Must return an array with keys 'code' and 'message':
	 *
	 *  return ['code' => null, 'message' => null);
	 *
	 * @return array
	 */
	public function error(): array
	{
        return $this->error;
	}

	//--------------------------------------------------------------------

	/**
     * Return the last id generated by autoincrement
     *
     * @return integer|null
     */
    public function lastId() : ?int
    {
        return $this->insertID();
    }
	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	abstract public function insertID(): int;

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return int
	 */
	abstract public function affectedRows(): int;

	/**
     * Returns the number of rows in the result set.
     *
     * @return integer
	 */
	abstract public function numRows(): int;



	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param boolean $constrainByPrefix
	 *
	 * @return string
	 */
	abstract protected function _listTables(bool $constrainByPrefix = false): string;

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	abstract protected function _listColumns(string $table = ''): string;

	//--------------------------------------------------------------------

	/**
	 * Platform-specific field data information.
	 *
	 * @param  string $table
	 * @see    getFieldData()
	 * @return array
	 */
	abstract protected function _fieldData(string $table): array;

	//--------------------------------------------------------------------



	//--------------------------------------------------------------------

	/**
	 * Platform-specific foreign keys data.
	 *
	 * @param  string $table
	 * @see    getForeignKeyData()
	 * @return array
	 */
	abstract protected function _foreignKeyData(string $table): array;

	//--------------------------------------------------------------------

	/**
	 * Accessor for properties if they exist.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if (property_exists($this, $key))
		{
			return $this->$key;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Checker for properties existence.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset(string $key): bool
	{
		return property_exists($this, $key);
	}

	//--------------------------------------------------------------------

}
