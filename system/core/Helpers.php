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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */

 
namespace dFramework\core;

/**
 * Helpers
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file		/system/core/Helpers.php
 */

class Helpers
{
    /**
     * @var Helpers
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

        $suffix = (string) $this->item('url_suffix');

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
        is_array($uri) && $uri = implode('/', $uri);
            return ltrim($uri, '/');
    }

    // --------------------------------------------------------------------

    /**
     * System URL
     *
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



    /**
     * Return IP of current user
     * 
     * @return string
     */
    public function ip_address() : string
    {
        return $_SERVER['REMOTE_ADDR'];
    }


    /**
     * @param $url
     * @return string
     */
    public function clean_url($url)
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
}