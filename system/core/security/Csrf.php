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


namespace dFramework\core\security;

/**
 * Csrf
 *
 * This class can generate token for csrf security.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Security
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       2.2
 * @file        /system/core/seurity/Csrf.php
 * @credit      PHP-CSRF-Security-class - by Malik Umer Farooq <https://www.facebook.com/malikumerfarooq01/>
 */


class Csrf
{
    private static $prefix = 'df_security';

    private $time;

    private $sysTime;
    
    /**
     * __construct.
     *
     *
     * @return Void;
     */
    public function __construct()
    {
        $this->deleteExpires();

        $this->updateSysTime();

        $this->generateSession();
    }

    private static $_instance = null;
    public static function instance()
    {
        if(null === self::$_instance) 
        {
            self::$_instance = new Csrf;
        }
        return self::$_instance;
    }

    /**
     * Delete token with $keye.
     *
     * @key = $key token tobe deleted
     *
     * @return void;
     */
    public function delete($token)
    {
        if (isset($_SESSION[self::$prefix]['csrf'][$token])) 
        {
            unset($_SESSION[self::$prefix]['csrf'][$token]);
        }
    }

    /**
     * Delete expire tokens.
     *
     *
     * @return void;
     */
    public function deleteExpires()
    {
        if (isset($_SESSION[self::$prefix]['csrf'])) 
        {
            foreach ($_SESSION[self::$prefix]['csrf'] As $token => $value) 
            {
                if (time() >= $value) 
                {
                    $this->delete($token);
                }
            }
        }
    }

    /**
     * Delete unnecessary tokens.
     *
     *
     * @return void;
     */
    public function deleteUnnecessary()
    {
        $total = self::countTokens();

        $delete = $total - 1;

        $tokens_deleted = $_SESSION[self::$prefix]['csrf'];

        $tokens = array_slice($tokens_deleted, 0, $delete);

        foreach ($tokens as $token => $time) 
        {
            $this->delete($token);
        }
    }

    /**
     * Debug
     *	return all tokens.
     *
     * @return json object;
     */
    public static function debug()
    {
        echo json_encode($_SESSION[self::$prefix]['csrf'], JSON_PRETTY_PRINT);
    }

    /**
     * Update time.
     *
     * @time = $time tobe updated
     *
     * @return bool
     */
    public function updateTime($time)
    {
        if(is_int($time) AND is_numeric($time)) 
        {
            $this->time = $time;

            return $this->time;
        } 
        return false;
    }

    /**
     * Update system time.
     *
     * @return void;
     */
    final private function updateSysTime()
    {
        $this->sysTime = time();
    }

