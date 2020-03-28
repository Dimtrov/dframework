<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core;

use dFramework\core\exception\ConfigException;

/**
 * Config
 *
 * Make, Get and Set the configurations of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
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
        'route'         => APP_DIR.'config'.DS.'route.php',
       
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
        'route'     => ['default_controller'],
    ];



    /**
     * Return some configuration of application
     *
     * @param string $config
     * @return array|mixed|null
     */
    public static function get(?string $config = null)
    {
        if (null === $config)
        {
            return self::$_config;
        }
        $config = explode('.', $config);
        $count = count($config);

        if($count == 1) {
            return self::$_config[$config[0]] ?? null;
        }
        if($count == 2) {
            return self::$_config[$config[0]][$config[1]] ?? null;
        }
        if($count == 3) {
            return self::$_config[$config[0]][$config[1]][$config[2]] ?? null;
        }
        if($count == 4) {
            return self::$_config[$config[0]][$config[1]][$config[2]][$config[3]] ?? null;
        }
        if($count == 5) {
            return self::$_config[$config[0]][$config[1]][$config[2]][$config[3]][$config[4]] ?? null;
        }
        return null;
    }

    /**
     * Set some configuration of application
     *
     * @param $config
     * @param $value
     */
    public static function set(string $config, $value)
    {
        $config = explode('.', $config);
        $count = count($config);

        if($count == 1) {
            self::$_config[$config[0]] = $value;
        }
        if($count == 2) {
            self::$_config[$config[0]][$config[1]] = $value;
        }
        if($count == 3) {
            self::$_config[$config[0]][$config[1]][$config[2]] = $value;
        }
        if($count == 4) {
            self::$_config[$config[0]][$config[1]][$config[2]][$config[3]] = $value;
        }
        if($count == 5) {
            self::$_config[$config[0]][$config[1]][$config[2]][$config[3]][$config[4]] = $value;
        }
    }


    /**
     * Config constructor.
     * @throws ConfigException
     */
    public static function init()
    {
        self::load(['autoload', 'data', 'general', 'route']);

        self::checkRequired();

        self::setDefaultVar();

        self::initialize();
    }

    /**
     * Load the specific configucation in the scoope
     * 
     * @param string|string[] $config
     * @param string|null $config_file
     */
    public static function load($config, ?string $config_file = null)
    {
        if(is_array($config))
        {
            foreach($config As $key => $value)
            {
                if (!is_string($value) OR empty($value))
                {
                    continue;
                }
                if(is_string($key))
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
        else if(is_string($config))
        {
            if(empty($config_file))
            {
                if(!empty(self::$_config_file[$config]))
                {
                    $config_file = self::$_config_file[$config];
                }
                else 
                {
                    $config_file = APP_DIR . 'config' . DS . $config . '.php';
                }
            }
            if (!file_exists($config_file))
            {
                ConfigException::except('Unable to loader the <b>'.$config.'</b> configuration because the &laquo; '.$config_file.' &raquo; file does not exist', 404);
            }
            if(!in_array($config_file, get_included_files()))
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
                if(empty(self::get($key.'.'.$item)))
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

    private static function setDefaultVar()
    {
        if(true !== self::get('general.use_absolute_link'))
        {
            self::set('general.base_url', str_replace('\\', '/', BASE_URL.'/'));
        }
        else if (empty(self::$_config['general']['base_url']))
        {
            if (isset($_SERVER['SERVER_ADDR']))
            {
                $server_addr = $_SERVER['HTTP_HOST'] ?? ((strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE) ? '[' . $_SERVER['SERVER_ADDR'] . ']' : $_SERVER['SERVER_ADDR']);
                if(isset($_SERVER['SERVER_PORT']))
                {
                    $server_addr .= ':'. ((!preg_match('#:'.$_SERVER['SERVER_PORT'].'$#', $server_addr)) ? $_SERVER['SERVER_PORT'] : '80');
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

        if(null === self::get('general.use_template_engine'))
        {
            self::set('general.use_template_engine', true);
        }
    }

    /**
     * Initialize the system configuration with data from config file
     *
     * @throws ConfigException
     */
    private static function initialize()
    {

        switch (self::$_config['general']['environment'])
        {
            case 'dev':
                error_reporting(-1);
                ini_set('display_errors', 1);
                break;
            case 'test':
            case 'prod':
                ini_set('display_errors', 0);
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                break;
            default:
                ConfigException::except('
                    The <b>general[environment]</b> configuration is not set correctly (Accept values: dev/prod/test). 
                    <br>
                    Please edit &laquo; '.self::$_config_file['general'].' &raquo; file to correct it
                ');
        }
        ini_set('log_errors', 1);
        ini_set('error_log', APP_DIR.'logs'.DS.'dflogs');


        self::$_config['general']['compress_output'] = self::$_config['general']['compress_output'] ?? 'auto';
        if(!in_array(self::$_config['general']['compress_output'], ['auto', true, false]))
        {
            ConfigException::except('
                The <b>general[compress_output]</b> configuration is not set correctly (Accept values: auto/true/false). 
                <br>
                Please edit &laquo; '.self::$_config_file['general'].' &raquo; file to correct it
            ');
        }
        else if(self::$_config['general']['compress_output'] === 'auto')
        {
            self::$_config['general']['compress_output'] = (self::$_config['general']['environment'] !== 'dev');
        }

        /* ----------------
            Parametres de session
        ------------------- */
        if(!empty(self::get('data.session.cache_limiter')))
        {
            $autorize = ['public', 'private', 'nocache', 'private_no_expire'];
            $config = strtolower(self::get('data.session.cache_limiter'));
            if(!in_array($config, $autorize))
            {
                ConfigException::except('
                    The <b>data[session][cache_limiter]</b> configuration is not set correctly (Accept values: '.implode('/', $autorize).'). 
                    <br>
                    Please edit &laquo; '.self::$_config_file['data'].' &raquo; file to correct it
                ');
            }
            self::set('data.session.cache_limiter', $config);
        }
        if(isset(self::$_config['data']['session']['lifetime']) AND !is_int(self::$_config['data']['session']['lifetime']))
        {
            ConfigException::except('
                The <b>session[lifetime]</b> configuration is not set correctly: It accept only integer values. 
                <br>
                Please edit &laquo; '.self::$_config_file['data'].' &raquo; file to correct it
            ');
        }
    }
}
