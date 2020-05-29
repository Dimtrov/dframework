<?php

use dFramework\core\data\Request;
use dFramework\core\Helpers;

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