    /**
     * generate salts for files.
     *
     * @param string $length length of salts
     *
     * @return string;
     */
    public static function generateSalt($length)
    {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

        $stringlength = count($chars);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $chars[rand(0, $stringlength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate tokens.
     *
     * @param int $time => $time
     * @param int $multiplier => 3*3600
     *
     * @return mixed;
     */
    public function generateToken($time, $multiplier)
    {
        $key = self::generateSalt(100);

        $utime = $this->updateTime($time);

        $value = $this->sysTime + ($utime * $multiplier);

        $_SESSION[self::$prefix]['csrf'][$key] = $value;

        return $key;
    }

    /**
     * Generate empty session.
     *
     * @return void;
     */
    protected function generateSession()
    {
        if (!isset($_SESSION[self::$prefix]['csrf'])) 
        {
            $_SESSION[self::$prefix]['csrf'] = [];
        }
    }

    /**
     * View token.
     *
     * @token = $key
     *
     * @return mixed;
     */
    public static function show($token)
    {
        if (isset($_SESSION[self::$prefix]['csrf'][$token])) 
        {
            return $_SESSION[self::$prefix]['csrf'][$token];
        } 
        else 
        {
            return false;
        }
    }

    /**
     * Verify token exists or not.
     *
     * @token = $key
     *
     * @return bool;
     */
    public function verify($token)
    {
        return (isset($_SESSION[self::$prefix]['csrf'][$token]));
    }

    /**
     * Last token.
     *
     * @return mixed
     */
    public static function lastToken()
    {
        if (isset($_SESSION[self::$prefix]['csrf'])) 
        {
            return end($_SESSION[self::$prefix]['csrf']);
        } 
        else 
        {
            return false;
        }
    }

    /**
     * Count tokens.
     *
     * @return int;
     */
    public static function countTokens()
    {
        if (isset($_SESSION[self::$prefix]['csrf'])) 
        {
            return count($_SESSION[self::$prefix]['csrf']);
        } 
        else 
        {
            return 0;
        }
    }


    /**
     * Check if request is from the same server
     *
     * @return bool
     */		 		
    public static function checkHost() : bool
    {
        if (isset($_SERVER['HTTP_REFERER'])) 
        {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            if (isset($_SERVER['HTTP_HOST'])) 
            {
                return ($referer['host'] == $_SERVER['HTTP_HOST']);
            } 
            if(isset($_SERVER['SERVER_NAME'])) 
            {
                return ($referer['host'] == $_SERVER['SERVER_NAME']);
            } 
            return false;
        }	
        return false;
    }		 		
    /**
     * Check if it's a postback action
     *
     * @return bool
     */
    public static function checkSameScript() : bool
    {
        if (isset($_SERVER['HTTP_REFERER'])) 
        {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            return ($referer['path'] == $_SERVER['SCRIPT_NAME']);
        }	
        return false;			
    }
    /**
     * Checking if not javascript injection
     *
     * @return bool False if allright or true if found injection		 
     */
    public static function checkVars() 
    {
        $ret = true;
        foreach($_GET as $k => $v) 
        {
            if (!self::checkValue($v)) 
            {
                $_GET[$k] = self::escapeValue($v);
                $ret = false;
            }
        }
        foreach($_POST as $k => $v) 
        {
            if (!self::checkValue($v)) 
            {
                $_POST[$k] = self::escapeValue($v);
                $ret = false;
            }
        }
        foreach($_COOKIE as $k => $v) 
        {
            if (!self::checkValue($v)) 
            {
                $_COOKIE[$k] = self::escapeValue($v);
                $ret = false;
            }
        }
        return $ret;
    } 

    /**
     * Disable injection expressions
     *
     * @param string|array $contents
     * @return string
     */ 		
    public static function escapeValue($contents) : string 
    {
        if (is_array($contents)) 
        {
            foreach($contents as $k => $v) 
            {
                $contents[$k] = self::escapeValue($v);
            }
        } 
        else 
        {
            // 1. Disable script injection with script tag
            if (strpos($contents, '<script') !== false) 
            {
                $contents = str_replace('<script', '<!-- script', $contents);
                $contents = str_replace('script>', 'script -->', $contents);
            }
            // 2. Disable javascript command
            if (strpos($contents, 'javascript') !== false) 
            {
                $contents = str_replace('javascript', '***', $contents);
            }		
            if (strpos($contents, "java\nscript") !== false) 
            {
                $contents = str_replace('javascript', '***', $contents);
            }
            // 3. Disable location command
            if (strpos($contents, 'document.location') !== false) 
            {
                $contents = str_replace('document.location', 'null', $contents);
            }
            // 4. Disable cookies command	
            if (strpos($contents, 'document.cookie') !== false) 
            {
                $contents = str_replace('document.cookie', 'null', $contents);
            }			
        }
        return $contents;		
    }		 	
    /**
     * Verify if found an injection expression
     *
     * @param string|array $contents
     * @return bool
     */	
    public static function checkValue($contents) 
    {
        if (is_array($contents)) 
        {
            foreach($contents As $k => $v) 
            {
                return (!self::checkValue($v));
            }
        } 
        else 
        {
            // 1. Disable script injection with script tag
            if (strpos($contents, '<script') !== false) {
                return false;
            }			
            // 2. Disable javascript command
            if (strpos($contents, 'javascript') !== false) {
                return false;		
            }		
            if (strpos($contents, "java\nscript") !== false) {
                return false;
            }
            // 3. Disable location command
            if (strpos($contents, 'document.location') !== false) {
                return false;
            }
            // 4. Disable cookies command	
            if (strpos($contents, 'document.cookie') !== false) {
                return false;
            }			
        }
        return true;			
    }
    
}