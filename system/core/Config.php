<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov SARL
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov SARL. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Config
 *
 * Configuration of application
 *
 * @class       Config
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/config
 * @file		/system/core/Config.php
 */

namespace dFramework\core;

use dFramework\core\exception\ConfigException;


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
        'data'      => ['encryption'],
        'database'  => ['default'],
        'general'   => ['environment', 'charset'],
        'route'     => ['default_controller']
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
        self::load();

        self::checkRequired();

        self::setDefaultVar();

        self::initialize();
    }


    /**
     * Load the applications configurations
     */
    private static function load()
    {
        if (empty(self::$_config))
        {
            foreach (self::$_config_file As $key => $value)
            {
                if(!file_exists($value))
                {
                    continue;
                }
                self::$_config = array_merge(self::$_config, require_once($value));
            }
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
                if(empty(self::$_config[$key][$item]))
                {
                    throw new ConfigException('
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
        if (empty(self::$_config['general']['base_url']))
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
                $base_url = 'http://localhost:'. $_SERVER['SERVER_PORT'] ?? '80';
            }
            self::set('general.base_url', rtrim(str_replace('\\', '/', $base_url), '/'));
        }
        if (empty(self::$_config['general']['index_page']))
        {
            self::set('general.index_page', '');
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
            case 'development':
                error_reporting(-1);
                ini_set('display_errors', 1);
                break;
            case 'testing':
            case 'production':
                ini_set('display_errors', 0);
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                break;
            default:
                throw new ConfigException('
                    The <b>general[environment]</b> configuration is not set correctly (Accept values: development/production/testing). 
                    <br>
                    Please edit &laquo; '.self::$_config_file['general'].' &raquo; file to correct it
                ');
        }


        if (!empty(self::$_config['data']['log_file']) AND is_string(self::$_config['data']['log_file']))
        {
            ini_set('log_errors', 1);

            ini_set('error_log', BASEPATH . self::$_config['data']['log_file']);
        }


        self::$_config['general']['compress_output'] = self::$_config['general']['compress_output'] ?? 'auto';
        if(!in_array(self::$_config['general']['compress_output'], ['auto', true, false]))
        {
            throw new ConfigException('
                The <b>general[compress_output]</b> configuration is not set correctly (Accept values: auto/true/false). 
                <br>
                Please edit &laquo; '.self::$_config_file['general'].' &raquo; file to correct it
            ');
        }
        else if(self::$_config['general']['compress_output'] === 'auto')
        {
            self::$_config['general']['compress_output'] = (self::$_config['general']['environment'] !== 'development');
        }


        foreach (self::$_config['database'] As $key => $value)
        {
            self::$_config['database'][$key]['debug'] = self::$_config['database'][$key]['debug'] ?? 'auto';
            if(!in_array(self::$_config['database'][$key]['debug'], ['auto', true, false]))
            {
                throw new ConfigException('
                The <b>database['.$key.'][debug]</b> configuration is not set correctly (Accept values: auto/true/false). 
                <br>
                Please edit &laquo; '.self::$_config_file['database'].' &raquo; file to correct it
            ');
            }
            else if(self::$_config['database'][$key]['debug'] === 'auto')
            {
                self::$_config['database'][$key]['debug'] = (self::$_config['general']['environment'] === 'development');
            }
        }
    }


}