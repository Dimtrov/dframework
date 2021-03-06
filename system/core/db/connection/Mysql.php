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

use dFramework\core\Config;
use dFramework\core\exception\DatabaseException;
use dFramework\core\utilities\Tableau;
use InvalidArgumentException;
use mysqli;
use PDO;
use SQLite3;

/**
 * Mysql
 *
 * Make a mysql database connection
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Connection
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/connection/Mysql.php
 */
class Mysql
{
    public $config = [];

    private $db_selected = '';
    
    private $db;

    private $db_type;

    private $already_initialize = false;


    public static function instance() : self
    {
        if (null === self::$_instance) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private static $_instance;

    /**
     * Connecte la bd et renvoie l'instance de pdo
     *
     * @param string $db_group
     * @param boolean $shared
     * @return object
     */
    public static function connect(string $db_group = 'default', bool $shared = true) : object
    {
        $db =  true === $shared ? self::instance() : new self;

        return $db->use($db_group)->connection();
    }

    public function use(string $db_selected) : self
    {
        if ($db_selected !== $this->db_selected)
        {
            Config::load('database');
            $this->db_selected = strtolower($db_selected);
            $this->config = (array) Config::get('database.'.$this->db_selected);
            
            $this->checkConfig();
        }
        
        return $this;
    }
    
    /**
     * Return instance of database connexion
     *
     * @return object
     */
    public function connection() : object
    {
        return $this->db;
    }

    public function type()
    {
        return $this->db_type;
    }

    public function config(?string $key = null)
    {
        if (empty($key))
        {
            return $this->config;
        }
        return Tableau::get_recusive($this->config, $key);
    }


    /**
     * Check if the configuration information of the database is correct
     */
    private function checkConfig()
    {
        $dbs = $this->db_selected;
        $config = $this->config ?? null;

        if (empty($config) OR !is_array($config))
        {
            DatabaseException::except('
                The <b>'.$dbs.'</b> database configuration is required. <br>
                Please open the "'.Config::$_config_file['database'].'" file or use &laquo; Database::setConfig &raquo; to correct it
            ');
        }
        $keys = ['driver','port','host','username','password','database','charset'];

        foreach ($keys As $key)
        {
            if (!array_key_exists($key, $config))
            {
                DatabaseException::except('
                    The <b>'.$key.'</b> key of the '.$dbs.' database configuration don\'t exist. <br>
                    Please fill it in array $config["database"]["'.$dbs.'"] of the file  &laquo; '.Config::$_config_file['database'].' &raquo or use &laquo; Database::setConfig &raquo; 
                ');
            }
        }

        foreach ($config As $key => $value)
        {
            if (!in_array($key, ['password', 'options','prefix', 'debug']) AND empty($value)) 
			{
                DatabaseException::except('
                    The <b>' . $key . '</b> key of ' . $dbs . ' database configuration must have a valid value. <br>
                    Please correct it in array $config["database"]["'.$dbs.'"] of the file  &laquo; ' . Config::$_config_file['database'] . ' &raquo or use &laquo; Database::setConfig &raquo; 
                ');
            }
        }

        $config['debug'] = $this->autoValue('debug', '[debug]');

        $config['options']['enable_stats'] = $this->autoValue('options.enable_stats', '[options][enable_stats]');

        $config['options']['enable_cache'] = $this->autoValue('options.enable_cache', '[options][enable_cache]');

        $this->config = $config;

        $this->initialize();
    }

    /**
     * Definit automatiquement la valeur d'une configuration en fonction de l'environnement
     *
     * @param string $key
     * @param string $label
     * @return boolean
     */
    private function autoValue(string $key, string $label) : bool
    {
        $value = $this->config($key);
        if (empty($value))
        {
            $value = 'auto';
        }
        
        if (!in_array($value, ['auto', true, false]))
        {
            DatabaseException::except('
                The <b>database['.$this->db_selected.']'.$label.'</b> configuration is not set correctly (Accept values: auto/true/false). 
                <br>
                Please edit &laquo; '.Config::$_config_file['database'].' &raquo; file  or use &laquo; Database::setConfig &raquo; to correct it
            ');
        }
        else if($value === 'auto')
        {
            $value = (Config::get('general.environment') === 'dev');
        }

        return (bool) $value;
    }

    /**
     * Initializes the access parameters to the database
     */
    private function initialize()
    {
        $db = $this->parse_config();

        $db['dbname'] = $db['database'];
        $commands = [];

        switch (strtolower($db['driver'])) 
        {
            case 'mysqli':
                $this->db = new mysqli(
                    $db['host'],
                    $db['username'],
                    $db['password'],
                    $db['database'],
                    $db['port']
                );

                if ($this->db->connect_error) 
                {
                    throw new DatabaseException('Connection error: '.$this->db->connect_error);
                }

                break;

            case 'pgsql':
                $str = sprintf(
                    'host=%s port=%s dbname=%s user=%s password=%s',
                    $db['host'],
                    $db['port'],
                    $db['database'],
                    $db['username'],
                    $db['password']
                );

                $this->db = pg_connect($str);

                break;

            case 'sqlite3':
                $this->db = new SQLite3($db['database']);

                break;

            case 'pdomysql':
            case 'pdo_mysql':
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s',
                    $db['host'],
                    isset($db['port']) ? $db['port'] : 3306,
                    $db['database']
                );

                $this->db = new PDO($dsn, $db['username'], $db['password']);
                $commands[] = 'SET SQL_MODE=ANSI_QUOTES';

                break;

            case 'pdopgsql':
            case 'pdo_pgsql':
                $dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                    $db['host'],
                    isset($db['port']) ? $db['port'] : 5432,
                    $db['database'],
                    $db['username'],
                    $db['password']
                );

                $this->db = new PDO($dsn);
                break;

            case 'pdosqlite':
            case 'pdo_sqlite':
                $this->db = new PDO('sqlite:/'.$db['database']);
                break;
            
            default:
                throw new InvalidArgumentException('Unsupported PDO driver: {<b>'.$db['driver'].'</b>}');
                
            break;
        }

        if ($this->db == null) {
            throw new DatabaseException('Undefined database.');
        }
        
        $this->db_type = strpos($db['driver'], 'pdo') !== false ? 'pdo' : $db['driver'];

        if (preg_match('#(mysql|pgsql)$#i', $db['driver']) AND isset($db['charset']))
        {
            $commands[] = "SET NAMES '{$db['charset']}'" . (
                (preg_match('#mysql$#i', $db['driver']) AND isset($db['collation'])) ?
                    " COLLATE '{$db['collation']}'" : ''
            );
        }

        if ($this->db_type === 'pdo')
        {
            foreach ($commands As $value)
            {
                $this->db->exec($value);
            }
            if (isset($db['debug']) AND $db['debug'] === true)
            {
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            if (isset($db['options']['column_case']))
            {
                switch (strtolower($db['options']['column_case']))
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
                $this->db->setAttribute(PDO::ATTR_CASE, $casse);
            }
        }
    }

    /**
    * Parse database configuration and use the correct key if we are in dev/prod environment
    *
    * @return array
	*/
	private function parse_config() : array
	{
		$config = $this->config;
		foreach ($config As $key => $value)
		{
			if (is_string($value) AND !in_array($key, ['options', 'debug'])) 
			{
				$tmp = explode('|', $value);
                if (preg_match('#^prod(uction)?$#i', Config::get('general.environment'))) 
                {
					$config[$key] = $tmp[1] ?? $tmp[0];
				}
                else 
                {
					$config[$key] = $tmp[0];
				}
			}
        }
        
		return $config;
	}
}