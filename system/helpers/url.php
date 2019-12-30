<?php

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
        return trim(Helpers::instance()->site_url(trim($_SERVER['REQUEST_URI'], '/') . (preg_match('#^/#', $url) ? $url : '/' . $url)), '/');
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
        return;
    }
}
