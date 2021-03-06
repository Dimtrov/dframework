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

namespace dFramework\core\db;

use dFramework\core\exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Query
 *
 * General Query class of system
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/db/Query.php
 */
class Query
{    
    protected $sql;

    protected $db_group;

    /**
     * @var Database
     */
    public $db;

    
    protected $query_details = [];
    protected $stats = [];

    protected $cache;
    protected $cache_type = 'file';
    protected $cache_file_dir = RESOURCE_DIR.'database'.DS.'cache'.DS;

    protected static $db_types = [
        'pdo', 'mysqli', 'pgsql', 'sqlite3'
    ];
    protected static $cache_types = [
        'memcached', 'memcache', 'xcache', 'file'
    ];

    public $last_query;
    public $is_cached = false;
    public $key_prefix = '';

    
    public function __construct(string $db_group = 'default')
    {
        $this->db_group = $db_group;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'db_config') 
        {
            return Database::instance()->use($this->db_group)->config();
        }
    }

    /**
     * Connection a la base de donnees
     *
     * @return self
     */
    private function dbConnect() : self
    {
        if (empty($this->db))
        {
            $this->db = Database::instance()->use($this->db_group);
        }

        return $this;
    }

    /**
     * Retourne l'instance de connexion a la base de donnees (PDO)
     *
     * @return mixed
     */
    public function db()
    {
        return $this->dbConnect()->db->connection();
    }

    /**
     * Renvoi les details de le requete courante
     *
     * @return array
     */
    public function details()
    {
        return $this->query_details;
    }
    /**
     * Renvoi le dernier id generé par autoincrement
     *
     * @return integer|null
     */
    public function lastId() : ?int
    {
        return $this->query_details['insert_id'] ?? null;
    }
    /**
     * Renvoi le nombre de ligne affecté par la requete
     *
     * @return integer
     */
    public function affectedRow() : int
    {
        return $this->query_details['affected_row'] ?? 0;
    }
    /**
     * Renvoi le nombre de ligne retourné par la requete
     *
     * @return integer
     */
    public function numRows() : int
    {
        return $this->query_details['num_rows'] ?? 0;
    }
    /**
     * Renvoi la dernier requete executée avant la requete courante
     * 
     * @return string
     */
    public function lastQuery()
    {
        return $this->last_query;
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
                $this->stats['num_rows'] += $query['rows'];
                $this->stats['num_changes'] += $query['changes'];
            }
        }

        $this->stats['avg_query_time'] =
            $this->stats['total_time'] /
            (float)(($this->stats['num_queries'] > 0) ? $this->stats['num_queries'] : 1);

        return $this->stats;
    }  

    public function exec(string $statement, bool $getResult = true)
    {
        if ($getResult)
        {
            return $this->query($statement)->result();
        }
        return $this->query($statement)->execute();
    }

    /**
     * Gets the SQL statement.
     *
     * @return string SQL statement
     */
    public function sql() : ?string 
    {
        return $this->sql;
    }
    /**
     * Set the SQL statement.
     *
     * @param string|array SQL statement
     * @return self
     */
    public function query($sql) : self
    {
        $this->sql = trim(
            (is_array($sql)) ?
                array_reduce($sql, [$this, 'build']) :
                $sql
        );

        return $this;
    }
    /**
     * Joins string tokens into a SQL statement.
     *
     * @param string $sql SQL statement
     * @param string $input Input string to append
     * @return string New SQL statement
     */
    public function build(?string $sql, ?string $input) : string
    {
        return (strlen($input) > 0) ? ($sql.' '.$input) : $sql;
    }


    /**
     * Executes a sql statement.
     *
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return object Query results object
     * @throws Exception When database is not defined
     */
    public function execute($key = null, $expire = 0) 
    {
        $this->dbConnect();

        if (!$this->db) 
        {
            throw new DatabaseException('Database is not defined.');
        }

        if ($key !== null) 
        {
            $result = $this->fetch($key);

            if ($this->is_cached) 
            {
                return $result;
            }
        }

        $result = null;

        $this->is_cached = false;
        $this->query_details['num_rows'] = 0;
        $this->query_details['affected_rows'] = 0;
        $this->query_details['insert_id'] = -1;
        $this->last_query = $this->sql;

        if ($this->db->config('options.enable_stats')) 
        {
            if (empty($this->stats)) 
            {
                $this->stats = [
                    'queries' => []
                ];
            }

            $this->query_details['time'] = microtime(true);
        }

        if (!empty($this->sql)) 
        {
            $error = null;

            switch ($this->db->type()) 
            {
                case 'pdo':
                    try {
                        $result = $this->db()->prepare($this->sql);

                        if (!$result) 
                        {
                            $error = $this->db()->errorInfo();
                        }
                        else 
                        {
                            $result->execute();

                            $this->query_details['num_rows'] = $result->rowCount();
                            $this->query_details['affected_rows'] = $result->rowCount();
                            $this->query_details['insert_id'] = $this->db()->lastInsertId();
                        }
                    }
                    catch (PDOException $ex) {
                        $error = $ex->getMessage();
                    }
                    break;

                case 'mysqli':
                    $result = $this->db()->query($this->sql);

                    if (!$result) 
                    {
                        $error = $this->db()->error;
                    }
                    else 
                    {
                        if (is_object($result)) 
                        {
                            $this->query_details['num_rows'] = $result->num_rows;
                        }
                        else 
                        {
                            $this->query_details['affected_rows'] = $this->db()->affected_rows;
                        }
                        $this->query_details['insert_id'] = $this->db()->insert_id;
                    }
                    break;

                case 'pgsql':
                    $result = pg_query($this->db(), $this->sql);

                    if (!$result) 
                    {
                        $error = pg_last_error($this->db());
                    }
                    else 
                    {
                        $this->query_details['num_rows'] = pg_num_rows($result);
                        $this->query_details['affected_rows'] = pg_affected_rows($result);
                        $this->query_details['insert_id'] = pg_last_oid($result);
                    }
                    break;

                case 'sqlite3':
                    $result = $this->db()->query($this->sql);

                    if ($result === false) 
                    {
                        $error = $this->db()->lastErrorMsg();
                    }
                    else 
                    {
                        $this->query_details['num_rows'] = 0;
                        $this->query_details['affected_rows'] = ($result) ? $this->db()->changes() : 0;
                        $this->query_details['insert_id'] = $this->db()->lastInsertRowId();
                    }
                    break;
            }

            if ($error !== null) 
            {
                $error .= "\nSQL: ".$this->sql;
                throw new DatabaseException('Database error: '.$error);
            }
        }

        if ($this->db->config('options.enable_stats')) 
        {
            $time = microtime(true) - $this->query_details['time'];
            $this->stats['queries'][] = [
                'query' => $this->sql,
                'time' => $time,
                'rows' => (int) $this->query_details['num_rows'],
                'changes' => (int) $this->query_details['affected_rows']
            ];
        }

        return $result;
    }

    /**
     * Fetch multiple rows from a select query.
     *
     * @param int|string $fetch_mode
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return array Rows
     */
    public function result($fetch_mode = PDO::FETCH_OBJ, $key = null, $expire = 0) : array
    {
        $fetch_mode = empty($fetch_mode) ? PDO::FETCH_OBJ : $fetch_mode;

        if (empty($this->sql)) 
        {
            throw new DatabaseException("Empty SQL statement");
        }

        $data = [];
        $result = $this->execute($key, $expire);

        if ($this->is_cached) 
        {
            $data = $result;

            if (true === $this->db->config('options.enable_cache')) 
            {
                $this->stats['cached'][$this->key_prefix.$key] = $this->sql;
            }
        }
        else 
        {
            switch ($this->db->type()) {
                case 'pdo':
                    if (is_int($fetch_mode))
                    {
                        $result->setFetchMode($fetch_mode);
                        $data = $result->fetchAll();
                    }
                    else if (is_string($fetch_mode))
                    {
                        $records = $result->fetchAll(PDO::FETCH_ASSOC);
                        $data = [];

                        foreach ($records As $key => $value)
                        {
                            if (!isset($data[$key]))
                            {
                                $data[$key] = Hydrator::hydrate($value, $fetch_mode);
                            }
                        }
                    }
                    $this->query_details['num_rows'] = count($data);
                    break;
    
                case 'mysqli':
                    if (function_exists('mysqli_fetch_all')) 
                    {
                        $data = $result->fetch_all(MYSQLI_ASSOC);
                    }
                    else 
                    {
                        while ($row = $result->fetch_assoc()) {
                            $data[] = $row;
                        }
                    }
                    $result->close();
                    break;
               
                case 'pgsql':
                    $data = pg_fetch_all($result);
                    pg_free_result($result);
                    break;
    
                case 'sqlite3':
                    if ($result) 
                    {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) 
                        {
                            $data[] = $row;
                        }
                        $result->finalize();
                        $this->num_rows = sizeof($data);
                    }
                    break;
            }
        }
    
        if (!$this->is_cached AND $key !== null) 
        {
            $this->store($key, $data, $expire);
        }
    
        return $data;
    }

    /**
     * Wraps quotes around a string and escapes the content for a string parameter.
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     */
    public function quote($value) 
    {
        if ($value === null) 
        {
            return 'NULL';
        }

        if (is_string($value)) 
        {
            if ($this->db !== null) 
            {
                switch ($this->db->type()) 
                {
                    case 'pdo':
                        return $this->db()->quote($value);

                    case 'mysqli':
                        return "'".$this->db()->real_escape_string($value)."'";

                    case 'pgsql':
                        return "'".pg_escape_string($this->db, $value)."'";

                    case 'sqlite3':
                        return "'".$this->db()->escapeString($value)."'";
                }
            }

            $value = str_replace(
                array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
                $value
            );

            return "'$value'";
        }

        return $value;
    }


    /*** Cache Methods ***/

    /**
     * Stores a value in the cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @param int $expire Expiration time in seconds
     */
    public function store($key, $value, $expire = 0) 
    {
        $key = $this->key_prefix.$key;

        switch ($this->cache_type) 
        {
            case 'memcached':
                $this->cache->set($key, $value, $expire);
                break;

            case 'memcache':
                $this->cache->set($key, $value, 0, $expire);
                break;

            case 'apc':
                if (function_exists('apc_store')) 
                {
                    @apc_store($key, $value, $expire);
                }
                break;

            case 'xcache':
                if (function_exists('xcache_set'))
                {
                    @xcache_set($key, $value, $expire);
                }
                break;

            case 'file':
                $file = $this->cache_file_dir.md5($key);
                $data = [
                    'value' => $value,
                    'expire' => ($expire > 0) ? (time() + $expire) : 0
                ];
                file_put_contents($file, serialize($data));
                break;

            default:
                $this->cache[$key] = $value;
        }
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key Cache key
     * @return mixed Cached value
     */
    public function fetch($key) 
    {
        $key = $this->key_prefix.$key;

        switch ($this->cache_type) 
        {
            case 'memcached':
                $value = $this->cache->get($key);
                $this->is_cached = ($this->cache->getResultCode() == Memcached::RES_SUCCESS);
                return $value;

            case 'memcache':
                $value = $this->cache->get($key);
                $this->is_cached = ($value !== false);
                return $value;

            case 'apc':
                return apc_fetch($key, $this->is_cached);

            case 'xcache':
                $this->is_cached = xcache_isset($key);
                return xcache_get($key);

            case 'file':
                $file = $this->cache.'/'.md5($key);

                if ($this->is_cached = file_exists($file)) {
                    $data = unserialize(file_get_contents($file));
                    if ($data['expire'] == 0 || time() < $data['expire']) {
                        return $data['value'];
                    }
                    else {
                        $this->is_cached = false;
                    }
                }
                break;

            default:
                return $this->cache[$key];
        }
        return null;
    }

    /**
     * Clear a value from the cache.
     *
     * @param string $key Cache key
     * @return object Self reference
     */
    public function clear($key) 
    {
        $key = $this->key_prefix.$key;

        switch ($this->cache_type) 
        {
            case 'memcached':
                return $this->cache->delete($key);

            case 'memcache':
                return $this->cache->delete($key);

            case 'apc':
                if (function_exists('apc_delete'))
                {
                    return pc_delete($key);
                }

            case 'xcache':
                if (function_exists('xcache_unset'))
                {
                    return xcache_unset($key);
                }

            case 'file':
                $file = $this->cache_file_dir.md5($key);
                if (file_exists($file)) 
                {
                    return unlink($file);
                }
                return false;

            default:
                if (isset($this->cache[$key])) {
                    unset($this->cache[$key]);
                    return true;
                }
                return false;
        }
    }

    /**
     * Flushes out the cache.
     */
    public function flush() 
    {
        switch ($this->cache_type) 
        {
            case 'memcached':
                $this->cache->flush();
                break;

            case 'memcache':
                $this->cache->flush();
                break;

            case 'apc':
                apc_clear_cache();
                break;

            case 'xcache':
                xcache_clear_cache();
                break;

            case 'file':
                if ($handle = opendir($this->cache_file_dir)) 
                {
                    while (false !== ($file = readdir($handle)))
                    {
                        if ($file != '.' AND $file != '..') 
                        {
                            unlink($this->cache.'/'.$file);
                        }
                    }
                    closedir($handle);
                }
                break;

            default:
                $this->cache = [];
                break;
        }
    }
}
