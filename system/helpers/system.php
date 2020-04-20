<?php
use dFramework\core\Helpers;
use dFramework\core\Config;
use dFramework\core\data\Request;

// ------------------------------------------------------------------------

if ( ! function_exists('is_https'))
{
    /**
     * Determines if the application is accessed via an encrypted * (HTTPS) connection.
     *
     * @return	bool
     */
    function is_https()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('is_localfile'))
{
    /**
     * Determines if the application is accessed via an encrypted * (HTTPS) connection.
     *
     * @return	bool
     */
    function is_localfile($name)
    {
        if(preg_match('#^'.Config::get('general.base_url').'#i', $name))
        {
            return true;
        }
        if(!preg_match('#^(https?://)#i', $name))
        {
            return true;
        }
        return false;
    }
}

if(!function_exists('is_online'))
{
    /**
     * Test if a application is running in local or online
     * 
     * @return bool
     */
    function is_online()
    {
        return (
            !in_array($_SERVER['HTTP_HOST'], ['localhost','127.0.0.1'])
            AND !preg_match('#\.dev$#', $_SERVER['HTTP_HOST'])
            AND !preg_match('#\.lab$#', $_SERVER['HTTP_HOST'])
            AND !preg_match('#\.loc(al)?$#', $_SERVER['HTTP_HOST'])
            AND !preg_match('#^192\.168#', $_SERVER['HTTP_HOST'])
        );
    }
}

if (!function_exists('is_ajax_request')) {
    /**
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
     *
     * @return    bool
     */
    function is_ajax_request()
    {
        return (new Request)->is_ajax();
    }
}

if (!function_exists('ip_address')) {
    /**
     * Return IP Address of current user
     *
     * @return    string
     */
    function ip_address()
    {
        return Helpers::instance()->ip_address();
    }
}


if (!function_exists('env')) {

    /**
     * Gets an environment variable from available sources, and provides emulation
     * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
     * IIS, or SCRIPT_NAME in CGI mode). Also exposes some additional custom
     * environment information.
     *
     * @param string $key Environment variable name.
     * @return string Environment variable setting.
     * @credit CakePHP - http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#env
     */
    function env($key)
    {
        if ($key === 'HTTPS') {
            if (isset($_SERVER['HTTPS'])) {
                return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            }
            return (strpos(env('SCRIPT_URI'), 'https://') === 0);
        }

        if ($key === 'SCRIPT_NAME') {
            if (env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
                $key = 'SCRIPT_URL';
            }
        }

        $val = null;
        if (isset($_SERVER[$key])) {
            $val = $_SERVER[$key];
        } elseif (isset($_ENV[$key])) {
            $val = $_ENV[$key];
        } elseif (getenv($key) !== false) {
            $val = getenv($key);
        }

        if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
            $addr = env('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null) {
                $val = $addr;
            }
        }

        if ($val !== null) {
            return $val;
        }

        switch ($key) {
            case 'DOCUMENT_ROOT':
                $name = env('SCRIPT_NAME');
                $filename = env('SCRIPT_FILENAME');
                $offset = 0;
                if (!strpos($name, '.php')) {
                    $offset = 4;
                }
                return substr($filename, 0, -(strlen($name) + $offset));
            case 'PHP_SELF':
                return str_replace(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
            case 'CGI_MODE':
                return (PHP_SAPI === 'cgi');
            case 'HTTP_BASE':
                $host = env('HTTP_HOST');
                $parts = explode('.', $host);
                $count = count($parts);

                if ($count === 1) {
                    return '.' . $host;
                } elseif ($count === 2) {
                    return '.' . $host;
                } elseif ($count === 3) {
                    $gTLD = array(
                        'aero',
                        'asia',
                        'biz',
                        'cat',
                        'com',
                        'coop',
                        'edu',
                        'gov',
                        'info',
                        'int',
                        'jobs',
                        'mil',
                        'mobi',
                        'museum',
                        'name',
                        'net',
                        'org',
                        'pro',
                        'tel',
                        'travel',
                        'xxx'
                    );
                    if (in_array($parts[1], $gTLD)) {
                        return '.' . $host;
                    }
                }
                array_shift($parts);
                return '.' . implode('.', $parts);
        }
        return null;
    }

}
