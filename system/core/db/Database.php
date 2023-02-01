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
 * @version     3.4.1
 */

namespace dFramework\core\db;

use dFramework\core\Config;
use dFramework\core\db\connection\BaseConnection;
use dFramework\core\db\connection\Mysql;
use dFramework\core\db\connection\Pgsql;
use dFramework\core\db\connection\Sqlite;
use dFramework\core\db\dump\BaseDump;
use dFramework\core\db\dump\Mysql as DumpMysql;
use dFramework\core\utilities\Arr;
use dFramework\core\exception\DatabaseException;

/**
 * Database
 *
 * Initialize a database process of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/db/Database.php
 */
class Database
{
    /**
     * @const array methodes autorisees pour la facade
     */
    const allowedFacadeMethods = [
        'databases', 'tables', 'tableExist', 'columns', 'columnsName',
        'query', 'truncate',
        'foreignKeys', 'enableFk', 'disableFk', 'indexes',
        'lastId', 'insertID', 'affectedRows',
        'beginTransaction', 'commit', 'rollback'
    ];

    /**
     * @var array
     */
    private $config;
	/**
	 * @var array
	 */
	private $customConfig = [];

    /**
     * @var string
     */
    private $group = null;

    /**
     * @var BaseConnection[]
     */
    private $connections = [];

    /**
     * @var BaseDump[]
     */
    private $dumpers = [];

    /**
     * @var self
     */
    private static $_instance = null;


