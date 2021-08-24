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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

namespace dFramework\core\utilities;

use dFramework\core\Config;
use dFramework\core\exception\Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use Laminas\Escaper\Escaper;

/**
 * Helpers
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file		/system/core/utilities/Helpers.php
 */
class Helpers
{
    /**
     * @var Helpers
     */
    private static $_instance = null;
    private static $config;

    /**
     * @return self
     */
    public static function instance() : self
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __construct()
    {
        self::$config = Config::get('general');
    }

    /**
     * Fetch a config file item
     *
     * @param	string	$item	Config item name
     * @param	string	$index	Index name
     * @return	string|null	The configuration item or NULL if the item doesn't exist
     */
    public static function item(string $item, ?string $index = '') : ?string
    {
		if (empty(self::$config))
		{
			self::$config = Config::get('general');
		}
        if ($index == '')
        {
            return self::$config[$item] ?? NULL;
        }
        return isset(self::$config[$index], self::$config[$index][$item]) ? self::$config[$index][$item] : NULL;
    }

    /**
     * Fetch a config file item with slash appended (if not empty)
     *
     * @param	string		$item	Config item name
     * @return	string|null	The configuration item or NULL if the item doesn't exist
     */
    public static function slash_item(string $item) : ?string
    {
		$config = self::item($item);

		if ($config == null)
		{
			return null;
		}
        if (trim($config) === '')
        {
            return '';
        }
        return rtrim($config, '/').'/';
    }

    /**
     * Site URL
     *
     * Returns base_url . index_page [. uri_string]
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @param	string|null	$protocol
     * @return	string
     */
    public static function site_url($uri = '', ?string $protocol = NULL) : string
    {
        $uri = explode('#', $uri);
        $hash = $uri[1] ?? '';
        $uri = explode('?', $uri[0]);
        $query = $uri[1] ?? '';

        $uri = $uri[0];
        $uri = preg_replace('#'.self::item('url_suffix').'$#i', '', $uri);

        $base_url = self::getBaseUrl();

        if (isset($protocol))
        {
            if ($protocol === '')
            {
                $base_url = substr($base_url, strpos($base_url, '//'));
            }
            else
            {
                $base_url = $protocol.substr($base_url, strpos($base_url, '://'));
            }
        }
        if (empty($uri))
        {
            return $base_url.self::item('index_page');
        }

        $uri = self::_uri_string($uri);

        $suffix = (string) self::item('url_suffix');

        if ($suffix !== '')
        {
            if (($offset = strpos($uri, '?')) !== FALSE)
            {
                $uri = substr($uri, 0, $offset).$suffix.substr($uri, $offset);
            }
            else
            {
                $uri .= $suffix;
            }
        }

        if (!empty($query))
        {
            $uri .= '?'.$query;
        }
        if (!empty($hash))
        {
            $uri .= '#'.$hash;
        }

        return $base_url.self::slash_item('index_page').$uri;
    }

    /**
     * Base URL
     *
     * Returns base_url [. uri_string]
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @param	string|null	$protocol
     * @return	string
     */
    public static function base_url($uri = '', ?string $protocol = NULL) : string
    {
        $base_url = self::getBaseUrl();

        if (isset($protocol))
        {
            // For protocol-relative links
            if ($protocol === '')
            {
                $base_url = substr($base_url, strpos($base_url, '//'));
            }
            else
            {
                $base_url = $protocol.substr($base_url, strpos($base_url, '://'));
            }
        }
        return $base_url.self::_uri_string($uri);
    }
    private static function getBaseUrl() : string
    {
        return (string) self::slash_item('base_url');

		/**
		 * @todo text
		 */
		/*
        return true !== Config::get('general.use_absolute_link')
        ? str_replace('\\', '/', BASE_URL.'/')
        : $this->slash_item('base_url');
		*/
    }

    /**
     * Build URI string
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @return	string
     */
    protected static function _uri_string($uri) : string
    {
        is_array($uri) && $uri = implode('/', $uri);
            return ltrim($uri, '/');
    }

