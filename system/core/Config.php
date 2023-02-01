<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.1
 */

namespace dFramework\core;

use dFramework\core\exception\ConfigException;
use dFramework\core\http\Uri;
use dFramework\core\utilities\Arr;
use InvalidArgumentException;

/**
 * Config
 *
 * Make, Get and Set the configurations of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/Config.php
 */
class Config
{
    /**
     *  All config file
     * @var array
     */
    public static $_config_file = [
        'autoload'      => APP_DIR.'config'.DS.'autoload.php',
        'data'          => APP_DIR.'config'.DS.'data.php',
        'database'      => APP_DIR.'config'.DS.'database.php',
        'general'       => APP_DIR.'config'.DS.'general.php',
        'layout'        => APP_DIR.'config'.DS.'layout.php',

        'email'         => APP_DIR.'config'.DS.'email.php',
        'rest'          => APP_DIR.'config'.DS.'rest.php',
    ];

    /**
     *  The configuration of application
     * @var array
     */
    private static $_config = [];

    /**
     * The required configuration
     * @var array
     */
    private static $_required_config  = [
        'data'      => ['encryption', 'session'],
        'general'   => ['environment', 'charset'],
    ];




    /**
     * Return some configuration of application
     *
     * @param string $config
     * @return mixed
     */
    public static function get(?string $config = null)
    {
        if (null === $config)
        {
            return self::$_config;
        }

        $config = explode('.', $config);
        $conf = array_shift($config);

        if (empty(self::$_config[$conf]))
        {
            self::load($conf);
        }

        return Arr::getRecursive(self::$_config[$conf], implode('.', $config));
    }

    /**
     * Set some configuration of application
     *
     * @param string $config
     * @param mixed $value
     */
    public static function set(string $config, $value)
    {
        $config = explode('.', $config);
        $conf = array_shift($config);

        Arr::setRecursive(self::$_config[$conf], implode('.', $config), $value);
    }


    /**
     * Config constructor.
     */
    public static function init()
    {
        self::load(['autoload', 'data', 'general']);

        self::checkRequired();

        self::setDefaultVar();

        self::initialize();
    }