    public function __construct(?string $group = null, array $customConfig = [])
    {
        $this->group = $group;
		$this->customConfig = $customConfig;
    }
    public static function instance(?string $group = null, array $customConfig = []) : self
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self($group, $customConfig);
        }
        return self::$_instance;
    }

    /**
     * Connecte la base de donnees
     *
     * @param string|null $group
     * @param boolean $shared
     * @return BaseConnection
     */
    public static function connect(?string $group = null, bool $shared = true) : BaseConnection
    {
        if (true === $shared)
        {
            return self::instance($group)->connection();
        }
        return (new self($group))->connection($group);
    }

	/**
	 * Modifie le groupe de connection auquel on souhaite se connecter
	 *
	 * @param string|null $group
	 * @return self
	 */
    public function setGroup(?string $group) : self
    {
        $this->group = $group;
        return $this;
    }

	/**
	 * Recupere le groupe de connection auquel on est actuellement connecter
	 *
	 * @return string
	 */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Verifie si le groupe de la bd est identique a celui fourni
     *
     * @param string $group
     * @return boolean
     */
    public function isGroup(string $group) : bool
    {
        return $this->group === $group;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::execFacade($name, $arguments);
    }
    public function __call($name, $arguments)
    {
        return self::execFacade($name, $arguments);
    }
    private static function execFacade($name, $arguments)
    {
        $connection = self::instance()->connection();
        if (in_array($name, self::allowedFacadeMethods) AND method_exists($connection, $name))
        {
            return call_user_func_array([$connection, $name], $arguments);
        }
        return false;
    }

    /**
     * Verifie si on utilise une connexion pdo ou pas
     *
     * @param string|null $group
     * @return boolean
     */
    public function isPdo(?string $group = null) : bool
    {
        $config = $this->config(null, $group);

        return preg_match('#pdo#', $config['driver']);
    }

    /**
     * Wraps quotes around a string and escapes the content for a string parameter.
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     */
    public function quote($value, ?string $group = null)
    {
        if ($value === null)
        {
            return 'NULL';
        }
        if (is_string($value))
        {
            $connection = $this->connection($group);

            if ($connection)
            {
                return $connection->escapeString($value);
            }

            return str_replace(
                array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
                $value
            );
        }
        return $value;
    }

    /**
     * Recupere la configuration de la base de donnees courante
     *
     * @param string|null $key
     * @param string|null $group
     * @return array|mixed
     */
    public function config(?string $key = null, ?string $group = null) : array
    {
        $config = [];

        if ($group === $this->group)
        {
            $config = $this->config;
        }
        if (empty($config))
        {
            $config = $this->config = $this->makeConfig($group);
        }

        if (!empty($key))
        {
            return $config[$key] ?? null;
        }

        return $config;
    }

    /**
     * Recupere la connection a la base de donnees
     *
     * @param string|null $group
     * @return BaseConnection
     */
    public function connection(?string $group = null) : BaseConnection
    {
        $connection = null;

        if (empty($group))
        {
            $group = $this->group;
        }
        if ($group === $this->group)
        {
            $connection = $this->connections[$group ?? 'default'] ?? null;
        }
        if (empty($connection))
        {
            $connection = $this->connections[$group ?? 'default'] = $this->createConnection($group);
        }

        return $connection;
    }

    /**
     * Recupere le gestionnaire adequat de dump a la base de donnees
     *
     * @param string|null $group
     * @return BaseDump
     */
    public function dumper(?string $group = null) : BaseDump
    {
		$dumper = null;
		if (empty($group))
		{
			$group = $this->group;
		}
        if ($group === $this->group)
		{
            $dumper = $this->dumpers[$group ?? 'default'] ?? null;
		}
		if (empty($dumper))
		{
			$dumper = $this->dumpers[$group ?? 'default'] = $this->createDumper($group);
        }

		return $dumper;
    }



    /**
     * Cree une connection a la base de donnees en utilisant le driver approprier
     *
     * @param string|null $group
     * @return BaseConnection
     */
    private function createConnection(?string $group = null) : BaseConnection
    {
        if (empty($this->config) OR $this->group !== $group)
        {
            $this->config = $this->makeConfig($group);
        }

        if (preg_match('#mysql#', $this->config['driver']))
        {
            return new Mysql($this->config);
        }
        if (preg_match('#sqlite#', $this->config['driver']))
        {
            return new Sqlite($this->config);
        }
        if (preg_match('#pgsql#', $this->config['driver']))
        {
            return new Pgsql($this->config);
        }
        /**
         * @todo gerer les autres driver
         */
        throw new DatabaseException("Database driver not available for the moment", 1);
    }

    /**
     * Cree l'instance approprier du dumper en fonction du driver de la base de données utilisee
     *
     * @param string|null $group
     * @return BaseDump
     */
    private function createDumper(?string $group = null): BaseDump
    {
        if (empty($this->config) OR $this->group !== $group)
        {
            $this->config = $this->makeConfig($group);
        }

        if (preg_match('#mysql#', $this->config['driver']))
        {
            return new DumpMysql($this);
        }

        /**
         * @todo gerer les autres driver
         */
        throw new DatabaseException("Database driver not available for the moment", 1);
    }

    /**
     * Cherche et recupere une cle de configutaion
     *
     * @param array $config
     * @param string|null $key
     * @return mixed
     */
    private function getConfig(array $config, ?string $key = null)
    {
        if (empty($key))
        {
            return $config;
        }
        return Arr::getRecursive($config, $key);
    }

    /**
     * Verifie et initialise les parametres de connexion a la base de donnees
     *
     * @param string|null $group
     * @return array
     */
    private function makeConfig(?string $group = null) : array
    {
        $config = Config::get('database');

        if (empty($config['connection']))
        {
            DatabaseException::except('Used connction not found', '
                The key <b>connection</b> is required. <br>
                Please open the "'.Config::$_config_file['database'].'" file to set it
            ');
        }

        $group = empty($group) ? $config['connection'] : $group;

        if (empty($config[$group]))
        {
            DatabaseException::except('Database configuration not found', '
                The <b>'.$group.'</b> database configuration is not define. <br>
                Please open the "'.Config::$_config_file['database'].'" file to correct it
            ');
        }

        $config = array_merge($config[$group], $this->customConfig ?? []);
        $this->group = $group;

        $keys = ['driver','port','host','username','password','database','charset'];

        foreach ($keys As $key)
        {
            if (!array_key_exists($key, $config))
            {
                DatabaseException::except('Configuration key don\'t exist', '
                    The <b>'.$key.'</b> key of the '.$group.' database configuration don\'t exist. <br>
                    Please fill it in array $database["'.$group.'"] of the file  &laquo; '.Config::$_config_file['database'].' &raquo;
                ');
            }
        }
        foreach ($config As $key => $value)
        {
            if (!in_array($key, ['password', 'options','prefix', 'debug']) AND empty($value))
			{
                DatabaseException::except('Invalid configuration key', '
                    The <b>' . $key . '</b> key of ' . $group . ' database configuration must have a valid value. <br>
                    Please correct it in array $database["'.$group.'"] of the file  &laquo; ' . Config::$_config_file['database'] . ' &raquo;
                ');
            }
        }

        $config['debug'] = $this->autoValue($config, 'debug', '[debug]');

        $config['options']['enable_stats'] = $this->autoValue($config, 'options.enable_stats', '[options][enable_stats]');

        $config['options']['enable_cache'] = $this->autoValue($config, 'options.enable_cache', '[options][enable_cache]');

        return $config;
    }

    /**
     * Definit automatiquement la valeur d'une configuration en fonction de l'environnement
     *
     * @param array $config
     * @param string $key
     * @param string $label
     * @return boolean
     */
    private function autoValue(array $config, string $key, string $label) : bool
    {
        $value = $this->getConfig($config, $key);
        if (empty($value))
        {
            $value = 'auto';
        }

        if (!in_array($value, ['auto', true, false]))
        {
            DatabaseException::except('Invalid key set', '
                The <b>database['.$this->group.']'.$label.'</b> configuration is not set correctly (Accept values: auto/true/false). <br>
                Please edit &laquo; '.Config::$_config_file['database'].' &raquo; file to correct it
            ');
        }
        else if ($value === 'auto')
        {
            $value = on_dev();
        }

        return (bool) $value;
    }
}
