<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

 
namespace dFramework\core\db;

use dFramework\core\Config;
use dFramework\core\exception\DatabaseException;
use dFramework\core\exception\Exception;
use InvalidArgumentException;
use PDO;
use PDOException;


/**
 * Database
 *
 * Initialise a database process of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/db/Database.php
 */

class Database
{
    public $config = [];

    private $db_selected = 'default';


    private static $_instance = null;
    /**
     * @return self
     */
    public static function getInstance()
    {
        if(is_null(self::$_instance))
        {
            $class = ucfirst(__CLASS__);
            self::$_instance = new $class();
        }
        return self::$_instance;
    }


    /**
     * Database constructor.
     * @param string $db_setting Database configuration that you want to use
     * @throws DatabaseException
     */
    public function __construct(string $db_setting = 'default')
    {
        $this->use($db_setting);
    }

    /**
     * @param string $db_setting Database configuration that you want to use
     * @throws DatabaseException
     */
    public function use(string $db_setting)
    {
        Config::load('database');
        
        $this->db_selected = strtolower($db_setting);
        $this->config = (array) Config::get('database.'.$this->db_selected);
        $this->check();
    }

    /**
     * Check if the configuration information of the database is correct
     */
    private function check()
    {
        $dbs = $this->db_selected;
        $config = $this->config ?? null;

        if(empty($config) OR !is_array($config))
        {
            DatabaseException::except('
                The <b>'.$dbs.'</b> database configuration is required. <br>
                Please open the "'.Config::$_config_file['database'].'" file to correct it
            ');
        }
        $keys = ['dbms','port','host','username','password','database','charset'];

        foreach ($keys As $key)
        {
            if(!array_key_exists($key, $config))
            {
                DatabaseException::except('
                    The <b>'.$key.'</b> key of the '.$dbs.' database configuration don\'t exist. <br>
                    Please fill it in array $config["database"]["'.$dbs.'"] of the file  &laquo; '.Config::$_config_file['database'].' &raquo
                ');
            }
        }

        foreach ($config As $key => $value)
        {
            if(!in_array($key, ['password','options','prefix', 'debug']) AND empty($value)) 
			{
                DatabaseException::except('
                    The <b>' . $key . '</b> key of ' . $dbs . ' database configuration must have a valid value. <br>
                    Please correct it in array $config["database"]["'.$dbs.'"] of the file  &laquo; ' . Config::$_config_file['database'] . ' &raquo
                ');
            }
        }

        $dbms = (strtolower($config['dbms']) === 'mariadb') ? 'mysql' : strtolower($config['dbms']);
        if(!in_array($dbms, ['mysql','oracle','sqlite','sybase']))
        {
            DatabaseException::except('
                The DBMS (<b>'.$dbms.'</b>) you entered for '.$dbs.' database is not supported by dFramework. <br>
                Please correct it in array $config["database"]["'.$dbs.'"] of the file  &laquo; ' . Config::$_config_file['database'] . ' &raquo
            ');
        }

        $config['debug'] = $config['debug'] ?? 'auto';
        if(!in_array($config['debug'], ['auto', true, false]))
        {
            DatabaseException::except('
                The <b>database['.$dbs.'][debug]</b> configuration is not set correctly (Accept values: auto/true/false). 
                <br>
                Please edit &laquo; '.Config::$_config_file['database'].' &raquo; file to correct it
            ');
        }
        else if($config['debug'] === 'auto')
        {
            $this->config['debug'] = (Config::get('general.environment') === 'dev');
        }

        $this->initialize();
    }

    /**
     * Initializes the access parameters to the database
     */
    private function initialize()
    {
        $config = $this->parse_config();

        $config['dbname'] = $config['database'];
        switch (strtolower($config['dbms']))
        {
            case 'mysql':
            case 'mariadb':
                $config['driver'] = 'mysql';
                $config['commands'][] = 'SET SQL_MODE=ANSI_QUOTES';
                break;
            case 'pgsql':
                $config['driver'] = 'pgsql';
                break;
            case 'sybase':
                $config['driver'] = 'dblib';
                break;
            case 'oracle':
                $config['driver'] = 'oci';
                $config['dbname'] = '//' .$config['host']. ':' .$config['port']. '/' .$config['database'];
                break;
            case 'sqlite':
                $config['driver'] = 'sqlite';
                break;
        }
        if (!in_array($config['driver'], PDO::getAvailableDrivers()))
        {
            throw new InvalidArgumentException('Unsupported PDO driver: {<b>'.$config['driver'].'</b>}');
        }

        $stack = [];
        foreach ($config As $key => $value)
        {
            if(!in_array($key, ['driver', 'dbms', 'username', 'password', 'database', 'debug', 'prefix', 'commands', 'options']))
            {
                $stack[] = is_int($key) ? $value : strtolower($key) . '=' . $value;
            }
        }
        if (in_array(strtolower($config['dbms']), ['mysql', 'pgsql', 'sybase']) AND isset($config['charset']))
        {
            $config['commands'][] = "SET NAMES '{$config['charset']}'" . (
                (strtolower($config['dbms']) === 'mysql' AND isset($config['collation'])) ?
                    " COLLATE '{$config['collation']}'" : ''
            );
        }
        $config['dsn'] = $config['driver'] . ':' . implode(';', $stack);
        $config['options'] = (isset($config['options']) AND is_array($config['options'])) ? $config['options'] : [];
        $config['debug'] = (isset($config['debug']) AND is_bool($config['debug'])) ? $config['debug'] : false;

        $this->config = $config;
    }

	/**
	* Parse database configuration and use the correct key if we are in dev/prod environment
	*/
	private function parse_config()
	{
		$config = $this->config;
		foreach($config As $key => $value)
		{
			if(is_string($value) AND !in_array($key, ['options', 'debug'])) 
			{
				$tmp = explode('|', $value);
				if(preg_match('#^prod(uction)?$#i', Config::get('general.environment'))) {
					$config[$key] = $tmp[1] ?? $tmp[0];
				}
				else {
					$config[$key] = $tmp[0];
				}
			}
		}
		return $config;
	}
	
	
    /**
     * @var array
     */
    private $pdo = [null, null];

    /**
     * @param bool $select_db
     * @return mixed
     */
    public function pdo(bool $select_db = true)
    {
        $select_db = intval($select_db);
        if($this->pdo[$select_db] === null)
        {
            $config = $this->config;
            if ($select_db === 0)
            {
                $config['dsn'] = preg_replace('#;?dbname=(.+);?#i', '', $config['dsn']);
            }
            try
            {
                $this->pdo[$select_db] = new PDO($config['dsn'], $config['username'], $config['password']);

                foreach ($config['commands'] As $value)
                {
                    $this->pdo[$select_db]->exec($value);
                }
                if (isset($config['debug']) AND $config['debug'] === true)
                {
                    $this->pdo[$select_db]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                if (isset($config['options']['column_case']))
                {
                    switch (strtolower($config['options']['column_case']))
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
                    $this->pdo[$select_db]->setAttribute(PDO::ATTR_CASE, $casse);
                }
            }
            catch (PDOException $e) {
                Exception::Throw($e);
            }
        }
        return $this->pdo[$select_db];
    }
}