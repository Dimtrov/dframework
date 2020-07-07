<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2
 */

use dFramework\core\http\Request;
use dFramework\core\Helpers;
use dFramework\core\router\Router;

/**
 * dFramework Url Helpers
 *
 * @package		dFramework
 * @subpackage	Helpers
 * @category	Helpers
 * @since 		1.0
 */

// ------------------------------------------------------------------------


if ( ! function_exists('site_url'))
{
    function site_url($uri = '', $protocol = NULL)
    {
        return Helpers::instance()->site_url($uri, $protocol);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('base_url'))
{
    /**
     * Base URL
     *
     * Create a local URL based on your basepath.
     * Segments can be passed in as a string or an array, same as site_url
     * or a URL to a file can be passed in, e.g. to an image file.
     *
     * @param	string	$uri
     * @param	string	$protocol
     * @return	string
     */
    function base_url($uri = '', $protocol = NULL)
    {
        return Helpers::instance()->base_url($uri, $protocol);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('current_url')) {
    /**
     * Current URL
     *
     * @param string $url
     * @return    string
     */
    function current_url($url = '')
    {
        return site_url((new Request)->here().$url);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('redirect')) {
    /**
     * Redirect user
     *
     * @param    string $uri
     * @param    string $protocol
     * @return    void
     */
    function redirect($uri = '', $protocol = NULL)
    {
        header('Location: ' . site_url($uri, $protocol));
        exit(1);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('link_to')) {
    /**
     * Redirect user
     *
     * @param    string $uri
     * @param    mixed ...$params
     * @return   string
     */
    function link_to(string $uri, ...$params)
    {
        return Router::url($uri, $params);
    }
}

// ------------------------------------------------------------------------

if(!function_exists('clean_url')) {
    /**
     * @param string $url
     * @return string
     */
    function clean_url($url)
    {
        return Helpers::instance()->clean_url($url);
    }
}

// ------------------------------------------------------------------------
