<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT)
 *
 * @package	dFramework
 * @author	Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

/**
 * Functions
 *
 * ///
 *
 * @class       Functions
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/helpers
 * @file		/system/core/Functions.php
 */

namespace dFramework\core;


class Functions
{
    /**
     * @var Functions
     */
    private static $_instance = null;
    private $config;

    /**
     * @return mixed
     */
    public static function instance()
    {
        if(is_null(self::$_instance))
        {
            $class = ucfirst(__CLASS__);
            self::$_instance = new $class();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->config = Config::get('general');
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item
     *
     * @param	string	$item	Config item name
     * @param	string	$index	Index name
     * @return	string|null	The configuration item or NULL if the item doesn't exist
     */
    public function item($item, $index = '')
    {
        if ($index == '')
        {
            return $this->config[$item] ?? NULL;
        }
        return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item with slash appended (if not empty)
     *
     * @param	string		$item	Config item name
     * @return	string|null	The configuration item or NULL if the item doesn't exist
     */
    public function slash_item($item)
    {
        if ( ! isset($this->config[$item]))
        {
            return NULL;
        }
        if (trim($this->config[$item]) === '')
        {
            return '';
        }
        return rtrim($this->config[$item], '/').'/';
    }

    // --------------------------------------------------------------------

    /**
     * Site URL
     *
     * Returns base_url . index_page [. uri_string]
     *
     * @uses	CI_Config::_uri_string()
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @param	string	$protocol
     * @return	string
     */
    public function site_url($uri = '', $protocol = NULL)
    {
        $base_url = $this->slash_item('base_url');

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
            return $base_url.$this->item('index_page');
        }

        $uri = $this->_uri_string($uri);

        if ($this->item('enable_query_strings') === FALSE)
        {
            $suffix = isset($this->config['url_suffix']) ? $this->config['url_suffix'] : '';

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
            return $base_url.$this->slash_item('index_page').$uri;
        }
        if (strpos($uri, '?') === FALSE)
        {
            $uri = '?'.$uri;
        }
        return $base_url.$this->item('index_page').$uri;
    }

    // -------------------------------------------------------------

    /**
     * Base URL
     *
     * Returns base_url [. uri_string]
     *
     * @uses	CI_Config::_uri_string()
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @param	string	$protocol
     * @return	string
     */
    public function base_url($uri = '', $protocol = NULL)
    {
        $base_url = $this->slash_item('base_url');

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
        return $base_url.$this->_uri_string($uri);
    }

    // -------------------------------------------------------------

    /**
     * Build URI string
     *
     * @used-by	CI_Config::site_url()
     * @used-by	CI_Config::base_url()
     *
     * @param	string|string[]	$uri	URI string or an array of segments
     * @return	string
     */
    protected function _uri_string($uri)
    {
        if ($this->item('enable_query_strings') === FALSE)
        {
            is_array($uri) && $uri = implode('/', $uri);
            return ltrim($uri, '/');
        }
        if (is_array($uri))
        {
            return http_build_query($uri);
        }
        return $uri;
    }

    // --------------------------------------------------------------------

    /**
     * System URL
     *
     * @deprecated	3.0.0	Encourages insecure practices
     * @return	string
     */
    public function system_url()
    {
        $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));
        return $this->slash_item('base_url').end($x).'/';
    }

    // --------------------------------------------------------------------

    /**
     * Set a config file item
     *
     * @param	string	$item	Config item key
     * @param	string	$value	Config item value
     * @return	void
     */
    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }

}