    /**
     * System URL
     *
     * @return	string
     */
    public static function system_url() : string
    {
        $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));

        return self::slash_item('base_url').end($x).'/';
    }

    /**
     * Set a config file item
     *
     * @param	string	$item	Config item key
     * @param	string	$value	Config item value
     * @return	void
     */
    public static function set_item($item, $value)
    {
        self::$config[$item] = $value;
    }


	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string $version
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	public static function is_php(string $version) : bool
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
    }

    /**
     * Verifies if the file you want to access is a local file of your application or not
     *
     * @param string $name
     * @return	bool
     */
    public static function is_localfile(string $name) : bool
    {
        if (preg_match('#^'.self::item('base_url').'#i', $name))
        {
            return true;
        }
        if (!preg_match('#^(https?://)#i', $name))
        {
            return true;
        }
        return false;
    }

    /**
     * Test if a application is running in local or online
     *
     * @return bool
     */
    public static function is_online() : bool
    {
		$host = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];

        return (
			!empty($host) // Si c'est vide, ca veut certainement dire qu'on est en CLI or le CLI << n'est pas >> utilisé en ligne
			AND !in_array($host, ['localhost','127.0.0.1'])
            AND !preg_match('#\.dev$#', $host)
            AND !preg_match('#\.test$#', $host)
            AND !preg_match('#\.lab$#', $host)
            AND !preg_match('#\.loc(al)?$#', $host)
            AND !preg_match('#^192\.168#', $host)
        );
    }

    /**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link https://bugs.php.net/bug.php?id=54709
	 * @param string $file
	 * @return boolean
	 *
	 * @throws             \Exception
	 * @codeCoverageIgnore Not practical to test, as travis runs on linux
	 */
	public static function is_really_writable(string $file): bool
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' || ! ini_get('safe_mode'))
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/') . '/' . bin2hex(random_bytes(16));
			if (($fp = @fopen($file, 'ab')) === false)
			{
				return false;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);

			return true;
		}
		elseif (! is_file($file) || ( $fp = @fopen($file, 'ab')) === false)
		{
			return false;
		}

		fclose($fp);

		return true;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function clean_url(string $url) : string
    {
        $path = parse_url($url);
        $query = '';

        if (!empty($path['host']))
        {
            $r = $path['scheme'].'://';
            if (!empty($path['user']))
            {
                $r .= $path['user'];
                if (!empty($path['pass']))
                {
                    $r .= ':'.$path['pass'].'@';
                }
                $r .= '@';
            }
            if (!empty($path['host']))
            {
                $r .= $path['host'];
            }
            if (!empty($path['port']))
            {
                $r .= ":".$path['port'];
            }
            $url = $r.$path['path'];
            if (!empty($path['query'])) {
                $query = "?".$path['query'];
            }
        }
        $url = str_replace('/./', '/', $url);

        while (substr_count($url, '../'))
        {
            $url = preg_replace("!/([\w\d]+/\.\.)!",'', $url);
        }
        return $url.$query;
    }

    /**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string $str
	 * @param	bool $url_encoded
	 * @return	string
	 */
	public static function remove_invisible_characters(string $str, bool $url_encoded = true) : string
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/i';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/i';	// url encoded 16-31
			$non_displayables[] = '/%7f/i';	// url encoded 127
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
    }

    /**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * Valid context values: html, js, css, url, attr, raw, null
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 *
	 * @return string|array
	 * @throws \InvalidArgumentException
	 */
	public static function esc($data, ?string $context = 'html', ?string $encoding = null)
	{
		if (is_array($data))
		{
			foreach ($data As $key => &$value)
			{
				$value = self::esc($value, $context);
			}
		}

		if (is_string($data))
		{
			$context = strtolower($context);

			// Provide a way to NOT escape data since
			// this could be called automatically by
			// the View library.
			if (empty($context) OR $context === 'raw')
			{
				return $data;
			}

			if (! in_array($context, ['html', 'js', 'css', 'url', 'attr']))
			{
				throw new \InvalidArgumentException('Invalid escape context provided.');
			}

			if ($context === 'attr')
			{
				$method = 'escapeHtmlAttr';
			}
			else
			{
				$method = 'escape' . ucfirst($context);
			}

			static $escaper;
			if (! $escaper)
			{
                $escaper = new Escaper($encoding);
			}

			if ($encoding AND $escaper->getEncoding() !== $encoding)
			{
				$escaper = new Escaper($encoding);
			}

			$data = $escaper->$method($data);
		}

		return $data;
    }

	/**
	 * Convenience method for htmlspecialchars.
	 *
	 * @param mixed $text Text to wrap through htmlspecialchars. Also works with arrays, and objects.
	 *    Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
	 *    implement a `__toString` method. Otherwise the class name will be used.
	 *    Other scalar types will be returned unchanged.
	 * @param bool $double Encode existing html entities.
	 * @param string|null $charset Character set to use when escaping. Defaults to config value in `mb_internal_encoding()`
	 * or 'UTF-8'.
	 * @return mixed Wrapped text.
	 * @credit CackePHP (https://cakephp.org)
	 */
	public static function h($text, bool $double = true, ?string $charset = null)
	{
		if (is_string($text))
		{
			//optimize for strings
		}
		elseif (is_array($text))
		{
			$texts = [];
			foreach ($text As $k => $t)
			{
				$texts[$k] = self::h($t, $double, $charset);
			}

			return $texts;
		}
		elseif (is_object($text))
		{
			if (method_exists($text, '__toString'))
			{
				$text = (string)$text;
			}
			else
			{
				$text = '(object)' . get_class($text);
			}
		}
		elseif ($text === null OR is_scalar($text))
		{
			return $text;
		}

		static $defaultCharset = false;
		if ($defaultCharset === false)
		{
			$defaultCharset = mb_internal_encoding();
			if ($defaultCharset === null)
			{
				$defaultCharset = 'UTF-8';
			}
		}
		if (is_string($double))
		{
			deprecationWarning(
				'Passing charset string for 2nd argument is deprecated. ' .
				'Use the 3rd argument instead.'
			);
			$charset = $double;
			$double = true;
		}

		return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset ?: $defaultCharset, $double);
	}

    /**
     * Purify input using the HTMLPurifier standalone class.
     * Easily use multiple purifier configurations.
     *
     * @param string|string[]  $dirty_html  A string (or array of strings) to be cleaned.
     * @param string|false        $config      The name of the configuration (switch case) to use.
     * @return string|string[]               The cleaned string (or array of strings).
     */
    public static function purify($dirty_html, $config = false)
    {
        if (is_array($dirty_html))
        {
            foreach ($dirty_html As $key => $val)
            {
                $clean_html[$key] = self::purify($val, $config);
            }
        }
        else
        {
            $charset = self::item('charset');

            switch ($config)
            {

                case 'comment':
                    $config = HTMLPurifier_Config::createDefault();
                    $config->set('Core.Encoding', $charset);
                    $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
                    $config->set('HTML.Allowed', 'p,a[href|title],abbr[title],acronym[title],b,strong,blockquote[cite],code,em,i,strike');
                    $config->set('AutoFormat.AutoParagraph', true);
                    $config->set('AutoFormat.Linkify', true);
                    $config->set('AutoFormat.RemoveEmpty', true);
                    break;

                case false:
                    $config = HTMLPurifier_Config::createDefault();
                    $config->set('Core.Encoding', $charset);
                    $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
                    break;

                default:
                    Exception::show('The HTMLPurifier configuration labeled "'.htmlspecialchars($config, ENT_QUOTES, $charset).'" could not be found.');
            }

            $purifier = new HTMLPurifier($config);
            $clean_html = $purifier->purify($dirty_html);
        }

        return $clean_html;
    }

    /**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param mixed   $attributes string, array, object
	 * @param boolean $js
	 *
	 * @return string
	 */
	public static function stringify_attributes($attributes, bool $js = false): string
	{
		$atts = '';

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key . '=' . self::esc($val, 'js') . ',' : ' ' . $key . '="' . self::esc($val, 'attr') . '"';
		}

		return rtrim($atts, ',');
	}

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
    public static function env(string $key, $default = null)
    {
        if ($key === 'HTTPS')
        {
            if (isset($_SERVER['HTTPS']))
            {
                return (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off');
            }
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
            {
                return (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
            }
            if (isset($_SERVER['HTTP_FRONT_END_HTTPS']))
            {
                return (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) AND strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off');
            }
            return (strpos(self::env('SCRIPT_URI'), 'https://') === 0);
        }

        if ($key === 'SCRIPT_NAME')
        {
            if (self::env('CGI_MODE') AND isset($_ENV['SCRIPT_URL']))
            {
                $key = 'SCRIPT_URL';
            }
        }

        $val = null;
        if (isset($_SERVER[$key]))
        {
            $val = $_SERVER[$key];
        }
        elseif (isset($_ENV[$key]))
        {
            $val = $_ENV[$key];
        }
        elseif (getenv($key) !== false)
        {
            $val = getenv($key);
        }

        if ($key === 'REMOTE_ADDR' AND $val === self::env('SERVER_ADDR'))
        {
            $addr = self::env('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null)
            {
                $val = $addr;
            }
        }

        if ($val !== null)
        {
            return $val;
        }

        switch ($key)
        {
            case 'DOCUMENT_ROOT':
                $name = self::env('SCRIPT_NAME');
                $filename = self::env('SCRIPT_FILENAME');
                $offset = 0;
                if (!strpos($name, '.php'))
                {
                    $offset = 4;
                }
                return substr($filename, 0, -(strlen($name) + $offset));
            case 'PHP_SELF':
                return str_replace(self::env('DOCUMENT_ROOT'), '', self::env('SCRIPT_FILENAME'));
            case 'CGI_MODE':
                return (PHP_SAPI === 'cgi');
            case 'HTTP_BASE':
                $host = self::env('HTTP_HOST');
                $parts = explode('.', $host);
                $count = count($parts);

                if ($count === 1)
                {
                    return '.' . $host;
                }
                elseif ($count === 2)
                {
                    return '.' . $host;
                }
                elseif ($count === 3)
                {
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
                    if (in_array($parts[1], $gTLD))
                    {
                        return '.' . $host;
                    }
                }
                array_shift($parts);
                return '.' . implode('.', $parts);
        }

        return $default;
    }

    /**
     * Shortcut to ref library, HTML mode
     *
     * @param   mixed $args
	 * @return  void|string
     */
    public static function r()
    {
        $args = func_get_args();

		$options = [];

		$expressions = \ref::getInputExpressions($options);
		$capture = in_array('@', $options, true);

	    if (func_num_args() !== count($expressions))
	    {
            $expressions = null;
        }
	    $format = (php_sapi_name() !== 'cli') || $capture ? 'html' : 'cliText';

        if (!$capture && ($format === 'html') && !headers_sent() && (!ob_get_level() || ini_get('output_buffering')))
        {
            print '<!DOCTYPE HTML><html><head><title>REF</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
        }
        $ref = new \ref($format);

	    if ($capture)
	    {
            ob_start();
        }
        foreach ($args as $index => $arg)
        {
            $ref->query($arg, $expressions ? $expressions[$index] : null);
        }

        if ($capture)
        {
            return ob_get_clean();
        }

        if (in_array('~', $options, true) && ($format === 'html'))
        {
	        print '</body></html>';
	        exit(0);
	    }
    }

    /**
     * Shortcut to ref, plain text mode
    *
    * @param   mixed $args
    * @return  void|string
    */
    public static function rt()
    {
        $args        = func_get_args();
        $options     = array();
        $expressions = \ref::getInputExpressions($options);
        $capture     = in_array('@', $options, true);
        $ref         = new \ref((php_sapi_name() !== 'cli') || $capture ? 'text' : 'cliText');

        if (func_num_args() !== count($expressions))
        {
            $expressions = null;
        }
        if (!headers_sent())
        {
            header('Content-Type: text/plain; charset=utf-8');
        }
        if ($capture)
        {
            ob_start();
        }
        foreach ($args as $index => $arg)
        {
            $ref->query($arg, $expressions ? $expressions[$index] : null);
        }
        if ($capture)
        {
            return ob_get_clean();
        }
        if (in_array('~', $options, true))
        {
            exit(0);
        }
    }
}
