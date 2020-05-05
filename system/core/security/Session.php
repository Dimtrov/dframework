<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\security;

use dFramework\core\Config;

/**
 * Session
 *
 * This class provides a wrapper to manage sessions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Security
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       2.2
 * @file        /system/core/security/Session.php
 * @credit      PHP-Session - By Josantonius <https://github.com/Josantonius/PHP-Session>
 */

class Session
{
    /**
     * Prefix for sessions.
     *
     * @var string
     */
    private static $prefix = 'df_session';

    /**
     * Determine if session has started.
     *
     * @var bool
     */
    private static $sessionStarted = false;

    /**
     * Set prefix for sessions.
     *
     * @param mixed $prefix → prefix for sessions
     *
     * @return bool
     */
    public static function setPrefix($prefix) : bool
    {
        return is_string(self::$prefix = $prefix);
    }
    /**
     * Get prefix for sessions.
     *
     * @return string
     */
    public static function getPrefix() : string
    {
        return self::$prefix;
    }

    /**
     * If session has not started, start sessions.
     *
     * @return bool
     */
    public static function start() : bool
    {
        Csrf::instance()->deleteExpires();
        
        if (self::$sessionStarted === false) 
        {
            $config = Config::get('data.session');
            
	        session_name('df_app_sessions');
            session_cache_limiter($config['cache_limiter']);
            session_set_cookie_params(0, '/');
            if('nocache' != $config['cache_limiter'])
            {
                session_cache_expire($config['lifetime']);
            }
            session_start();
            
            if(!isset($_SESSION[self::$prefix]))
            {
                $_SESSION[self::$prefix] = [];
            }
            return self::$sessionStarted = true;
        }
        return false;
    }

    /**
     * Add value to a session.
     *
     * @param string|array $key   → name the data to save
     * @param mixed  $value → the data to save
     *
     * @return bool true
     */
    public static function set($key, $value = false)
    {
        if (is_array($key) AND $value === false) 
        {
            foreach ($key As $name => $value) 
            {
                self::set($name, $value);
            }
        } 
        else if(is_string($key))
        {                
            $session = $_SESSION[self::$prefix];
            $key = explode('.', $key);
            $count = count($key);

            $session_crypt = Config::get('data.session.crypt');
            
            if($count == 1) {
                $session[$key[0]] = $value;
            }
            if($count == 2) {
                $session[$key[0]][$key[1]] = $value;
            }
            if($count == 3) {
                $session[$key[0]][$key[1]][$key[2]] = $value;
            }
            if($count == 4) {
                $session[$key[0]][$key[1]][$key[2]][$key[3]] = $value;
            }
            if($count == 5) {
                $session[$key[0]][$key[1]][$key[2]][$key[3]][$key[4]] = $value;
            }

            $_SESSION[self::$prefix] = $session;
        }
        return true;
    }

    /**
     * Get item from session.
     *
     * @param string      $key       → item to look for in session
     *
     * @return mixed|null → key value, or null if key doesn't exists
     */
    public static function get(?string $key = null)
    {
        $session = $_SESSION[self::$prefix];
        if (empty($key))
        {
            return $session;
        }
        $key = explode('.', $key);
        $count = count($key);

        if($count == 1) 
        {
            return $session[$key[0]] ?? null;
        }
        if($count == 2) 
        {
            return $session[$key[0]][$key[1]] ?? null;
        }
        if($count == 3) 
        {
            return $session[$key[0]][$key[1]][$key[2]] ?? null;
        }
        if($count == 4) 
        {
            return $session[$key[0]][$key[1]][$key[2]][$key[3]] ?? null;
        }
        if($count == 5) 
        {
            return $session[$key[0]][$key[1]][$key[2]][$key[3]][$key[4]] ?? null;
        }
        return null;
    }

    /**
     * Extract session item, delete session item and finally return the item.
     *
     * @param string $key → item to extract
     *
     * @return mixed|null → return item or null when key does not exists
     */
    public static function pull($key)
    {
        if (isset($_SESSION[self::$prefix][$key])) 
        {
            $value = $_SESSION[self::$prefix][$key];
            unset($_SESSION[self::$prefix][$key]);

            return $value;
        }
        return null;
    }

    /**
     * Verify if a specific key exist in session
     *
     * @param string $key
     * @return bool
     */
    public static function exist(string $key) : bool
    {
        return !empty(self::get($key));
    }


    /**
     * Get session id.
     *
     * @return string → the session id or empty
     */
    public static function id() : string
    {
        return session_id();
    }

    /**
     * Regenerate session_id.
     *
     * @return string → session_id
     */
    public static function regenerate() : string
    {
        session_regenerate_id(true);

        return self::id();
    }

    /**
     * Empties and destroys the session.
     *
     * @param string $key    → session name to destroy
     * @param true|null|string   $prefix → if null clear all sessions, if true clear only index of session for current prefix, if string clear index of session for specific prefix
     *
     * @return bool
     */
    public static function destroy($key = '', $prefix = true) : bool
    {
        if(empty($key) AND null === $prefix)
        {
            session_unset();
            session_destroy();

            return true;
        }
        else if (true === self::$sessionStarted) 
        {
            if (empty($key)) 
            {
                if(true === $prefix) 
                {
                    $pref = self::$prefix;
                }
                else if(is_string($prefix))
                {
                    $pref = $prefix;
                }
                if(isset($pref))
                {
                    foreach ($_SESSION As $index => $value) 
                    {
                        if (strpos($index, $pref) === 0) 
                        {
                            unset($_SESSION[$index]);
                        }
                    }
                }            
            } 
            else 
            {
               unset($_SESSION[self::$prefix][$key]);
            }
            return true;
        }
        return false;
    }

}