    /**
     * Load the specific configuration in the scoope
     *
     * @param string|string[] $config
     * @param string|null $config_file
     */
    public static function load($config, ?string $config_file = null)
    {
        if (is_array($config))
        {
            foreach ($config As $key => $value)
            {
                if (! is_string($value) OR empty($value))
                {
                    continue;
                }
                if (is_string($key))
                {
                    $config_file = $value;
                    $conf = $key;
                }
                else
                {
                    $config_file = null;
                    $conf = $value;
                }
                self::load($conf, $config_file);
            }
        }
        else if (is_string($config))
        {
            if (empty($config_file))
            {
                if (! empty(self::$_config_file[$config]))
                {
                    $config_file = self::$_config_file[$config];
                }
                else
                {
                    $config_file = APP_DIR . 'config' . DS . $config . '.php';
                }
            }
            if (! file_exists($config_file))
            {
                ConfigException::except('
                    Unable to loader the <b>'.$config.'</br> configuration
                    <br>
                    The &laquo; '.$config_file.' &raquo; file does not exist
                ', 404);
            }
            if (! in_array($config_file, get_included_files()))
            {
                self::$_config = array_merge(self::$_config, require($config_file));
            }
        }
        else
        {
            return false;
        }
    }


    /**
     * Check if the required configurations is enter
     */
    private static function checkRequired()
    {
        foreach (self::$_required_config As $key => $value)
        {
            foreach ($value AS $item)
            {
                if (empty(self::get($key.'.'.$item)))
                {
                    ConfigException::except('
                        The <b>'.$key.'['.$item.']</b> configuration is required.
                        <br>
                        Please edit &laquo; '.self::$_config_file[$key].' &raquo; file to correct it
                    ');
                }
            }
        }
    }

	/**
	 * Set default value of required configuration
	 */
    private static function setDefaultVar()
    {
        if (empty(self::$_config['general']['base_url']))
        {
            if (isset($_SERVER['SERVER_ADDR']))
            {
                $server_addr = $_SERVER['HTTP_HOST'] ?? ((strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE) ? '[' . $_SERVER['SERVER_ADDR'] . ']' : $_SERVER['SERVER_ADDR']);
                if(isset($_SERVER['SERVER_PORT']))
                {
					$server_addr .= !preg_match('#:'.$_SERVER['SERVER_PORT'].'$#', $server_addr) ? ':80' : '';
                }
                if(
                    (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) !== 'off') OR
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') OR
                    (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) AND strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
                )
                {
                    $base_url = 'https';
                }
                else
                {
                    $base_url = 'http';
                }
                $base_url .= '://'.$server_addr.dirname(substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME']))));
            }
            else
            {
                $base_url = 'http://localhost:'. ($_SERVER['SERVER_PORT'] ?? '80');
            }
            self::set('general.base_url', rtrim(str_replace('\\', '/', $base_url), '/'));
        }

        if (null === self::get('general.use_template_engine'))
        {
            self::set('general.use_template_engine', true);
        }
    }

    /**
     * Initialize the system configuration with data from config file
     */
    private static function initialize()
    {
		if (self::$_config['general']['environment'] == 'auto')
		{
			self::$_config['general']['environment'] = is_online() ? 'production' : 'development';
		}
		if (self::$_config['general']['environment'] == 'dev')
		{
			self::$_config['general']['environment'] = 'development';
		}
		if (self::$_config['general']['environment'] == 'prod')
		{
			self::$_config['general']['environment'] = 'production';
		}

        switch (self::$_config['general']['environment'])
        {
			case 'development':
                error_reporting(-1);
                ini_set('display_errors', 1);
                break;
            case 'test':
            case 'production':
                ini_set('display_errors', 0);
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                break;
            default:
				self::exceptBadConfigValue('environment', ['development', 'production', 'test', 'auto'], 'general');
        }
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_DIR.'dflogs');

		/* ----------------
            Compression de vue
        ------------------- */
        self::$_config['general']['compress_output'] = self::$_config['general']['compress_output'] ?? 'auto';
        if (!in_array(self::$_config['general']['compress_output'], ['auto', true, false]))
        {
			self::exceptBadConfigValue('compress_output', ['auto', true, false], 'general');
        }
		if (self::$_config['general']['compress_output'] === 'auto')
		{
			self::$_config['general']['compress_output'] = !on_dev();
		}

		/* ----------------
            Affichage de la debugbar
        ------------------- */
		self::$_config['general']['show_debugbar'] = self::$_config['general']['show_debugbar'] ?? 'auto';
        if (!in_array(self::$_config['general']['show_debugbar'], ['auto', true, false]))
        {
			self::exceptBadConfigValue('show_debugbar', ['auto', true, false], 'general');
        }
		if (self::$_config['general']['show_debugbar'] == 'auto')
		{
			self::$_config['general']['show_debugbar'] = !is_online();
		}

        /* ----------------
            Parametres de session
        ------------------- */

        if (!empty(self::get('data.session.cache_limiter')))
        {
            $autorize = ['public', 'private', 'nocache', 'private_no_expire'];
            $config = strtolower(self::get('data.session.cache_limiter'));
            if (!in_array($config, $autorize))
            {
				self::exceptBadConfigValue('session[cache_limiter]', $autorize, 'data');
            }
            self::set('data.session.cache_limiter', $config);
        }
        if (isset(self::$_config['data']['session']['lifetime']) AND !is_int(self::$_config['data']['session']['lifetime']))
        {
			self::exceptBadConfigValue('session[lifetime]', 'It accept only integer values', 'data');
        }
    }


	/**
	 * Affiche l'exception dû à la mauvaise definition d'une configuration
	 *
	 * @param string $config_key
	 * @param array|string $accepts_values
	 * @param string $group (general, data, database, etc.)
	 */
	public static function exceptBadConfigValue(string $config_key, $accepts_values, string $group)
	{
		if (is_array($accepts_values))
		{
			$accepts_values = '(Accept values: '.implode('/', $accepts_values).')';
		}
		else if (!is_string($accepts_values))
		{
			throw new InvalidArgumentException('Mauvaise utilisation de la methode '. __METHOD__);
		}
		ConfigException::except('
			The <b>'.$group.'.'.$config_key.'</b> configuration is not set correctly. '.$accepts_values.'.
			<br>
			Please edit &laquo; '.self::$_config_file[$group].' &raquo; file to correct it
		');
	}


	 /**
     * Utilisé par les autres fonctions d'URL pour construire un
     * URI spécifique au framework basé sur la configuration de l'application.
     *
     * @internal En dehors du framework, ceci ne doit pas être utilisé directement.
     *
     * @param string $relativePath Peut inclure des requêtes ou des fragments
     *
     * @throws InvalidArgumentException Pour les chemins ou la configuration non valides
     */
    public static function getUri(string $relativePath = ''): Uri
    {
        $config = (object) config('general');

        if ($config->base_url === '')
		{
            throw new InvalidArgumentException(__METHOD__ . ' requires a valid baseURL.');
        }

        // Si un URI complet a été passé, convertissez-le
        if (is_int(strpos($relativePath, '://')))
			{
            $full         = new Uri($relativePath);
            $relativePath = Uri::createURIString(null, null, $full->getPath(), $full->getQuery(), $full->getFragment());
        }

        $relativePath = URI::removeDotSegments($relativePath);

        // Construire l'URL complète basée sur $config et $relativePath
        $url = rtrim($config->base_url, '/ ') . '/';

        // Recherche une page d'index
        if (! empty($config->index_page))
		{
            $url .= $config->index_page;

            // Vérifie si nous avons besoin d'un séparateur
            if ($relativePath !== '' && $relativePath[0] !== '/' && $relativePath[0] !== '?')
			{
                $url .= '/';
            }
        }

        $url .= $relativePath;

        $uri = new Uri($url);

        // Vérifie si le schéma baseURL doit être contraint dans sa version sécurisée
        if ($config->force_global_secure_requests && $uri->getScheme() === 'http')
		{
            $uri->setScheme('https');
        }

        return $uri;
    }
}
